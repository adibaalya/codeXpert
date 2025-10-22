@extends('layouts.app')

@section('title', 'Dashboard - CodeXpert')

@section('content')
<div class="min-h-screen bg-black">
    <!-- Header -->
    <div class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-orange-500 rounded flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">CX</span>
                    </div>
                    <span class="text-white text-xl font-semibold">CodeXpert</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">Welcome, {{ Auth::user()->name }}!</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-orange-500 hover:text-orange-400 transition duration-150 ease-in-out">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-white mb-4">Welcome to CodeXpert</h1>
            <p class="text-xl text-gray-300 mb-8">From practice to pro — powered by AI</p>
            
            <div class="bg-gray-800 rounded-2xl p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-semibold text-white mb-4">Your Learning Dashboard</h2>
                <p class="text-gray-300 mb-6">Start your coding journey with AI-powered practice sessions.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">Practice Problems</h3>
                        <p class="text-gray-300 text-sm">Solve coding challenges with AI assistance</p>
                    </div>
                    <div class="bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">AI Tutoring</h3>
                        <p class="text-gray-300 text-sm">Get personalized learning recommendations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
