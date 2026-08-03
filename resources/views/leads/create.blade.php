@extends('layouts.app')

@section('header', 'Add New Lead')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700">New Lead Details</h3>
        </div>

        <form action="{{ route('leads.store') }}" method="POST" class="p-6">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Lead / Client Name</label>
                <input type="text" name="name" id="name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" id="email"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                <input type="text" name="phone" id="phone"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="job_description" class="block text-gray-700 text-sm font-bold mb-2">Job Description</label>
                <textarea name="job_description" id="job_description" rows="4"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required></textarea>
                <p class="text-xs text-gray-500 mt-1">Describe the potential project or requirements.</p>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('leads.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Save Lead
                </button>
            </div>
        </form>
    </div>
@endsection