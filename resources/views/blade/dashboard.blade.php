@extends('kaely-auth::blade.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">{{ config('app.name', 'Laravel') }}</h1>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-gray-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome, {{ Auth::user()->name }}!</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- User Info -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">User Information</h3>
                            <div class="space-y-2">
                                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                <p><strong>Member since:</strong> {{ Auth::user()->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 mb-2">Recent Activity</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-green-700">Last login: {{ now()->format('M d, Y H:i') }}</p>
                                <p class="text-sm text-green-700">Session active</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-900 mb-2">Quick Actions</h3>
                            <div class="space-y-2">
                                <a href="#" class="block text-sm text-purple-700 hover:text-purple-900">Update Profile</a>
                                <a href="#" class="block text-sm text-purple-700 hover:text-purple-900">Change Password</a>
                                <a href="#" class="block text-sm text-purple-700 hover:text-purple-900">View Settings</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection 