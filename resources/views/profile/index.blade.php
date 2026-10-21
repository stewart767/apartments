@extends('layouts.admin')

@section('title', 'Profile Management')
@section('page_title', 'Profile Management')
@section('breadcrumb_title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4" x-data="profileEditor()">
    
    <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/50 overflow-hidden">
        <!-- Profile Header Cover -->
        <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>

        <form method="POST" action="{{ route('profile') }}" enctype="multipart/form-data" class="p-8 sm:p-10 -mt-16 space-y-8">
            @csrf

            <!-- Profile Photo & Basic Meta -->
            <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-100" x-data="{ avatarPreview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}' }">
                <div class="relative">
                    <template x-if="avatarPreview">
                        <img :src="avatarPreview" class="w-28 h-28 rounded-2xl object-cover border-4 border-white shadow-md">
                    </template>
                    <template x-if="!avatarPreview">
                        <div class="w-28 h-28 rounded-2xl bg-indigo-600 text-white font-bold text-3xl flex items-center justify-center border-4 border-white shadow-md">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </template>
                    <label for="avatar" class="absolute -bottom-2 -right-2 p-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-xl shadow-md cursor-pointer transition-colors z-20">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <input id="avatar" name="avatar" type="file" accept="image/*" class="sr-only"
                               @change="const file = $event.target.files[0]; if (file) { const r = new FileReader(); r.onload = (e) => { avatarPreview = e.target.result; }; r.readAsDataURL(file); }">
                    </label>
                </div>
                <div class="text-center sm:text-left mt-2 sm:mt-8">
                    <h3 class="text-xl font-bold text-slate-900">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-400 capitalize font-semibold tracking-wider mt-0.5">{{ $user->role }}</p>
                </div>
            </div>

            <!-- SECTION 1: ACCOUNT DETAILS -->
            <div class="space-y-6">
                <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Account Details</span>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700">Full Name</label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email (Read-Only) -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Email Address (Cannot change)</label>
                        <input type="email" disabled value="{{ $user->email }}"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-100 bg-slate-50 rounded-xl text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700">Phone Number</label>
                        <input id="phone" name="phone" type="text" required value="{{ old('phone', $user->phone) }}"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-slate-700">WhatsApp (Optional)</label>
                        <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('whatsapp')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-slate-700">Gender</label>
                        <select id="gender" name="gender" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-slate-700">Date of Birth</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" required value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('date_of_birth')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PASSWORD CHANGE (OPTIONAL) -->
            <div class="space-y-6 pt-6 border-t border-slate-100" x-data="{ open: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                        <span>Change Password (Optional)</span>
                    </h4>
                    <span class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="open" x-collapse x-cloak>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">New Password</label>
                        <input id="password" name="password" type="password" placeholder="Leave blank to keep current"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: CONDITIONAL TENANT PROFILE -->
            @if($user->isTenant())
                @php
                    $detail = $user->tenantDetail;
                @endphp
                <div class="space-y-6 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Tenant Profile Details</span>
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- National ID / Passport -->
                        <div>
                            <label for="national_id_passport" class="block text-sm font-semibold text-slate-700">National ID / Passport</label>
                            <input id="national_id_passport" name="national_id_passport" type="text" required value="{{ old('national_id_passport', $detail->national_id_passport ?? '') }}"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('national_id_passport')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Emergency Contact -->
                        <div>
                            <label for="emergency_contact" class="block text-sm font-semibold text-slate-700">Emergency Contact</label>
                            <input id="emergency_contact" name="emergency_contact" type="text" required value="{{ old('emergency_contact', $detail->emergency_contact ?? '') }}"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('emergency_contact')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Student Switch Toggler -->
                    <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900">Are you a Student?</span>
                            <span class="text-xs text-slate-500 mt-0.5">Toggle to link your university details</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_student" value="1" x-model="isStudent" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <!-- Conditional Student Fields -->
                    <div x-show="isStudent" x-collapse x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 border border-slate-100 rounded-2xl">
                        <!-- Institution Name Selection -->
                        <div>
                            <label for="institution_name" class="block text-sm font-semibold text-slate-700">Select/Type Institution</label>
                            <input id="institution_name" name="institution_name" list="institutions_list" :required="isStudent"
                                   value="{{ old('institution_name', $detail && $detail->institution ? $detail->institution->name : '') }}" placeholder="Select or type..."
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <datalist id="institutions_list">
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->name }}"></option>
                                @endforeach
                            </datalist>
                            @error('institution_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Student Registration Number -->
                        <div>
                            <label for="student_id" class="block text-sm font-semibold text-slate-700">Student ID / Registration Number</label>
                            <input id="student_id" name="student_id" type="text" :required="isStudent"
                                   value="{{ old('student_id', $detail->student_reg_number ?? '') }}" placeholder="Student ID Number"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('student_id')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- SECTION 4: CONDITIONAL OWNER PROFILE -->
            @if($user->isOwner() && $user->ownerDetail)
                @php
                    $detail = $user->ownerDetail;
                @endphp
                <div class="space-y-6 pt-6 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Owner Profile Details</span>
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Legal Name -->
                        <div>
                            <label for="full_legal_name" class="block text-sm font-semibold text-slate-700">Full Legal Name</label>
                            <input id="full_legal_name" name="full_legal_name" type="text" required value="{{ old('full_legal_name', $detail->full_legal_name ?? '') }}"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('full_legal_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIDA -->
                        <div>
                            <label for="nida" class="block text-sm font-semibold text-slate-700">National ID / NIDA Number</label>
                            <input id="nida" name="nida" type="text" required value="{{ old('nida', $detail->nida ?? '') }}"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('nida')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nationality -->
                        <div>
                            <label for="nationality" class="block text-sm font-semibold text-slate-700">Nationality</label>
                            <input id="nationality" name="nationality" type="text" required value="{{ old('nationality', $detail->nationality ?? 'Tanzanian') }}"
                                   class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            @error('nationality')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Owner Address Details -->
                    <div class="space-y-4 pt-4 border-t border-slate-50">
                        <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registered Physical Address</h5>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Region -->
                            <div>
                                <label for="region_id" class="block text-sm font-semibold text-slate-700">Region</label>
                                <select id="region_id" name="region_id" x-model="regionId" @change="districtId = ''; wardId = ''" required
                                        class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                                    <option value="" disabled>Select Region...</option>
                                    <template x-for="r in regions" :key="r.id">
                                        <option :value="r.id" x-text="r.name" :selected="r.id == regionId"></option>
                                    </template>
                                </select>
                                @error('region_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label for="district_id" class="block text-sm font-semibold text-slate-700">District</label>
                                <select id="district_id" name="district_id" x-model="districtId" @change="wardId = ''" required :disabled="!regionId"
                                        class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                                    <option value="" disabled>Select District...</option>
                                    <template x-for="d in filteredDistricts" :key="d.id">
                                        <option :value="d.id" x-text="d.name" :selected="d.id == districtId"></option>
                                    </template>
                                </select>
                                @error('district_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ward -->
                            <div>
                                <label for="ward_id" class="block text-sm font-semibold text-slate-700">Ward</label>
                                <select id="ward_id" name="ward_id" x-model="wardId" required :disabled="!districtId"
                                        class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                                    <option value="" disabled>Select Ward...</option>
                                    <template x-for="w in filteredWards" :key="w.id">
                                        <option :value="w.id" x-text="w.name" :selected="w.id == wardId"></option>
                                    </template>
                                </select>
                                @error('ward_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="street_address" class="block text-sm font-semibold text-slate-700">Street / Physical Address</label>
                            <textarea id="street_address" name="street_address" rows="3" placeholder="Plot, House No, Road..."
                                      class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">{{ old('street_address', $detail->street_address ?? '') }}</textarea>
                            @error('street_address')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Owner Documents Update -->
                    <div class="space-y-6 pt-4 border-t border-slate-50">
                        <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Verification Documents</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Profile Photo (Legal) -->
                            <div x-data="{ photoName: '' }">
                                <label class="block text-sm font-semibold text-slate-700">Update Profile Photo</label>
                                <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-slate-250 px-6 py-8 hover:border-indigo-500 transition-colors relative cursor-pointer group">
                                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="photoName = $event.target.files[0].name">
                                    <div class="text-center space-y-1.5 pointer-events-none">
                                        <i data-lucide="image" class="w-6 h-6 mx-auto text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                                        <div class="text-xs font-bold text-slate-700">
                                            <span class="text-indigo-600 hover:underline">Upload a new photo</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400" x-text="photoName || '{{ $detail->profile_photo ? basename($detail->profile_photo) : 'JPG, PNG up to 2MB' }}'"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- ID Document -->
                            <div x-data="{ docName: '' }">
                                <label class="block text-sm font-semibold text-slate-700">Update National ID / Passport scan</label>
                                <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-slate-250 px-6 py-8 hover:border-indigo-500 transition-colors relative cursor-pointer group">
                                    <input id="id_document" name="id_document" type="file" accept="image/*,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="docName = $event.target.files[0].name">
                                    <div class="text-center space-y-1.5 pointer-events-none">
                                        <i data-lucide="file-up" class="w-6 h-6 mx-auto text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                                        <div class="text-xs font-bold text-slate-700">
                                            <span class="text-indigo-600 hover:underline">Upload a new ID scan</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400" x-text="docName || '{{ $detail->id_document_path ? basename($detail->id_document_path) : 'PDF, JPG, PNG up to 5MB' }}'"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Supporting Document -->
                            <div x-data="{ supName: '' }" class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">Update Supporting Documents</label>
                                <div class="mt-2 flex justify-center rounded-2xl border-2 border-dashed border-slate-250 px-6 py-8 hover:border-indigo-500 transition-colors relative cursor-pointer group">
                                    <input id="supporting_document" name="supporting_document" type="file" accept="image/*,application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="supName = $event.target.files[0].name">
                                    <div class="text-center space-y-1.5 pointer-events-none">
                                        <i data-lucide="file-plus" class="w-6 h-6 mx-auto text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                                        <div class="text-xs font-bold text-slate-700">
                                            <span class="text-indigo-600 hover:underline">Upload supporting files</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400" x-text="supName || '{{ $detail->supporting_document_path ? basename($detail->supporting_document_path) : 'Company reg, Land title, Utility bills' }}'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Section -->
            <div class="pt-6 border-t border-slate-150 flex justify-end">
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Profile Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function profileEditor() {
        return {
            isStudent: {{ ($user->isTenant() && $user->tenantDetail && $user->tenantDetail->is_student) ? 'true' : 'false' }},
            regionId: '',
            districtId: '',
            wardId: '',
            regions: @json($regions),
            districts: @json($districts),
            wards: @json($wards),

            init() {
                const initialRegion = '{{ old('region_id', ($user->isOwner() && $user->ownerDetail) ? $user->ownerDetail->region_id : '') }}';
                const initialDistrict = '{{ old('district_id', ($user->isOwner() && $user->ownerDetail) ? $user->ownerDetail->district_id : '') }}';
                const initialWard = '{{ old('ward_id', ($user->isOwner() && $user->ownerDetail) ? $user->ownerDetail->ward_id : '') }}';

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
            }
        }
    }
</script>
@endsection
