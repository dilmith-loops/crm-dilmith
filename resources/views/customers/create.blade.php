@extends('layouts.app')

@section('header', 'Add New Customer')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 bg-gradient-to-r from-brand-blue to-brand-teal border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Create New Customer</h3>
                        <p class="text-sm text-white/80 mt-1">Add comprehensive customer information</p>
                    </div>
                    <a href="{{ route('customers.index') }}"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-medium transition-all backdrop-blur-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="p-8">
                @csrf

                <!-- Company Information Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-blue">
                        <div class="w-10 h-10 bg-brand-blue/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-building text-brand-blue"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Customer Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Name of Customer <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Name of Customer">
                            @error('name') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="billing_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                Billing Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="billing_address" id="billing_address"
                                value="{{ old('billing_address') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Billing Address">
                            @error('billing_address') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">
                                Brand <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="brand" id="brand"
                                value="{{ old('brand') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Brand Name">
                            @error('brand') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- General Office Contact Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-purple">
                        <div class="w-10 h-10 bg-brand-purple/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-phone text-brand-purple"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">General Office Contact</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="telephone" class="block text-sm font-semibold text-gray-700 mb-2">
                                Telephone No <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Telephone" pattern="[0-9\s\+\-\(\)]+" title="Please enter a valid phone number (digits, spaces, and +, -, (, ) are allowed)">
                            @error('telephone') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="fax" class="block text-sm font-semibold text-gray-700 mb-2">
                                Fax
                            </label>
                            <input type="text" name="fax" id="fax" value="{{ old('fax') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Fax" pattern="[0-9\s\+\-\(\)]*" title="Please enter a valid fax number">
                            @error('fax') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Email">
                            @error('email') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="business_registration_number" class="block text-sm font-semibold text-gray-700 mb-2">
                            Business Registration Number
                        </label>
                        <input type="text" name="business_registration_number" id="business_registration_number"
                            value="{{ old('business_registration_number') }}"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                            placeholder="Business Registration Number">
                        @error('business_registration_number') <p class="mt-2 text-sm text-red-600"><i
                        class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Primary Point of Contact Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-pink">
                        <div class="w-10 h-10 bg-brand-pink/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-tie text-brand-pink"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Primary Point of Contact</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="primary_contact_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Name
                            </label>
                            <input type="text" name="primary_contact_name" id="primary_contact_name"
                                value="{{ old('primary_contact_name') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all"
                                placeholder="Name">
                            @error('primary_contact_name') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="primary_contact_designation" class="block text-sm font-semibold text-gray-700 mb-2">
                                Designation
                            </label>
                            <input type="text" name="primary_contact_designation" id="primary_contact_designation"
                                value="{{ old('primary_contact_designation') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all"
                                placeholder="Designation">
                            @error('primary_contact_designation') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="primary_contact_mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                                Mobile <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="primary_contact_mobile" id="primary_contact_mobile"
                                value="{{ old('primary_contact_mobile') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all"
                                placeholder="Mobile" pattern="[0-9\s\+\-\(\)]+" title="Please enter a valid mobile number">
                            @error('primary_contact_mobile') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="primary_contact_office" class="block text-sm font-semibold text-gray-700 mb-2">
                                Office
                            </label>
                            <input type="text" name="primary_contact_office" id="primary_contact_office"
                                value="{{ old('primary_contact_office') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all"
                                placeholder="Office" pattern="[0-9\s\+\-\(\)]*" title="Please enter a valid office number">
                            @error('primary_contact_office') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="primary_contact_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="primary_contact_email" id="primary_contact_email"
                                value="{{ old('primary_contact_email') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all"
                                placeholder="Email Address">
                            @error('primary_contact_email') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Finance Point of Contact Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-teal">
                        <div class="w-10 h-10 bg-brand-teal/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-invoice-dollar text-brand-teal"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Finance Point of Contact</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="finance_contact_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Name
                            </label>
                            <input type="text" name="finance_contact_name" id="finance_contact_name"
                                value="{{ old('finance_contact_name') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition-all"
                                placeholder="Name">
                            @error('finance_contact_name') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="finance_contact_designation" class="block text-sm font-semibold text-gray-700 mb-2">
                                Designation
                            </label>
                            <input type="text" name="finance_contact_designation" id="finance_contact_designation"
                                value="{{ old('finance_contact_designation') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition-all"
                                placeholder="Designation">
                            @error('finance_contact_designation') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="finance_contact_mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                                Mobile
                            </label>
                            <input type="text" name="finance_contact_mobile" id="finance_contact_mobile"
                                value="{{ old('finance_contact_mobile') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition-all"
                                placeholder="Mobile" pattern="[0-9\s\+\-\(\)]*" title="Please enter a valid mobile number">
                            @error('finance_contact_mobile') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="finance_contact_office" class="block text-sm font-semibold text-gray-700 mb-2">
                                Office
                            </label>
                            <input type="text" name="finance_contact_office" id="finance_contact_office"
                                value="{{ old('finance_contact_office') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition-all"
                                placeholder="Office" pattern="[0-9\s\+\-\(\)]*" title="Please enter a valid office number">
                            @error('finance_contact_office') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="finance_contact_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" name="finance_contact_email" id="finance_contact_email"
                                value="{{ old('finance_contact_email') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition-all"
                                placeholder="Email Address">
                            @error('finance_contact_email') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Tax Information Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-purple">
                        <div class="w-10 h-10 bg-brand-purple/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-invoice-dollar text-brand-purple"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Customer Tax Numbers</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="customer_tax_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                Customer Tax Number
                            </label>
                            <input type="text" name="customer_tax_number" id="customer_tax_number"
                                value="{{ old('customer_tax_number') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Tax Number">
                            @error('customer_tax_number') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_vat_registration_number"
                                class="block text-sm font-semibold text-gray-700 mb-2">
                                Customer VAT Registration Number
                            </label>
                            <input type="text" name="customer_vat_registration_number" id="customer_vat_registration_number"
                                value="{{ old('customer_vat_registration_number') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Customer VAT Registration Number">
                            @error('customer_vat_registration_number') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_suspended_vat_registration_number"
                                class="block text-sm font-semibold text-gray-700 mb-2">
                                Customer Suspended VAT Registration Number
                            </label>
                            <input type="text" name="customer_suspended_vat_registration_number"
                                id="customer_suspended_vat_registration_number"
                                value="{{ old('customer_suspended_vat_registration_number') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all"
                                placeholder="Customer Suspended VAT Registration Number">
                            @error('customer_suspended_vat_registration_number') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Credit Terms Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-blue">
                        <div class="w-10 h-10 bg-brand-blue/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-brand-blue"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Credit Terms</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="approved_credit_period" class="block text-sm font-semibold text-gray-700 mb-2">
                                Approved Credit Period
                            </label>
                            <input type="text" name="approved_credit_period" id="approved_credit_period"
                                value="{{ old('approved_credit_period') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Approved Credit Period">
                            @error('approved_credit_period') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="approved_credit_limit" class="block text-sm font-semibold text-gray-700 mb-2">
                                Approved Credit Limit
                            </label>
                            <input type="number" step="0.01" name="approved_credit_limit" id="approved_credit_limit"
                                value="{{ old('approved_credit_limit') }}"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Approved Credit Limit">
                            @error('approved_credit_limit') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('customers.index') }}"
                        class="px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 rounded-lg bg-gradient-to-r from-brand-blue to-brand-teal text-white font-bold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i>Add Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection