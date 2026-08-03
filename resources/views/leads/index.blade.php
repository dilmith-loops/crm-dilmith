@extends('layouts.app')

@section('header', 'Leads')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700">Leads Management</h3>
        </div>

        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h4 class="text-md font-medium text-gray-600">Active Leads</h4>
            <a href="{{ route('leads.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                <i class="fas fa-plus mr-2"></i> Add Lead
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job
                            Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-6 py-4 white-space-nowrap text-sm font-medium text-gray-900">{{ $lead->name }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-sm text-gray-500">
                                <div>{{ $lead->email }}</div>
                                <div>{{ $lead->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($lead->job_description, 50) }}</td>
                            <td class="px-6 py-4 white-space-nowrap text-sm">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $lead->status === 'done' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 white-space-nowrap text-right text-sm font-medium">
                                @if($lead->status !== 'done')
                                    <form action="{{ route('leads.done', $lead) }}" method="POST" class="inline-block"
                                        onsubmit="return confirm('Mark as Done? This will create a Customer and Draft Estimate.');">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Mark as Done">
                                            <i class="fas fa-check-circle"></i> Done
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400"><i class="fas fa-check"></i> Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">No leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection