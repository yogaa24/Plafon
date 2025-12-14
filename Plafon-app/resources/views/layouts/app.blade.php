<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sales Submission System')</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-2">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('img/karisma_logo.png') }}" alt="Logo" class="h-20 w-24 object-contain">
                        <span class="ml-2 text-xl font-bold text-gray-900">Karisma Difon</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">
                        <span class="font-semibold">{{ Auth::user()->name }}</span>
                        <span class="text-gray-500 ml-2">({{ ucfirst(Auth::user()->role) }})</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow">
            <div class="flex">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <main class="flex-1 px-6 sm:px-8 lg:px-20 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="md:flex md:items-center md:justify-between">
                <!-- Left Side - Company Info -->
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <img src="{{ asset('img/karisma_logo.png') }}" alt="Logo" class="h-10 w-12 object-contain">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">PT Karisma Indoargo Universal</p>
                        <p class="text-xs text-gray-500">Difon System</p>
                    </div>
                </div>

                <!-- Center - Links (Optional) -->
                <div class="flex space-x-6 mb-4 md:mb-0">
                    <a href="#" class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Bantuan
                    </a>
                    <a href="#" class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Panduan
                    </a>
                    <a href="#" class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Kontak
                    </a>
                </div>

                <!-- Right Side - Copyright -->
                <div class="text-center md:text-right">
                    <p class="text-xs text-gray-500">
                        &copy; {{ date('Y') }} Karisma Difon. All rights reserved.
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Version 1.0.0
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>