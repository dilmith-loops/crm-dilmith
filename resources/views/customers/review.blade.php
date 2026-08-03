@extends('layouts.app')

@section('header', 'Review Customer Update Request')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 bg-gradient-to-r from-brand-blue to-brand-teal border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Review Update Request</h3>
                        <p class="text-sm text-white/80 mt-1">
                            Requested by {{ $request->user->name }} on {{ $request->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    <a href="{{ route('customers.index') }}"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-medium transition-all backdrop-blur-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Current Data -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h4 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Current Data</h4>
                        <dl class="space-y-3 text-sm">
                            @foreach($request->data as $key => $value)
                                <div class="grid grid-cols-3 gap-2">
                                    <dt class="font-semibold text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                    <dd class="col-span-2 text-gray-800">{{ $request->customer->$key ?? 'N/A' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    <!-- Proposed Changes -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h4 class="text-lg font-bold text-brand-blue mb-4 border-b border-blue-200 pb-2">Proposed Changes
                        </h4>
                        <dl class="space-y-3 text-sm">
                            @foreach($request->data as $key => $value)
                                <div
                                    class="grid grid-cols-3 gap-2 p-1 rounded {{ ($request->customer->$key != $value) ? 'bg-yellow-100 ring-1 ring-yellow-300' : '' }}">
                                    <dt class="font-semibold text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                    <dd class="col-span-2 text-gray-900 font-medium">{{ $value ?? 'N/A' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                    <form action="{{ route('customers.requests.reject', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-6 py-3 rounded-lg border-2 border-red-200 text-red-600 bg-red-50 hover:bg-red-100 font-medium transition-all">
                            <i class="fas fa-times mr-2"></i>Reject Request
                        </button>
                    </form>

                    <form action="{{ route('customers.requests.approve', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-8 py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg transition-all">
                            <i class="fas fa-check mr-2"></i>Approve & Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection