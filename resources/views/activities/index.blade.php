@extends('layouts.app')

@section('header', 'Activity Log')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">System Activity Logs</h3>
        </div>

        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP
                            Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date &
                            Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <img class="h-8 w-8 rounded-full"
                                            src="https://ui-avatars.com/api/?name={{ urlencode($activity->user->name ?? 'System') }}&background=random"
                                            alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $activity->user->name ?? 'System' }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $activity->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $activity->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}
                                @if($activity->subject_id)
                                    <span class="text-xs text-gray-400">(ID: {{ $activity->subject_id }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                {{ $activity->ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $activity->created_at->format('M d, Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                <i class="fas fa-history text-4xl text-gray-200 mb-3"></i>
                                <p>No activity logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
@endsection