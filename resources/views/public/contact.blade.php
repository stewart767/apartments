@extends('layouts.public')

@section('title', 'Contact Support - RelianceHomes')
@section('meta_description', 'Get in touch with RelianceHomes customer care. We are here to help property owners list rooms and tenants book spaces.')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-slate-950 py-24 sm:py-32">
    <!-- Glowing background elements -->
    <div class="absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-indigo-600/10 blur-3xl"></div>
    <div class="absolute top-1/2 right-0 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-purple-600/10 blur-3xl"></div>
    
    <div class="mx-auto max-w-7xl px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-400 ring-1 ring-inset ring-indigo-500/25">
            Contact Support
        </span>
        <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-6xl bg-gradient-to-r from-white via-slate-100 to-indigo-200 bg-clip-text text-transparent">
            Get In Touch
        </h1>
        <p class="mt-6 text-lg leading-8 text-slate-400 max-w-2xl mx-auto">
            Have questions about room verification audits, payments, digital leasing, or room listings? Talk to our team.
        </p>
    </div>
</div>

<!-- Info Cards + Contact Form -->
<div class="bg-white py-20 sm:py-28 border-b border-slate-100">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-stretch">
            
            <!-- Left Column: Contact Cards -->
            <div class="flex flex-col justify-between space-y-8">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-full text-xs font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Office Status: Open Now
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        RelianceHomes Headquarters
                    </h2>
                    <p class="text-base text-slate-500">
                        Located at Ubungo Plaza, Dar es Salaam, Tanzania. Drop in for physical registration, platform partnership meetings, or detailed compliance checks.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                    <!-- Phone card -->
                    <div class="group p-6 bg-slate-50 hover:bg-indigo-50/40 rounded-3xl border border-slate-100 transition-all duration-300">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 group-hover:bg-indigo-650 group-hover:text-white transition-colors duration-300">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <h3 class="mt-4 font-bold text-slate-900">Phone & WhatsApp</h3>
                        <p class="mt-2 text-xs text-slate-500">+255 712 345 678</p>
                        <p class="text-[10px] text-slate-400">Mon - Fri, 8AM - 5PM</p>
                    </div>

                    <!-- Email card -->
                    <div class="group p-6 bg-slate-50 hover:bg-indigo-50/40 rounded-3xl border border-slate-100 transition-all duration-300">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 group-hover:bg-indigo-650 group-hover:text-white transition-colors duration-300">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <h3 class="mt-4 font-bold text-slate-900">Email Address</h3>
                        <p class="mt-2 text-xs text-slate-500">support@reliancehomes.co.tz</p>
                        <p class="text-[10px] text-slate-400">Response within 2 hours</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Glassmorphic Contact Form -->
            <div x-data="{ 
                name: '', 
                email: '', 
                message: '', 
                submitted: false,
                submitForm() {
                    if (this.name && this.email && this.message) {
                        this.submitted = true;
                        // Simulate sending details
                        setTimeout(() => {
                            this.name = '';
                            this.email = '';
                            this.message = '';
                            this.submitted = false;
                            alert('Thank you! Your message has been sent to the RelianceHomes support team.');
                        }, 850);
                    }
                }
            }" class="relative bg-slate-50 border border-slate-100 p-8 sm:p-10 rounded-3xl shadow-xl shadow-slate-100">
                <h3 class="text-xl font-bold text-slate-900 mb-6">Send Us A Message</h3>
                
                <form @submit.prevent="submitForm()" class="space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Full Name</label>
                        <input type="text" x-model="name" id="name" required placeholder="e.g. John Doe"
                               class="mt-2 block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 bg-white transition-all">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Email Address</label>
                        <input type="email" x-model="email" id="email" required placeholder="e.g. john@example.com"
                               class="mt-2 block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 bg-white transition-all">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Message Details</label>
                        <textarea x-model="message" id="message" rows="4" required placeholder="Describe your question or issue in detail..."
                                  class="mt-2 block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 bg-white transition-all"></textarea>
                    </div>

                    <button type="submit" :disabled="submitted" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md shadow-indigo-100 hover:shadow-lg disabled:opacity-50">
                        <i data-lucide="send" class="w-4 h-4" x-show="!submitted"></i>
                        <span x-text="submitted ? 'Sending...' : 'Send Message'">Send Message</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Interactive Maps Section -->
<div class="bg-slate-50 py-16">
    <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center space-y-6">
        <div class="max-w-3xl mx-auto space-y-2">
            <h3 class="text-2xl font-bold text-slate-900">Find Our Headquarters</h3>
            <p class="text-sm text-slate-500">We are located on the ground floor of Ubungo Plaza Building along Morogoro Road.</p>
        </div>
        <div class="w-full h-[400px] rounded-3xl overflow-hidden border border-slate-200/60 shadow-lg relative bg-slate-200">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.644131557008!2d39.21980831140928!3d-6.813045666611388!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x185c4c23dbb7af5f%3A0xb351e604f323a67d!2sUbungo%20Plaza%20Ltd!5e0!3m2!1sen!2stz!4v1714000000000!5m2!1sen!2stz" 
                class="absolute inset-0 w-full h-full border-0" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>

<!-- Interactive FAQs (Alpine.js Accordion) -->
<div class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-4xl px-6 lg:px-8">
        <div class="text-center space-y-4 mb-16">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Frequently Asked Questions</h2>
            <p class="text-slate-500">Quick answers to common questions about RelianceHomes accommodation services.</p>
        </div>

        <div x-data="{ activeFaq: null }" class="space-y-4">
            <!-- FAQ 1 -->
            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-slate-50 hover:bg-slate-100/30 transition-colors">
                <button @click="activeFaq = activeFaq === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left font-bold text-slate-900">
                    <span>How do I know an accommodation is physically verified?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeFaq === 1 ? 'rotate-180 text-indigo-600' : ''"></i>
                </button>
                <div x-show="activeFaq === 1" x-transition x-cloak class="px-6 pb-6 text-sm text-slate-655 leading-relaxed">
                    Look for the green "Verified" badge on the listing details page. This badge indicates our compliance team has physically visited the property, audited amenities (water, electricity, security guard presence), verified landlord credentials, and matched structural logs to prevent fraud.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-slate-50 hover:bg-slate-100/30 transition-colors">
                <button @click="activeFaq = activeFaq === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left font-bold text-slate-900">
                    <span>How does the booking shield prevent double bookings?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeFaq === 2 ? 'rotate-180 text-indigo-600' : ''"></i>
                </button>
                <div x-show="activeFaq === 2" x-transition x-cloak class="px-6 pb-6 text-sm text-slate-655 leading-relaxed">
                    Once you submit an application, the target bed or room goes into a "Reserved" state in our database. Other users will not be able to apply for that specific slot until your reservation expires, is rejected, or is processed. Upon approval, the bed moves to "Occupied".
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-slate-50 hover:bg-slate-100/30 transition-colors">
                <button @click="activeFaq = activeFaq === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left font-bold text-slate-900">
                    <span>Is my lease agreement legally binding?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeFaq === 3 ? 'rotate-180 text-indigo-600' : ''"></i>
                </button>
                <div x-show="activeFaq === 3" x-transition x-cloak class="px-6 pb-6 text-sm text-slate-655 leading-relaxed">
                    Yes. RelianceHomes compiles standard digital lease agreements approved by legal advisors. Both landlord and tenant sign using double-factor authorization hashes, producing a legally binding PDF contract download.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-slate-100 rounded-2xl overflow-hidden bg-slate-50 hover:bg-slate-100/30 transition-colors">
                <button @click="activeFaq = activeFaq === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left font-bold text-slate-900">
                    <span>How can I report a maintenance issue in my room?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeFaq === 4 ? 'rotate-180 text-indigo-600' : ''"></i>
                </button>
                <div x-show="activeFaq === 4" x-transition x-cloak class="px-6 pb-6 text-sm text-slate-655 leading-relaxed">
                    Logged-in tenants have access to a "Maintenance" tab on their dashboard. You can create a request ticket, upload images of the issue, and trace live updates as managers allocate support staff to resolve the issue.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
