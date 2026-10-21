@extends('layouts.admin')

@section('title', 'Owner Profile Onboarding')
@section('breadcrumb_title', 'Onboarding')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto" x-data="onboardingWizard()">
    <!-- Onboarding Progress Header -->
    <div class="border-b border-slate-200 pb-6 mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Owner Profile Onboarding</h1>
        <p class="mt-1 text-sm text-slate-500">
            Complete your onboarding details to unlock property listing and tenancy management features.
        </p>

        <!-- Progress Steps Bar -->
        <div class="mt-8 flex items-center justify-between">
            <!-- Step 1: Account (Done) -->
            <div class="flex items-center text-indigo-600">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-600 ring-4 ring-indigo-50/50">
                    <i data-lucide="check" class="w-5 h-5"></i>
                </span>
                <span class="ml-3 text-sm font-semibold hidden sm:inline">Account Created</span>
            </div>

            <div class="flex-1 border-t border-slate-200 mx-4" :class="step >= 2 ? 'border-indigo-600' : ''"></div>

            <!-- Step 2: Profile -->
            <div class="flex items-center" :class="step >= 2 ? 'text-indigo-600' : 'text-slate-400'">
                <span class="flex h-9 w-9 items-center justify-center rounded-full font-bold transition-all duration-300"
                      :class="step == 2 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 2 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    <span x-show="step <= 2">2</span>
                    <i data-lucide="check" class="w-5 h-5" x-show="step > 2" style="display:none;"></i>
                </span>
                <span class="ml-3 text-sm font-semibold hidden sm:inline" :class="step == 2 ? 'text-slate-900' : ''">Owner Profile</span>
            </div>

            <div class="flex-1 border-t border-slate-200 mx-4" :class="step >= 3 ? 'border-indigo-600' : ''"></div>

            <!-- Step 3: Verification -->
            <div class="flex items-center" :class="step >= 3 ? 'text-indigo-600' : 'text-slate-400'">
                <span class="flex h-9 w-9 items-center justify-center rounded-full font-bold transition-all duration-300"
                      :class="step == 3 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : 'bg-slate-100 text-slate-500'">
                    3
                </span>
                <span class="ml-3 text-sm font-semibold hidden sm:inline" :class="step == 3 ? 'text-slate-900' : ''">Verification</span>
            </div>
        </div>
    </div>

    <!-- Onboarding Panel -->
    <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/50 p-8 sm:p-10">
        <!-- Draft status alert -->
        <div class="mb-6 p-4 bg-amber-50/50 border border-amber-100 rounded-2xl flex items-center justify-between text-xs text-amber-800">
            <div class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                <span>All progress is automatically saved to the database. You can exit and return to resume later.</span>
            </div>
            @if(isset($draft->updated_at))
                <span class="text-slate-400">Last saved: {{ $draft->updated_at->diffForHumans() }}</span>
            @endif
        </div>

        <form id="onboardingForm" method="POST" action="{{ route('owner.onboarding.save-draft') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="current_step" :value="step">
            <input type="hidden" name="action" x-model="action">

            <!-- STEP 2: PROFILE DETAILS -->
            <div x-show="step == 2" x-transition.fade.duration.300ms class="space-y-6">
                <h3 class="text-lg font-bold text-slate-900">Legal Profile Details</h3>
                <p class="text-xs text-slate-500">Provide legal identification details matching your official ID cards.</p>

                <!-- Profile Photo Upload -->
                <div class="flex items-center gap-6" x-data="{ photoPreview: '{{ $draft->data['profile_photo'] ?? '' ? asset('storage/' . $draft->data['profile_photo']) : '' }}' }">
                    <div class="relative">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" class="w-24 h-24 rounded-2xl object-cover border border-slate-200">
                        </template>
                        <template x-if="!photoPreview">
                            <div class="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-400">
                                <i data-lucide="user" class="w-10 h-10"></i>
                            </div>
                        </template>
                        <label for="profile_photo" class="absolute -bottom-2 -right-2 p-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-lg shadow-sm cursor-pointer transition-colors">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="sr-only" 
                                   @change="const file = $event.target.files[0]; if (file) { const r = new FileReader(); r.onload = (e) => { photoPreview = e.target.result; }; r.readAsDataURL(file); }">
                        </label>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Profile Photo</h4>
                        <p class="text-xs text-slate-500 mt-1">Upload a clear passport-style portrait photo.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Legal Name -->
                    <div>
                        <label for="full_legal_name" class="block text-sm font-semibold text-slate-700">Full Legal Name</label>
                        <input id="full_legal_name" name="full_legal_name" type="text" required value="{{ old('full_legal_name', $draft->data['full_legal_name'] ?? '') }}" placeholder="e.g. John Doe"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <!-- National ID / NIDA -->
                    <div>
                        <label for="nida" class="block text-sm font-semibold text-slate-700">National ID / NIDA Number</label>
                        <input id="nida" name="nida" type="text" required value="{{ old('nida', $draft->data['nida'] ?? '') }}" placeholder="NIDA Card Number"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label for="nationality" class="block text-sm font-semibold text-slate-700">Nationality</label>
                        <input id="nationality" name="nationality" type="text" required value="{{ old('nationality', $draft->data['nationality'] ?? 'Tanzanian') }}" placeholder="Nationality"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Official Email</label>
                        <input id="email" name="email" type="email" required value="{{ old('email', $draft->data['email'] ?? $draft->user->email) }}" placeholder="e.g. owner@example.com"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700">Phone Number</label>
                        <input id="phone" name="phone" type="text" required value="{{ old('phone', $draft->data['phone'] ?? $draft->user->phone) }}" placeholder="+255..."
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <!-- WhatsApp Number -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-slate-700">WhatsApp Number (Optional)</label>
                        <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $draft->data['whatsapp'] ?? $draft->user->whatsapp) }}" placeholder="+255..."
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                </div>

                <!-- Location details -->
                <div class="space-y-4 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Address details</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Region -->
                        <div>
                            <label for="region_id" class="block text-sm font-semibold text-slate-700">Region</label>
                            <select id="region_id" name="region_id" x-model="regionId" @change="districtId = ''; wardId = ''" required
                                    class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                                <option value="" disabled selected>Select Region...</option>
                                @foreach($regions as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- District -->
                        <div>
                            <label for="district_id" class="block text-sm font-semibold text-slate-700">District</label>
                            <select id="district_id" name="district_id" x-model="districtId" @change="wardId = ''" required :disabled="!regionId"
                                    class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                                <option value="" disabled selected>Select District...</option>
                                <template x-for="d in filteredDistricts" :key="d.id">
                                    <option :value="d.id" x-text="d.name" :selected="d.id == districtId"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Ward -->
                        <div>
                            <label for="ward_id" class="block text-sm font-semibold text-slate-700">Ward</label>
                            <select id="ward_id" name="ward_id" x-model="wardId" required :disabled="!districtId"
                                    class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                                <option value="" disabled selected>Select Ward...</option>
                                <template x-for="w in filteredWards" :key="w.id">
                                    <option :value="w.id" x-text="w.name" :selected="w.id == wardId"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="street_address" class="block text-sm font-semibold text-slate-700">Street / Physical Address</label>
                        <textarea id="street_address" name="street_address" rows="3" placeholder="Plot, House No, Road, Landmark..."
                                  class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-655 focus:border-indigo-600 sm:text-sm">{{ old('street_address', $draft->data['street_address'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 3: DOCUMENT UPLOAD -->
            <div x-show="step == 3" x-transition.fade.duration.300ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Verification Documents</h3>
                <p class="text-xs text-slate-500 font-medium">Upload scan copies of verification documents. Admin audits will confirm their validity.</p>

                <!-- National ID File Upload -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700">National ID Scan (NIDA or Passport) <span class="text-rose-500">*</span></label>
                    <div class="mt-2 flex justify-center rounded-3xl border-2 border-dashed border-slate-200 px-6 py-10 hover:border-indigo-500 hover:bg-indigo-50/5 transition-all duration-300 relative cursor-pointer group">
                        <input id="id_document" name="id_document" type="file" accept="image/*,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="idDocName = $event.target.files[0] ? $event.target.files[0].name : ''">
                        <div class="text-center space-y-2 pointer-events-none">
                            <div class="inline-flex p-3 bg-white border border-slate-100 rounded-2xl shadow-sm text-slate-400 group-hover:text-indigo-600 transition-colors">
                                <i data-lucide="file-up" class="w-8 h-8"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-600">
                                <span class="text-indigo-600 hover:underline">Upload NIDA ID Card or Passport</span>
                            </div>
                            <p class="text-xs text-slate-400">PDF, JPG, PNG up to 5MB</p>
                            <!-- Show selected filename or existing path -->
                            <p class="text-xs font-bold text-slate-700 mt-2" x-text="idDocName || '{{ basename($draft->data['id_document_path'] ?? 'No document uploaded yet') }}'"></p>
                        </div>
                    </div>
                </div>

                <!-- Supporting Document (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Supporting Documents (Optional)</label>
                    <div class="mt-2 flex justify-center rounded-3xl border-2 border-dashed border-slate-200 px-6 py-10 hover:border-indigo-500 hover:bg-indigo-50/5 transition-all duration-300 relative cursor-pointer group">
                        <input id="supporting_document" name="supporting_document" type="file" accept="image/*,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="supDocName = $event.target.files[0] ? $event.target.files[0].name : ''">
                        <div class="text-center space-y-2 pointer-events-none">
                            <div class="inline-flex p-3 bg-white border border-slate-100 rounded-2xl shadow-sm text-slate-400 group-hover:text-indigo-600 transition-colors">
                                <i data-lucide="file-plus" class="w-8 h-8"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-600">
                                <span class="text-indigo-600 hover:underline">Upload Company registration, land title, or utility bills</span>
                            </div>
                            <p class="text-xs text-slate-400">PDF, JPG, PNG up to 5MB</p>
                            <!-- Show selected filename or existing path -->
                            <p class="text-xs font-bold text-slate-700 mt-2" x-text="supDocName || '{{ basename($draft->data['supporting_document_path'] ?? 'No document uploaded yet') }}'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error outputs -->
            @if ($errors->any())
                <div class="mt-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                    <ul class="list-disc pl-5 text-xs text-rose-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Navigation Actions -->
            <div class="pt-8 mt-8 border-t border-slate-150 flex items-center justify-between gap-4">
                <button type="button" @click="saveDraftAction()" class="px-5 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                    Save as Draft & Exit
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" x-show="step > 2" @click="prevAction()" class="px-5 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors" style="display:none;">
                        Previous
                    </button>

                    <button type="button" x-show="step < 3" @click="nextAction()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                        <span>Save & Continue</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>

                    <button type="button" x-show="step == 3" @click="submitAction()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-all" style="display:none;">
                        <span>Submit for Verification</span>
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('scripts')
<script>
    function onboardingWizard() {
        return {
            step: {{ $draft->current_step ?? 2 }},
            action: 'save_draft',
            idDocName: '',
            supDocName: '',
            regionId: '',
            districtId: '',
            wardId: '',
            regions: @json($regions),
            districts: @json($districts),
            wards: @json($wards),

            init() {
                // Safely restore initial state step-by-step to avoid race conditions
                const initialRegion = '{{ old('region_id', $draft->data['region_id'] ?? '') }}';
                const initialDistrict = '{{ old('district_id', $draft->data['district_id'] ?? '') }}';
                const initialWard = '{{ old('ward_id', $draft->data['ward_id'] ?? '') }}';

                if (initialRegion) {
                    this.regionId = initialRegion;
                    this.$nextTick(() => {
                        if (initialDistrict) {
                            this.districtId = initialDistrict;
                            this.$nextTick(() => {
                                if (initialWard) {
                                    this.wardId = initialWard;
                                }
                            });
                        }
                    });
                }
            },

            get filteredDistricts() {
                if (!this.regionId) return [];
                return this.districts.filter(d => d.region_id == this.regionId);
            },

            get filteredWards() {
                if (!this.districtId) return [];
                return this.wards.filter(w => w.district_id == this.districtId);
            },

            nextAction() {
                this.action = 'next';
                this.$nextTick(() => {
                    document.getElementById('onboardingForm').submit();
                });
            },

            prevAction() {
                this.action = 'previous';
                this.$nextTick(() => {
                    document.getElementById('onboardingForm').submit();
                });
            },

            saveDraftAction() {
                this.action = 'save_draft';
                this.$nextTick(() => {
                    document.getElementById('onboardingForm').submit();
                });
            },

            submitAction() {
                const form = document.getElementById('onboardingForm');
                form.action = "{{ route('owner.onboarding.submit') }}";
                form.submit();
            }
        }
    }
</script>
@endsection
