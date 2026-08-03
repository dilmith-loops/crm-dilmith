@extends('layouts.app')

@section('header', 'System Settings')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 pb-12">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <!-- Sidebar Navigation for Settings -->
            <div class="col-span-1 space-y-2">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wider px-1 sm:px-3 mb-2">Management</h3>
                <div class="flex flex-row overflow-x-auto whitespace-nowrap pb-2 md:pb-0 gap-2 md:flex-col md:space-y-2 md:gap-0 -mx-2 px-2 md:mx-0 md:px-0">
                    <button onclick="showSection('general')"
                        class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                        id="btn-general">
                        <i class="fas fa-building mr-2 text-brand-blue"></i> Company & Branding
                    </button>
                    <button onclick="showSection('tax')"
                        class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                        id="btn-tax">
                        <i class="fas fa-percentage mr-2 text-green-600"></i> Tax Rates (VAT/SSCL)
                    </button>
                    <button onclick="showSection('terms')"
                        class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                        id="btn-terms">
                        <i class="fas fa-file-contract mr-2 text-brand-pink"></i> Standard Terms
                    </button>
                    <button onclick="showSection('targets')"
                        class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                        id="btn-targets">
                        <i class="fas fa-bullseye mr-2 text-red-500"></i> Targets
                    </button>
                    @if(auth()->user()->hasRole('super_admin'))
                        <button onclick="showSection('currencies')"
                            class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                            id="btn-currencies">
                            <i class="fas fa-coins mr-2 text-yellow-500"></i> Currencies
                        </button>
                        <button onclick="showSection('expense-categories')"
                            class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                            id="btn-expense-categories">
                            <i class="fas fa-tags mr-2 text-indigo-500"></i> Expense Categories
                        </button>
                        <button onclick="showSection('maintenance')"
                            class="section-btn text-left px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg bg-white shadow-sm border border-gray-100 hover:border-brand-blue transition-all shrink-0 text-xs sm:text-sm"
                            id="btn-maintenance">
                            <i class="fas fa-tools mr-2 text-orange-500"></i> Maintenance Mode
                        </button>
                    @endif
                </div>
            </div>

            <!-- Settings Content Area -->
            <div class="col-span-1 md:col-span-2">
                <!-- General Settings Section -->
                <section id="section-general" class="settings-section space-y-6">
                    <form action="{{ route('settings.updateGeneral') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-800">Company Information</h3>
                                <button type="submit"
                                    class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                    Save Changes
                                </button>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                    <input type="text" name="company_name"
                                        value="{{ \App\Models\Setting::get('company_name') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                    <input type="text" name="company_address_1"
                                        value="{{ \App\Models\Setting::get('company_address_1') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                                    <input type="text" name="company_address_2"
                                        value="{{ \App\Models\Setting::get('company_address_2') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="company_phone"
                                        value="{{ \App\Models\Setting::get('company_phone') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                    <input type="text" name="company_web"
                                        value="{{ \App\Models\Setting::get('company_web') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT Number</label>
                                    <input type="text" name="company_vat"
                                        value="{{ \App\Models\Setting::get('company_vat') }}"
                                        class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-800">Visual Identity (Brand Colors)</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary (Pink)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="color" name="brand_pink"
                                            value="{{ \App\Models\Setting::get('brand_pink') }}"
                                            class="h-10 w-10 p-0 border-none rounded">
                                        <input type="text" name="brand_pink_val" disabled
                                            value="{{ \App\Models\Setting::get('brand_pink') }}"
                                            class="bg-gray-50 text-gray-500 border-none text-sm rounded">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary (Blue)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="color" name="brand_blue"
                                            value="{{ \App\Models\Setting::get('brand_blue') }}"
                                            class="h-10 w-10 p-0 border-none rounded">
                                        <input type="text" name="brand_blue_val" disabled
                                            value="{{ \App\Models\Setting::get('brand_blue') }}"
                                            class="bg-gray-50 text-gray-500 border-none text-sm rounded">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Accent (Purple)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="color" name="brand_purple"
                                            value="{{ \App\Models\Setting::get('brand_purple') }}"
                                            class="h-10 w-10 p-0 border-none rounded">
                                        <input type="text" name="brand_purple_val" disabled
                                            value="{{ \App\Models\Setting::get('brand_purple') }}"
                                            class="bg-gray-50 text-gray-500 border-none text-sm rounded">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Info (Teal)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="color" name="brand_teal"
                                            value="{{ \App\Models\Setting::get('brand_teal') }}"
                                            class="h-10 w-10 p-0 border-none rounded">
                                        <input type="text" name="brand_teal_val" disabled
                                            value="{{ \App\Models\Setting::get('brand_teal') }}"
                                            class="bg-gray-50 text-gray-500 border-none text-sm rounded">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- Tax Settings Section -->
                <section id="section-tax" class="settings-section hidden space-y-6">
                    <form action="{{ route('settings.updateTax') }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-800">Tax Rates</h3>
                                <button type="submit"
                                    class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                    Save Rates
                                </button>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SSCL Rate (%)</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" step="0.0001" name="sscl_rate"
                                            value="{{ \App\Models\Setting::get('sscl_rate', 2.5641) }}"
                                            class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm pr-8">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">SSCL rate is used in tax calculations.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" step="0.01" name="vat_rate"
                                            value="{{ \App\Models\Setting::get('vat_rate', 15) }}"
                                            class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm pr-8">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </section>


                <!-- Standard Terms Section -->
                <section id="section-terms" class="settings-section hidden space-y-6">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Standard Estimate/Invoice Terms</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('settings.storeTerm') }}" method="POST" class="mb-6 space-y-3">
                                @csrf
                                <textarea name="content" placeholder="New term content..." required
                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm"
                                    rows="2"></textarea>
                                <div class="flex justify-end">
                                    <button type="submit"
                                        class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                        <i class="fas fa-plus mr-1"></i> Add Term
                                    </button>
                                </div>
                            </form>

                            <div class="space-y-4">
                                @foreach($terms as $term)
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100 relative group">
                                        <p class="text-sm text-gray-600 italic pr-16">"{{ $term->content }}"</p>
                                        <div
                                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                            <button
                                                onclick="editTerm('{{ route('settings.updateTerm', $term) }}', '{{ addslashes($term->content) }}')"
                                                class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="{{ route('settings.destroyTerm.get', $term) }}"
                                                onclick="return confirm('Are you sure you want to delete this term?');"
                                                class="text-red-500 hover:text-red-700 inline-flex items-center gap-1 ml-2">
                                                <i class="fas fa-times-circle pointer-events-none"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Edit Term Modal -->
                        <div id="editTermModal"
                            class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="mt-3">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2">Edit Term</h3>
                                    <form id="editTermForm" method="POST" class="mt-4">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2">Content</label>
                                            <textarea name="content" id="edit_term_content" required rows="4"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button"
                                                onclick="document.getElementById('editTermModal').classList.add('hidden')"
                                                class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 bg-brand-blue text-white text-base font-medium rounded-md hover:bg-brand-purple focus:outline-none focus:ring-2 focus:ring-blue-300">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                
                <!-- Targets Section -->
                <section id="section-targets" class="settings-section hidden space-y-6">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">Department Targets (LKR)</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('settings.updateDepartmentTargets') }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @php
                                        $departments = [];
                                        foreach (\App\Models\User::DEPARTMENT_HIERARCHY as $group) {
                                            $departments = array_merge($departments, array_keys($group));
                                        }
                                    @endphp
                                    @foreach($departments as $dept)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $dept }}</label>
                                            <input type="number" step="0.01" name="targets[{{ $dept }}]"
                                                value="{{ isset($departmentTargets[$dept]) ? $departmentTargets[$dept]->target_amount : 0 }}"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit"
                                        class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                        Save Department Targets
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md overflow-hidden mt-8">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-800">User Targets (LKR)</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('settings.updateUserTargets') }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($users as $user)
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            <label class="block text-sm font-medium text-gray-800 mb-1">
                                                {{ $user->name }}
                                                <span class="text-xs text-brand-blue block">{{ $user->department ?? 'No Dept' }}</span>
                                            </label>
                                            <input type="number" step="0.01" name="targets[{{ $user->id }}]"
                                                value="{{ isset($userTargets[$user->id]) ? $userTargets[$user->id]->target_amount : 0 }}"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm mt-2">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit"
                                        class="px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                        Save User Targets
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Currency Management Section (Super Admin Only) -->
                @if(auth()->user()->hasRole('super_admin'))
                    <section id="section-currencies" class="settings-section hidden space-y-6">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-lg font-bold text-gray-800">Supported Currencies</h3>
                            </div>
                            <div class="p-6">
                                <!-- Add Currency Form -->
                                <form action="{{ route('settings.storeCurrency') }}" method="POST"
                                    class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    @csrf
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Add New
                                        Currency</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Currency Code (e.g.
                                                USD)</label>
                                            <input type="text" name="code" placeholder="USD" required maxlength="3"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm uppercase">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Currency Name</label>
                                            <input type="text" name="name" placeholder="US Dollar"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Symbol</label>
                                            <input type="text" name="symbol" placeholder="$"
                                                class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                        </div>
                                        <button type="submit"
                                            class="px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all w-full">
                                            <i class="fas fa-plus mr-1"></i> Add Currency
                                        </button>
                                    </div>
                                </form>

                                <!-- Currency List -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Code</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Name</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Symbol</th>
                                                <th
                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($currencies as $currency)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $currency->code }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $currency->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $currency->symbol }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <button
                                                            onclick="editCurrency('{{ route('settings.updateCurrency', $currency) }}', '{{ $currency->code }}', '{{ addslashes($currency->name) }}', '{{ $currency->symbol }}')"
                                                            class="text-blue-500 hover:text-blue-700 transition-colors mr-2">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="{{ route('settings.destroyCurrency.get', $currency) }}"
                                                            onclick="return confirm('Are you sure you want to delete this currency?');"
                                                            class="text-red-500 hover:text-red-700 transition-colors inline-flex items-center gap-1 ml-2">
                                                            <i class="fas fa-trash-alt pointer-events-none"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Edit Currency Modal -->
                            <div id="editCurrencyModal"
                                class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                    <div class="mt-3">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2">Edit Currency</h3>
                                        <form id="editCurrencyForm" method="POST" class="mt-4">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-4">
                                                <label class="block text-gray-700 text-sm font-bold mb-2">Code</label>
                                                <input type="text" name="code" id="edit_currency_code" required maxlength="3"
                                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm uppercase">
                                            </div>
                                            <div class="mb-4">
                                                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                                <input type="text" name="name" id="edit_currency_name"
                                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                            </div>
                                            <div class="mb-4">
                                                <label class="block text-gray-700 text-sm font-bold mb-2">Symbol</label>
                                                <input type="text" name="symbol" id="edit_currency_symbol"
                                                    class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button"
                                                    onclick="document.getElementById('editCurrencyModal').classList.add('hidden')"
                                                    class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="px-4 py-2 bg-brand-blue text-white text-base font-medium rounded-md hover:bg-brand-purple focus:outline-none focus:ring-2 focus:ring-blue-300">
                                                    Update
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Maintenance Mode Section -->
                    <section id="section-maintenance" class="settings-section hidden space-y-6">
                        <form action="{{ route('settings.updateMaintenance') }}" method="POST">
                            @csrf
                            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                    <h3 class="text-lg font-bold text-gray-800">Maintenance Mode</h3>
                                    <button type="submit"
                                        class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all">
                                        Save Changes
                                    </button>
                                </div>
                                <div class="p-6">
                                    <div class="max-w-md">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">System Status</label>
                                        <select name="maintenance_mode" class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                            <option value="0" {{ \App\Models\Setting::get('maintenance_mode') != 1 ? 'selected' : '' }}>Active (Normal Operation)</option>
                                            <option value="1" {{ \App\Models\Setting::get('maintenance_mode') == 1 ? 'selected' : '' }}>Maintenance Mode (Block non-Super Admins)</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-2">When Maintenance Mode is active, all active non-Super Admin users will be logged out immediately and prevented from accessing any part of the system.</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>

                    <!-- Expense Categories Section -->
                    <section id="section-expense-categories" class="settings-section hidden space-y-6">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Expense Categories</h3>
                                    <p class="text-xs text-gray-500">Manage category classifications for company expenses.</p>
                                </div>
                                <button onclick="document.getElementById('addExpenseCategoryModal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-all flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Expense Category
                                </button>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                                <th class="py-3 px-4">Name</th>
                                                <th class="py-3 px-4">Description</th>
                                                <th class="py-3 px-4">Status</th>
                                                <th class="py-3 px-4 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-sm">
                                            @forelse($expenseCategories as $category)
                                                <tr class="hover:bg-gray-50/50 transition-all">
                                                    <td class="py-3 px-4 font-semibold text-gray-800">{{ $category->name }}</td>
                                                    <td class="py-3 px-4 text-gray-600">{{ $category->description ?: '-' }}</td>
                                                    <td class="py-3 px-4">
                                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $category->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst($category->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-4 text-right space-x-2">
                                                        <button onclick="editExpenseCategory('{{ route('settings.updateExpenseCategory', $category) }}', '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', '{{ $category->status }}')"
                                                            class="text-brand-blue hover:text-brand-purple transition-colors">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form action="{{ route('settings.destroyExpenseCategory', $category) }}" method="POST" class="inline-block"
                                                            onsubmit="return confirm('Are you sure you want to delete this expense category?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        No expense categories found. Click "Add Expense Category" to create one.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Add Expense Category Modal -->
                        <div id="addExpenseCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-6 border w-96 shadow-lg rounded-xl bg-white">
                                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800">Add Expense Category</h3>
                                    <button onclick="document.getElementById('addExpenseCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <form action="{{ route('settings.storeExpenseCategory') }}" method="POST" class="mt-4 space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                                        <input type="text" name="name" required class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" onclick="document.getElementById('addExpenseCategoryModal').classList.add('hidden')"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-brand-blue text-white text-sm font-medium rounded-md hover:bg-brand-purple">
                                            Save Category
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Expense Category Modal -->
                        <div id="editExpenseCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-6 border w-96 shadow-lg rounded-xl bg-white">
                                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800">Edit Expense Category</h3>
                                    <button onclick="document.getElementById('editExpenseCategoryModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <form id="editExpenseCategoryForm" action="" method="POST" class="mt-4 space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                                        <input type="text" name="name" id="edit_exp_cat_name" required class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea name="description" id="edit_exp_cat_description" rows="3" class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="edit_exp_cat_status" class="w-full rounded-md border-gray-300 focus:border-brand-blue focus:ring-brand-blue sm:text-sm">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" onclick="document.getElementById('editExpenseCategoryModal').classList.add('hidden')"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-brand-blue text-white text-sm font-medium rounded-md hover:bg-brand-purple">
                                            Update Category
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>



    <script>
        function showSection(id) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(s => s.classList.add('hidden'));
            // Show selected section
            document.getElementById('section-' + id).classList.remove('hidden');

            // Update buttons
            document.querySelectorAll('.section-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-brand-blue', 'bg-blue-50');
            });
            document.getElementById('btn-' + id).classList.add('ring-2', 'ring-brand-blue', 'bg-blue-50');
        }


        function editTerm(url, content) {
            const form = document.getElementById('editTermForm');
            form.action = url;
            document.getElementById('edit_term_content').value = content;
            document.getElementById('editTermModal').classList.remove('hidden');
        }

        function editCurrency(url, code, name, symbol) {
            const form = document.getElementById('editCurrencyForm');
            form.action = url;
            document.getElementById('edit_currency_code').value = code;
            document.getElementById('edit_currency_name').value = name;
            document.getElementById('edit_currency_symbol').value = symbol;
            document.getElementById('editCurrencyModal').classList.remove('hidden');
        }

        function editExpenseCategory(url, name, description, status) {
            const form = document.getElementById('editExpenseCategoryForm');
            form.action = url;
            document.getElementById('edit_exp_cat_name').value = name;
            document.getElementById('edit_exp_cat_description').value = description;
            document.getElementById('edit_exp_cat_status').value = status;
            document.getElementById('editExpenseCategoryModal').classList.remove('hidden');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            showSection('general');
        });

        // Update color displays on change
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('input', (e) => {
                const valInput = e.target.nextElementSibling;
                if (valInput) valInput.value = e.target.value;
            });
        });


    </script>
@endsection