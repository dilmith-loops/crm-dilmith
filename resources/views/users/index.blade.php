@extends('layouts.app')

@section('header', 'User Management')

@section('content')
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">Manage Users</h3>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-xs sm:text-sm font-medium transition-colors text-center">
                    <i class="fas fa-file-import mr-2"></i>Import Users
                </button>
                <a href="{{ route('users.create') }}"
                    class="px-4 py-2 bg-brand-pink text-white rounded-md hover:bg-brand-purple text-xs sm:text-sm font-medium transition-colors text-center">
                    <i class="fas fa-user-plus mr-2"></i>Add New User
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-4 sm:mx-6 mt-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-4 sm:mx-6 mt-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if(session('import_errors'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mx-4 sm:mx-6 mt-4" role="alert">
                <p class="font-bold text-sm">Some rows failed to import:</p>
                <ul class="list-disc list-inside text-xs sm:text-sm mt-1">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supervisor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created
                            At</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full 
                                                                    {{ $user->role === 'Super Admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                                                    {{ $user->role === 'Management' ? 'bg-blue-100 text-blue-800' : '' }}
                                                                    {{ $user->role === 'HOD' ? 'bg-green-100 text-green-800' : '' }}
                                                                    {{ $user->role === 'Manager' ? 'bg-gray-100 text-gray-800' : '' }}
                                                                    {{ $user->role === 'Staff' ? 'bg-amber-100 text-amber-800' : '' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="capitalize text-gray-700 font-medium">{{ $user->department ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->supervisor ? $user->supervisor->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('users.edit', $user) }}"
                                    class="text-brand-blue hover:text-brand-purple mr-3 transition-colors">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Import Users via CSV</h3>
                <div class="mt-2 text-left">
                    <p class="text-sm text-gray-500 mb-4">
                        Upload a CSV file with columns: <strong>Name, Email, Role, Supervisor Email, Department,
                            Password</strong>.
                        <br>
                        <a href="{{ route('users.download-sample') }}" class="text-brand-blue hover:underline">Download
                            Sample CSV</a>
                    </p>
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2" for="file">
                                Choose CSV File
                            </label>
                            <input
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                id="file" name="file" type="file" accept=".csv,.txt" required>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-purple text-sm font-medium">
                                Upload & Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection