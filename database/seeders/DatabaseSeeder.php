<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institution;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\PropertyType;
use App\Models\RoomType;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Bed;
use App\Models\AssetCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to safely truncate all tables
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $tables = [
            'tenant_assets',
            'asset_inspections',
            'asset_handover_records',
            'assets',
            'asset_categories',
            'notifications',
            'activity_logs',
            'favorites',
            'reviews',
            'maintenance_images',
            'maintenance_requests',
            'check_outs',
            'check_ins',
            'payments',
            'contracts',
            'applications',
            'tenant_details',
            'owner_details',
            'wizard_drafts',
            'property_images',
            'amenity_room',
            'amenity_property',
            'beds',
            'rooms',
            'floors',
            'buildings',
            'property_user',
            'properties',
            'amenities',
            'room_types',
            'property_types',
            'wards',
            'districts',
            'regions',
            'institutions',
            'users',
        ];

        foreach ($tables as $table) {
            \Illuminate\Support\Facades\DB::table($table)->truncate();
        }

        if ($driver === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        } elseif ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 1. Seed Default Users
        $superadmin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@platform.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'status' => 'active',
            'phone' => '+255712345678',
            'gender' => 'male',
        ]);

        $owner = User::create([
            'name' => 'John Property Owner',
            'email' => 'owner@platform.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
            'phone' => '+255723456789',
            'gender' => 'male',
        ]);

        $manager = User::create([
            'name' => 'Sarah Property Manager',
            'email' => 'manager@platform.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'status' => 'active',
            'phone' => '+255734567890',
            'gender' => 'female',
        ]);

        $receptionist = User::create([
            'name' => 'Grace Receptionist',
            'email' => 'receptionist@platform.com',
            'password' => bcrypt('password'),
            'role' => 'receptionist',
            'status' => 'active',
            'phone' => '+255745678901',
            'gender' => 'female',
        ]);

        $tenant = User::create([
            'name' => 'Frank Student Tenant',
            'email' => 'tenant@platform.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'status' => 'active',
            'phone' => '+255756789012',
            'gender' => 'male',
            'date_of_birth' => '2004-05-15',
        ]);

        // 2. Seed Institutions
        $udsm = Institution::create([
            'name' => 'University of Dar es Salaam',
            'description' => 'Main campus at Mlimani',
            'address' => 'Ubungo, Dar es Salaam',
            'latitude' => -6.7801,
            'longitude' => 39.2033,
        ]);

        $aru = Institution::create([
            'name' => 'Ardhi University',
            'description' => 'Located near UDSM',
            'address' => 'Mwenge, Dar es Salaam',
            'latitude' => -6.7765,
            'longitude' => 39.2180,
        ]);

        $dit = Institution::create([
            'name' => 'Dar es Salaam Institute of Technology',
            'description' => 'Main city campus',
            'address' => 'Bibi Titi, Ilala, Dar es Salaam',
            'latitude' => -6.8145,
            'longitude' => 39.2801,
        ]);

        Institution::create([
            'name' => 'Muhimbili University of Health and Allied Sciences',
            'description' => 'Main campus Upanga',
            'address' => 'Upanga, Dar es Salaam',
        ]);

        Institution::create([
            'name' => 'Mzumbe University',
            'description' => 'Main campus Morogoro',
            'address' => 'Mzumbe, Morogoro',
        ]);

        Institution::create([
            'name' => 'University of Dodoma',
            'description' => 'Main campus Dodoma',
            'address' => 'Dodoma',
        ]);

        Institution::create([
            'name' => 'Sokoine University of Agriculture',
            'description' => 'Main campus Morogoro',
            'address' => 'Morogoro',
        ]);

        Institution::create([
            'name' => 'State University of Zanzibar',
            'description' => 'SUZA Zanzibar',
            'address' => 'Zanzibar',
        ]);

        Institution::create([
            'name' => 'Institute of Finance Management',
            'description' => 'IFM main campus',
            'address' => 'Shaaban Robert Street, Dar es Salaam',
        ]);

        Institution::create([
            'name' => 'College of Business Education',
            'description' => 'CBE Dar es Salaam',
            'address' => 'Bibi Titi Road, Dar es Salaam',
        ]);

        Institution::create([
            'name' => 'Tanzania Institute of Accountancy',
            'description' => 'TIA main campus',
            'address' => 'Kurasini, Dar es Salaam',
        ]);

        Institution::create([
            'name' => 'National Institute of Transport',
            'description' => 'NIT Mabibo campus',
            'address' => 'Mabibo, Dar es Salaam',
        ]);

        Institution::create([
            'name' => 'St. Augustine University of Tanzania',
            'description' => 'SAUT Mwanza',
            'address' => 'Mwanza',
        ]);

        Institution::create([
            'name' => 'Mbeya University of Science and Technology',
            'description' => 'MUST Mbeya',
            'address' => 'Mbeya',
        ]);

        Institution::create([
            'name' => 'Hubert Kairuki Memorial University',
            'description' => 'HKMU Mikocheni',
            'address' => 'Mikocheni, Dar es Salaam',
        ]);


        // 3. Seed Locations (Region -> District -> Ward)
        // (Location tables are already truncated at the beginning of this seeder)

        // Seed Regions
        $regionsJson = json_decode(file_get_contents(database_path('data/regions.json')), true)['features'];
        $regionsData = [];
        foreach ($regionsJson as $r) {
            $name = trim($r['properties']['region']);
            $regionsData[] = [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        Region::insert($regionsData);
        $regionsMap = Region::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();

        // Seed Districts
        $districtsJson = json_decode(file_get_contents(database_path('data/districts.json')), true)['features'];
        $districtsData = [];
        foreach ($districtsJson as $d) {
            $regRaw = trim($d['properties']['region']);
            $regClean = trim(preg_replace('/ Region$/i', '', $regRaw));
            $distRaw = trim($d['properties']['District']);
            
            $regionId = $regionsMap[strtolower($regClean)] ?? null;
            if (!$regionId) {
                foreach ($regionsMap as $key => $val) {
                    if (strpos(strtolower($regClean), $key) !== false) {
                        $regionId = $val;
                        break;
                    }
                }
            }
            
            if ($regionId) {
                $districtsData[] = [
                    'region_id' => $regionId,
                    'name' => $distRaw,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        District::insert($districtsData);
        $districtsMap = District::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();

        // Seed Wards
        $suffixPattern = '/\b(District|Town|Municipal|City|Rural|TC|MC|DC|Council|Municipal Council|Municipality|Kati|Zanzibar)\b/i';
        $cleanName = function($name) use ($suffixPattern) {
            $cleaned = preg_replace($suffixPattern, '', $name);
            return strtolower(trim(preg_replace('/\s+/', ' ', $cleaned)));
        };

        $cleanDistMap = [];
        foreach (District::all() as $d) {
            $cleanDistMap[$cleanName($d->name)] = $d->id;
        }

        $wardsJson = json_decode(file_get_contents(database_path('data/wards.json')), true)['features'];
        $wardsData = [];
        foreach ($wardsJson as $w) {
            $distRaw = trim($w['properties']['District']);
            $wardRaw = trim($w['properties']['Ward']);
            
            $districtId = $districtsMap[strtolower($distRaw)] ?? null;
            if (!$districtId) {
                $distClean = $cleanName($distRaw);
                if ($distClean === 'unguja kati') {
                    $districtId = $cleanDistMap['kati'] ?? null;
                } elseif ($distClean === 'kusini unguja') {
                    $districtId = $cleanDistMap['kusini'] ?? null;
                } elseif ($distClean === 'songwe chunya') {
                    $districtId = $cleanDistMap['songwe'] ?? null;
                } elseif ($distClean === 'tunduma') {
                    $districtId = $cleanDistMap['momba'] ?? null;
                } else {
                    $districtId = $cleanDistMap[$distClean] ?? null;
                }
            }
            
            if ($districtId) {
                $wardsData[] = [
                    'district_id' => $districtId,
                    'name' => $wardRaw,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        foreach (array_chunk($wardsData, 500) as $chunk) {
            Ward::insert($chunk);
        }

        // Fetch location instances for downstream seeding of institutions, properties, etc.
        $dsm = Region::where('name', 'Dar es Salaam')->first();
        $arusha = Region::where('name', 'Arusha')->first();
        $dodoma = Region::where('name', 'Dodoma')->first();
        $morogoro = Region::where('name', 'Morogoro')->first();

        $kinondoni = District::where('name', 'like', 'Kinondoni%')->first();
        $ilala = District::where('name', 'like', 'Ilala%')->first();
        $temeke = District::where('name', 'like', 'Temeke%')->first();
        $ubungoDistrict = District::where('name', 'like', 'Ubungo%')->first();
        $kigamboni = District::where('name', 'like', 'Kigamboni%')->first();
        $arushaCity = District::where('name', 'like', 'Arusha City%')->orWhere('name', 'like', 'Arusha Municipal%')->orWhere('name', 'like', 'Arusha%')->first();
        $dodomaMunicipal = District::where('name', 'like', 'Dodoma Municipal%')->orWhere('name', 'like', 'Dodoma%')->first();
        $morogoroMunicipal = District::where('name', 'like', 'Morogoro Municipal%')->orWhere('name', 'like', 'Morogoro%')->first();


        $mwenge = Ward::where('name', 'Mwenge')->where('district_id', $kinondoni->id)->first() 
            ?? Ward::firstOrCreate(['name' => 'Mwenge', 'district_id' => $kinondoni->id]);
        $kijitonyama = Ward::where('name', 'Kijitonyama')->where('district_id', $kinondoni->id)->first();
        $mwananyamala = Ward::where('name', 'Mwananyamala')->where('district_id', $kinondoni->id)->first();
        $upanga = Ward::where('name', 'like', 'Upanga%')->where('district_id', $ilala->id)->first()
            ?? Ward::firstOrCreate(['name' => 'Upanga', 'district_id' => $ilala->id]);
        $kariakoo = Ward::where('name', 'Kariakoo')->where('district_id', $ilala->id)->first();
        $kisutu = Ward::where('name', 'Kisutu')->where('district_id', $ilala->id)->first();
        $ubungo = Ward::where('name', 'Ubungo')->where('district_id', $kinondoni->id)->first()
            ?? Ward::where('name', 'Ubungo')->first();
        $kimara = Ward::where('name', 'Kimara')->where('district_id', $kinondoni->id)->first();
        $mbezi = Ward::where('name', 'Mbezi')->where('district_id', $kinondoni->id)->first();
        $temekeWard = Ward::where('name', 'Temeke')->where('district_id', $temeke->id)->first();
        $changombe = Ward::where('name', 'Chang\'ombe')->where('district_id', $temeke->id)->first();
        $kigamboniWard = Ward::where('name', 'Kigamboni')->where('district_id', $temeke->id)->first()
            ?? Ward::where('name', 'Kigamboni')->first();

        $sekei = Ward::where('name', 'Sekei')->where('district_id', $arushaCity->id)->first();
        $kaloleni = Ward::where('name', 'Kaloleni')->where('district_id', $arushaCity->id)->first();

        $hazina = Ward::where('name', 'Hazina')->where('district_id', $dodomaMunicipal->id)->first();
        $miyuji = Ward::where('name', 'Miyuji')->where('district_id', $dodomaMunicipal->id)->first();

        $mzumbeWard = Ward::where('name', 'Mzumbe')->where('district_id', $morogoroMunicipal->id)->first()
            ?? Ward::where('name', 'Mzumbe')->first();

        // 3b. Seed Asset Categories
        AssetCategory::create(['name' => 'Furniture', 'description' => 'Beds, tables, chairs, wardrobes, and cupboards']);
        AssetCategory::create(['name' => 'Appliances', 'description' => 'Refrigerators, microwaves, AC units, and washing machines']);
        AssetCategory::create(['name' => 'Electronics', 'description' => 'Television sets, routers, decoders, and audio systems']);
        AssetCategory::create(['name' => 'Bedding', 'description' => 'Mattresses, pillows, bed sheets, and blankets']);
        AssetCategory::create(['name' => 'Bathroom Fixtures', 'description' => 'Showers, mirrors, towel racks, and toilet fixtures']);
        AssetCategory::create(['name' => 'Lighting & Electrical', 'description' => 'LED bulbs, fans, extension cords, and switches']);
        AssetCategory::create(['name' => 'Kitchenware', 'description' => 'Pots, pans, cutlery, plates, and glasses']);
        AssetCategory::create(['name' => 'Safety & Security', 'description' => 'Fire extinguishers, smoke detectors, and safes']);

        // 4. Seed Property Types
        $hostel = PropertyType::create(['name' => 'Student Hostel', 'slug' => 'student-hostel', 'description' => 'Student hostel with bed/room rentals']);
        $apartment = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment', 'description' => 'Independent residential apartment unit']);
        $bedsitter = PropertyType::create(['name' => 'Bedsitter', 'slug' => 'bedsitter', 'description' => 'Single self-contained room']);
        $house = PropertyType::create(['name' => 'Normal House', 'slug' => 'normal-house', 'description' => 'Standard residential house']);
        $shared = PropertyType::create(['name' => 'Shared House', 'slug' => 'shared-house', 'description' => 'Co-living space with shared amenities']);

        // 5. Seed Room Types
        $single = RoomType::create(['name' => 'Single Room', 'slug' => 'single-room', 'description' => 'One person room']);
        $double = RoomType::create(['name' => 'Double Room', 'slug' => 'double-room', 'description' => 'Two person room']);
        $triple = RoomType::create(['name' => 'Triple Room', 'slug' => 'triple-room', 'description' => 'Three person room']);
        $quad = RoomType::create(['name' => 'Four Person Room', 'slug' => 'four-person-room', 'description' => 'Four person room']);
        $studio = RoomType::create(['name' => 'Studio Unit', 'slug' => 'studio-unit', 'description' => 'Self-contained studio room']);
        $aptRoom = RoomType::create(['name' => 'Apartment Unit', 'slug' => 'apartment-unit', 'description' => 'Entire apartment unit containing multiple rooms']);

        // 6. Seed Amenities
        $wifi = Amenity::create(['name' => 'Wi-Fi', 'icon' => 'wifi', 'category' => 'general']);
        $water = Amenity::create(['name' => 'Water 24/7', 'icon' => 'droplet', 'category' => 'general']);
        $electricity = Amenity::create(['name' => 'Electricity LUKU', 'icon' => 'bolt', 'category' => 'general']);
        $parking = Amenity::create(['name' => 'Secure Parking', 'icon' => 'square-p', 'category' => 'general']);
        $cctv = Amenity::create(['name' => 'CCTV Security', 'icon' => 'video', 'category' => 'property']);
        $security = Amenity::create(['name' => 'Gate Security Guard', 'icon' => 'shield', 'category' => 'property']);
        $kitchen = Amenity::create(['name' => 'Kitchen Facility', 'icon' => 'cooking-pot', 'category' => 'room']);
        $laundry = Amenity::create(['name' => 'Laundry Machine', 'icon' => 'washing-machine', 'category' => 'general']);
        $ac = Amenity::create(['name' => 'Air Conditioning', 'icon' => 'wind', 'category' => 'room']);
        $study = Amenity::create(['name' => 'Study Desk/Room', 'icon' => 'book-open', 'category' => 'general']);
        $generator = Amenity::create(['name' => 'Backup Generator', 'icon' => 'zap', 'category' => 'property']);
        $bathroom = Amenity::create(['name' => 'Private Bathroom', 'icon' => 'bath', 'category' => 'room']);

        // 7. Seed Properties
        // Property 1: UDSM Mlimani Hostels (Student Hostel rented by Bed)
        $prop1 = Property::create([
            'owner_id' => $owner->id,
            'property_name' => 'UDSM Mlimani Executive Hostel',
            'property_type_id' => $hostel->id,
            'description' => 'Premium student accommodation located 5 minutes walking distance from UDSM campus. Features high-speed internet, security guards, electricity, water, and common rooms.',
            'region_id' => $dsm->id,
            'district_id' => $kinondoni->id,
            'ward_id' => $ubungo->id,
            'street' => 'Mlimani Road',
            'address' => 'UDSM Main Campus Road, Ubungo, Dar es Salaam',
            'latitude' => -6.7812,
            'longitude' => 39.2045,
            'nearby_institution_id' => $udsm->id,
            'distance_from_institution' => 0.40,
            'status' => 'published',
            'verification_status' => 'approved',
            'is_featured' => true,
            'rent_structure' => 'per_bed',
        ]);
        
        $prop1->amenities()->attach([$wifi->id, $water->id, $electricity->id, $cctv->id, $security->id, $study->id]);
        $prop1->managers()->attach($manager->id, ['role' => 'manager']);
        $prop1->managers()->attach($receptionist->id, ['role' => 'receptionist']);

        $bld1 = Building::create([
            'property_id' => $prop1->id,
            'building_name' => 'Block A',
            'building_code' => 'BLKA',
            'number_of_floors' => 2,
            'description' => 'Primary male residential block',
            'status' => 'active',
        ]);

        $fl1 = Floor::create([
            'building_id' => $bld1->id,
            'floor_number_name' => 'Ground Floor',
            'description' => 'Rooms 101 to 105',
            'status' => 'active',
        ]);

        $fl2 = Floor::create([
            'building_id' => $bld1->id,
            'floor_number_name' => '1st Floor',
            'description' => 'Rooms 201 to 205',
            'status' => 'active',
        ]);

        // Rooms on Ground Floor
        $rm101 = Room::create([
            'property_id' => $prop1->id,
            'building_id' => $bld1->id,
            'floor_id' => $fl1->id,
            'room_number' => 'A101',
            'room_type_id' => $double->id,
            'capacity' => 2,
            'occupancy' => 0,
            'description' => 'Spacious ground floor double room.',
            'gender_accommodation' => 'male',
            'furnished_status' => 'semi_furnished',
            'bathroom_type' => 'shared',
            'kitchen_type' => 'none',
            'price' => 150000.00,
            'billing_period' => 'monthly',
            'rental_model' => 'per_bed',
            'status' => 'active',
        ]);
        $rm101->amenities()->attach([$wifi->id, $study->id]);

        // Beds for A101
        Bed::create([
            'room_id' => $rm101->id,
            'bed_number' => 'A101-01',
            'price' => 150000.00,
            'billing_period' => 'monthly',
            'status' => 'available',
        ]);
        Bed::create([
            'room_id' => $rm101->id,
            'bed_number' => 'A101-02',
            'price' => 150000.00,
            'billing_period' => 'monthly',
            'status' => 'available',
        ]);

        // Room on 1st Floor
        $rm201 = Room::create([
            'property_id' => $prop1->id,
            'building_id' => $bld1->id,
            'floor_id' => $fl2->id,
            'room_number' => 'A201',
            'room_type_id' => $quad->id,
            'capacity' => 4,
            'occupancy' => 0,
            'description' => 'Spacious 4-person quad room.',
            'gender_accommodation' => 'male',
            'furnished_status' => 'fully_furnished',
            'bathroom_type' => 'private',
            'kitchen_type' => 'shared',
            'price' => 120000.00,
            'billing_period' => 'monthly',
            'rental_model' => 'per_bed',
            'status' => 'active',
        ]);
        $rm201->amenities()->attach([$wifi->id, $bathroom->id, $study->id]);

        // Beds for A201
        for ($i = 1; $i <= 4; $i++) {
            Bed::create([
                'room_id' => $rm201->id,
                'bed_number' => "A201-0$i",
                'price' => 120000.00,
                'billing_period' => 'monthly',
                'status' => 'available',
            ]);
        }

        // Property 2: Mwenge Heights (Apartments rented by whole room/unit)
        $prop2 = Property::create([
            'owner_id' => $owner->id,
            'property_name' => 'Mwenge Heights Apartments',
            'property_type_id' => $apartment->id,
            'description' => 'Modern apartments in Mwenge, suitable for professionals or students. Composed of 2-bedroom self-contained units with AC, high-end finishing, secure parking and water reserve.',
            'region_id' => $dsm->id,
            'district_id' => $kinondoni->id,
            'ward_id' => $mwenge->id,
            'street' => 'Mlalakuwa St',
            'address' => 'Plot 43, Mlalakuwa, Mwenge, Dar es Salaam',
            'latitude' => -6.7758,
            'longitude' => 39.2195,
            'nearby_institution_id' => $aru->id,
            'distance_from_institution' => 0.80,
            'status' => 'published',
            'verification_status' => 'approved',
            'is_featured' => true,
            'rent_structure' => 'per_room',
        ]);

        $prop2->amenities()->attach([$wifi->id, $water->id, $electricity->id, $parking->id, $cctv->id, $ac->id]);
        $prop2->managers()->attach($manager->id, ['role' => 'manager']);

        // Since it is an apartment building, it has multiple apartment units
        $apt1 = Room::create([
            'property_id' => $prop2->id,
            'room_number' => 'Apartment 1A',
            'room_type_id' => $studio->id,
            'capacity' => 1,
            'occupancy' => 0,
            'description' => 'Cozy self-contained studio apartment with kitchen and bathroom.',
            'gender_accommodation' => 'any',
            'furnished_status' => 'fully_furnished',
            'bathroom_type' => 'private',
            'kitchen_type' => 'private',
            'price' => 600000.00,
            'billing_period' => 'monthly',
            'rental_model' => 'per_room',
            'status' => 'active',
        ]);
        $apt1->amenities()->attach([$bathroom->id, $kitchen->id]);

        $apt2 = Room::create([
            'property_id' => $prop2->id,
            'room_number' => 'Apartment 2B',
            'room_type_id' => $studio->id,
            'capacity' => 1,
            'occupancy' => 0,
            'description' => 'Double bedroom apartment with private kitchen, balcony, and AC.',
            'gender_accommodation' => 'any',
            'furnished_status' => 'unfurnished',
            'bathroom_type' => 'private',
            'kitchen_type' => 'private',
            'price' => 850000.00,
            'billing_period' => 'monthly',
            'rental_model' => 'per_room',
            'status' => 'active',
        ]);
        $apt2->amenities()->attach([$bathroom->id, $kitchen->id]);

        // Property 3: Kijitonyama Studio Hub (Rented as a whole property/unit)
        $prop3 = Property::create([
            'owner_id' => $owner->id,
            'property_name' => 'Kijitonyama Co-Living House',
            'property_type_id' => $shared->id,
            'description' => 'Whole residential house in Kijitonyama available for rent. Ideal for groups or student networks who want to share a 3-bedroom bungalow near dit/city lines.',
            'region_id' => $dsm->id,
            'district_id' => $kinondoni->id,
            'ward_id' => $kijitonyama->id,
            'street' => 'Ali Hassan Mwinyi Rd',
            'address' => 'Bungalow 5, Kijitonyama, Dar es Salaam',
            'latitude' => -6.7720,
            'longitude' => 39.2312,
            'nearby_institution_id' => $aru->id,
            'distance_from_institution' => 2.10,
            'status' => 'published',
            'verification_status' => 'approved',
            'is_featured' => false,
            'rent_structure' => 'entire_property',
            'price' => 1500000.00,
            'billing_period' => 'monthly',
        ]);

        $prop3->amenities()->attach([$water->id, $electricity->id, $parking->id, $security->id]);

        // Create the rooms for reference
        Room::create([
            'property_id' => $prop3->id,
            'room_number' => 'Main House Unit',
            'room_type_id' => $aptRoom->id,
            'capacity' => 1,
            'occupancy' => 0,
            'description' => 'Whole bungalow unit, 3 bedrooms, large living area, kitchen.',
            'gender_accommodation' => 'any',
            'furnished_status' => 'semi_furnished',
            'bathroom_type' => 'shared',
            'kitchen_type' => 'private',
            'price' => 1500000.00,
            'billing_period' => 'monthly',
            'rental_model' => 'entire_property',
            'status' => 'active',
        ]);
    }
}
