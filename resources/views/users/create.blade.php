@extends('layouts.app')

@section('header', 'Add New User')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 bg-gradient-to-r from-brand-pink to-brand-purple border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Create New User</h3>
                        <p class="text-sm text-white/80 mt-1">Add a new team member to the system</p>
                    </div>
                    <a href="{{ route('users.index') }}"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-medium transition-all backdrop-blur-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="p-8">
                @csrf

                <!-- Personal Information Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-blue">
                        <div class="w-10 h-10 bg-brand-blue/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-brand-blue"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Personal Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-id-card text-brand-blue mr-2"></i>Full Name <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="Enter full name">
                            @error('name') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-brand-blue mr-2"></i>Email Address <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 transition-all"
                                placeholder="user@example.com">
                            @error('email') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Role & Hierarchy Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-purple">
                        <div class="w-10 h-10 bg-brand-purple/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-brand-purple"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Role & Hierarchy</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-briefcase text-brand-purple mr-2"></i>User Role <span
                                    class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all">
                                <option value="">-- Select Role --</option>
                                @foreach(\App\Models\User::ROLES as $role)
                                    @if($role !== 'Super Admin')
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('role') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="supervisor_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-sitemap text-brand-purple mr-2"></i>Assigned Supervisor
                            </label>
                            <select name="supervisor_id" id="supervisor_id"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all">
                                <option value="">-- No Supervisor --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('supervisor_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supervisor_id') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="department" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-building text-brand-purple mr-2"></i>Department <span
                                    class="text-red-500">*</span>
                            </label>
                            <select name="department" id="department" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-purple focus:ring-2 focus:ring-brand-purple/20 transition-all">
                                <option value="">-- Select Department --</option>
                                @foreach(\App\Models\User::DEPARTMENT_HIERARCHY as $group => $departments)
                                    @foreach($departments as $key => $label)
                                        <option value="{{ $key }}" {{ old('department') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('department') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="mb-8">
                    <div class="flex items-center mb-4 pb-2 border-b-2 border-brand-pink">
                        <div class="w-10 h-10 bg-brand-pink/10 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-lock text-brand-pink"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800">Security Credentials</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-key text-brand-pink mr-2"></i>Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all pr-12"
                                    placeholder="Minimum 8 characters">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-pink focus:outline-none transition-colors">
                                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('password') <p class="mt-2 text-sm text-red-600"><i
                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-check-double text-brand-pink mr-2"></i>Confirm Password <span
                                    class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-brand-pink focus:ring-2 focus:ring-brand-pink/20 transition-all pr-12"
                                    placeholder="Re-enter password">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-pink focus:outline-none transition-colors">
                                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-brand-blue rounded-r-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Password Requirements:</strong> Must be at least 8 characters long and match the
                            confirmation field.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('users.index') }}"
                        class="px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 rounded-lg bg-gradient-to-r from-brand-pink to-brand-purple text-white font-bold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-user-plus mr-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection