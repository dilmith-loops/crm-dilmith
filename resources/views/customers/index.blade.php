@extends('layouts.app')

@section('header', 'Customers')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden" x-data="{ 
        columns: $persist(['name', 'email', 'brand', 'address', 'actions']).as('customers_columns'),
        showPicker: false,
        isColumnVisible(col) { return this.columns.includes(col); },
        toggleColumn(col) {
            if (this.isColumnVisible(col)) {
                this.columns = this.columns.filter(c => c !== col);
            } else {
                this.columns.push(col);
            }
        },
        init() {
            if (this.columns.includes('phone')) {
                this.columns = this.columns.map(c => c === 'phone' ? 'brand' : c);
            } else if (!this.columns.includes('brand')) {
                this.columns.push('brand');
            }
        }
    }">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">All Customers</h3>
            {{-- Columns button hidden --}}
        </div>

        <!-- Search -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ route('customers.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 items-end">
                <div class="relative">
                    <label for="search" class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Customer Name or Brand"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue text-base sm:text-sm h-10 pl-10 pr-3 border">
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" title="Search"
                        class="px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-colors h-10 flex items-center justify-center">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('customers.index') }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors h-10 flex items-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50">
            <h4 class="text-sm sm:text-md font-medium text-gray-600">Active Customers</h4>
            <a href="{{ route('customers.create') }}"
                class="w-full sm:w-auto text-center px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Customer
            </a>
        </div>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th x-show="isColumnVisible('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th x-show="isColumnVisible('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                        </th>
                        <th x-show="isColumnVisible('brand')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand
                        </th>
                        <th x-show="isColumnVisible('address')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address
                        </th>
                        <th x-show="isColumnVisible('actions')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr>
                            <td x-show="isColumnVisible('name')" class="px-6 py-4 white-space-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                            </td>
                            <td x-show="isColumnVisible('email')" class="px-6 py-4 white-space-nowrap">
                                <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                            </td>
                            <td x-show="isColumnVisible('brand')" class="px-6 py-4 white-space-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->brand ?? '-' }}</div>
                            </td>
                            <td x-show="isColumnVisible('address')" class="px-6 py-4">
                                <div class="text-sm text-gray-500">{{ Str::limit($customer->billing_address ?? $customer->address, 30) ?? '-' }}</div>
                            </td>
                            <td x-show="isColumnVisible('actions')" class="px-6 py-4 white-space-nowrap text-right text-sm font-medium">
                                <a href="{{ route('customers.edit', $customer) }}"
                                    class="text-brand-blue hover:text-brand-purple mr-3">Edit</a>
                                @if(auth()->user()->role === 'Super Admin')
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-700 transition-colors">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">
                                No customers found. Start by creating one!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection