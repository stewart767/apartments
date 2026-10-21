<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Institution;
use App\Models\Amenity;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicController extends Controller
{
    protected $availabilityService;
    protected $bookingService;

    public function __construct(AvailabilityService $availabilityService, BookingService $bookingService)
    {
        $this->availabilityService = $availabilityService;
        $this->bookingService = $bookingService;
    }

    /**
     * Home Page. Featured and Verified properties are queried.
     */
    public function home()
    {
        $featuredProperties = Property::with(['primaryImage', 'images', 'propertyType', 'region', 'district', 'institution'])
            ->published()
            ->verified()
            ->featured()
            ->latest()
            ->limit(6)
            ->get();

        if ($featuredProperties->isEmpty()) {
            $featuredProperties = Property::with(['primaryImage', 'images', 'propertyType', 'region', 'district', 'institution'])
                ->published()
                ->latest()
                ->limit(6)
                ->get();
        }

        $recentProperties = Property::with(['primaryImage', 'images', 'propertyType', 'region', 'district', 'institution'])
            ->published()
            ->latest()
            ->limit(6)
            ->get();

        $regions = Region::withCount('properties')->orderBy('properties_count', 'desc')->limit(6)->get();
        $propertyTypes = PropertyType::withCount('properties')->get();
        $institutions = Institution::withCount('properties')->limit(8)->get();
        $topAmenities = Amenity::limit(8)->get();

        return view('public.home', compact(
            'featuredProperties',
            'recentProperties',
            'regions',
            'propertyTypes',
            'institutions',
            'topAmenities'
        ));
    }

    /**
     * Search & Filter Marketplace.
     */
    public function search(Request $request)
    {
        $query = Property::with(['primaryImage', 'propertyType', 'region', 'district', 'institution'])
            ->published();

        // 1. Text Search (Property name or description)
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('property_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Region / District / Ward
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }
        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->input('ward_id'));
        }

        // 3. Property Type
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->input('property_type_id'));
        }

        // 4. Rent Structure / Accommodation Type
        if ($request->filled('rent_structure')) {
            $query->where('rent_structure', $request->input('rent_structure'));
        }

        // 5. Institution & Distance
        if ($request->filled('institution_id')) {
            $query->where('nearby_institution_id', $request->input('institution_id'));
            if ($request->filled('max_distance')) {
                $query->where('distance_from_institution', '<=', $request->input('max_distance'));
            }
        }

        // 6. Price Range
        if ($request->filled('min_price')) {
            $query->where(function($q) use ($request) {
                $q->where('price', '>=', $request->input('min_price'))
                  ->orWhereHas('rooms', function($sq) use ($request) {
                      $sq->where('price', '>=', $request->input('min_price'));
                  })
                  ->orWhereHas('beds', function($sq) use ($request) {
                      $sq->where('price', '>=', $request->input('min_price'));
                  });
            });
        }
        if ($request->filled('max_price')) {
            $query->where(function($q) use ($request) {
                $q->where('price', '<=', $request->input('max_price'))
                  ->orWhereHas('rooms', function($sq) use ($request) {
                      $sq->where('price', '<=', $request->input('max_price'));
                  })
                  ->orWhereHas('beds', function($sq) use ($request) {
                      $sq->where('price', '<=', $request->input('max_price'));
                  });
            });
        }

        // 7. Verified Only
        if ($request->boolean('verified_only') || $request->input('verified') == 1) {
            $query->where('verification_status', 'approved');
        }

        // 8. Amenities (Querying through Pivot Table)
        if ($request->filled('amenities') && is_array($request->input('amenities'))) {
            $amenityIds = $request->input('amenities');
            foreach ($amenityIds as $id) {
                $query->whereHas('amenities', function ($q) use ($id) {
                    $q->where('amenities.id', $id);
                });
            }
        }

        // Pagination: 9 per page
        $properties = $query->latest()->paginate(9)->withQueryString();

        // Load reference data for filter selectors
        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();
        $propertyTypes = PropertyType::all();
        $institutions = Institution::all();
        $amenitiesList = Amenity::all();

        return view('public.search', compact('properties', 'regions', 'districts', 'wards', 'propertyTypes', 'institutions', 'amenitiesList'));
    }

    /**
     * Details Page for a single Property.
     */
    public function details($id)
    {
        // Cache property details view model for 5 minutes
        $property = Cache::remember("property_details_{$id}", 300, function () use ($id) {
            return Property::with([
                'owner',
                'propertyType',
                'region',
                'district',
                'ward',
                'institution',
                'amenities',
                'images',
                'buildings.floors.rooms.beds', // Eager load whole hierarchy to avoid N+1
                'rooms.beds', // Eager load direct rooms too
                'reviews.tenant'
            ])
            ->published()
            ->findOrFail($id);
        });

        // Compute real-time availability summary using our engine
        $availability = $this->availabilityService->getAvailabilitySummary($property);

        // Fetch similar / recommended properties
        $similarProperties = Property::with(['primaryImage', 'images', 'propertyType', 'region', 'district'])
            ->published()
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('public.details', compact('property', 'availability', 'similarProperties'));
    }

    /**
     * Handle the booking reservation post.
     */
    public function book(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required',
            'bed_id' => 'nullable',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'tenant_notes' => 'nullable|string|max:500',
        ]);

        try {
            $data = $request->only(['room_id', 'bed_id', 'start_date', 'end_date', 'tenant_notes']);
            $data['property_id'] = $id;

            // Normalize "all" option for entire property
            if ($data['room_id'] === 'all') {
                $data['room_id'] = null;
            }

            $application = $this->bookingService->createApplication(auth()->user(), $data);

            return redirect()->route('tenant.applications')
                ->with('success', "Your booking application #{$application->application_number} was successfully submitted and is under review!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
