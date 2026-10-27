<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display the public customer-facing pricing page.
     */
    public function index()
    {
        $plans = Plan::active()->get();

        $onboardingServices = [
            [
                'title' => 'Basic Landlord Setup',
                'price' => 'TSh 50,000 - 100,000',
                'description' => 'Property profile configuration, building & unit setup, rent configuration, and portal orientation.',
                'ideal_for' => 'Individual landlords & small apartment owners',
                'turnaround' => '1 - 2 business days',
            ],
            [
                'title' => 'Hostel Setup & Staff Training',
                'price' => 'TSh 150,000 - 300,000',
                'description' => 'Full room & bed layout mapping, inventory asset registration, on-site/remote training for managers & receptionists.',
                'ideal_for' => 'Student hostels & medium accommodation operators',
                'turnaround' => '2 - 4 business days',
            ],
            [
                'title' => 'Multi-Property Enterprise Onboarding',
                'price' => 'TSh 300,000 - 800,000+',
                'description' => 'Bulk data migration from spreadsheets/legacy systems, multi-property hierarchy setup, custom staff role configuration, and priority SLA.',
                'ideal_for' => 'Real estate management firms & large hostel chains',
                'turnaround' => '5 - 7 business days',
            ],
        ];

        $featuredListingPricing = [
            [
                'tier' => 'standard',
                'title' => 'Standard Boost',
                'duration' => '7 Days',
                'price' => 'TSh 5,000',
                'features' => ['Top search placement badge', 'Priority in city search results', 'Instant tenant inquiries'],
            ],
            [
                'tier' => 'extended',
                'title' => 'Extended Spotlight',
                'duration' => '14 Days',
                'price' => 'TSh 10,000',
                'features' => ['Homepage featured carousel', 'Priority in university radius filters', '2x social marketplace highlight'],
            ],
            [
                'tier' => 'premium',
                'title' => 'Premium Campaign',
                'duration' => '30 Days',
                'price' => 'TSh 15,000',
                'features' => ['Permanent featured banner for full month', 'Recommended badge on comparable stays', 'Full occupancy push support'],
            ],
        ];

        $faqs = [
            [
                'q' => 'How are rental units counted on RelianceHomes?',
                'a' => 'We use a transparent, property-type specific rule: For full houses or apartments rented as a whole, 1 property counts as 1 unit. For properties rented by room, each private room counts as 1 unit. For student hostels rented by bed space, each bed counts as 1 unit. You only pay for what you actually rent out.',
            ],
            [
                'q' => 'Is tenant browsing or applying free?',
                'a' => 'Yes! Searching, inspecting, and applying for student hostels and apartments is completely free for tenants and students. Subscription fees are only paid by property owners and managers to access the property management software.',
            ],
            [
                'q' => 'How does annual billing work?',
                'a' => 'Annual plans offer approximately 2 months free compared to paying monthly. For example, the Starter plan is TSh 25,000/month (TSh 300,000/year), but paying annually costs only TSh 250,000/year—saving you TSh 50,000 upfront.',
            ],
            [
                'q' => 'What payment methods are supported for subscriptions in Tanzania?',
                'a' => 'We support Tanzanian mobile money (M-Pesa, TigoPesa, Airtel Money) as well as direct bank transfers (CRDB, NMB). After initiating an order, you can upload your transaction reference or receipt for immediate activation.',
            ],
            [
                'q' => 'What happens if I reach my unit or property limit?',
                'a' => 'Your existing properties, active contracts, and historical financial records will remain completely intact and accessible. When you are ready to add new units or properties, you can upgrade your plan seamlessly from your dashboard.',
            ],
        ];

        return view('public.pricing', compact('plans', 'onboardingServices', 'featuredListingPricing', 'faqs'));
    }

    /**
     * Submit an Enterprise / Custom Quotation Request.
     */
    public function quoteRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:150',
            'estimated_units' => 'required|string|max:50',
            'property_types' => 'nullable|string|max:200',
            'message' => 'nullable|string|max:1000',
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'enterprise_quote_requested',
            'description' => "Enterprise quotation requested by {$validated['name']} ({$validated['email']}) for {$validated['estimated_units']} units.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Thank you! Your enterprise quotation request has been received. Our Tanzanian accommodation team will contact you within 24 hours.');
    }
}
