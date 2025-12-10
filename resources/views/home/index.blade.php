<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Booking System - Streamline Your Business</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        📅 Appointment Booking System
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('credits') }}" class="text-gray-600 hover:text-gray-900">Credits</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-5xl font-extrabold text-gray-900 sm:text-6xl">
                    Streamline Your
                    <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Appointment Booking
                    </span>
                </h2>
                <p class="mt-6 text-xl text-gray-600 max-w-3xl mx-auto">
                    Powerful, easy-to-use appointment booking system for businesses of all sizes. 
                    Manage bookings, staff, payments, and more - all in one place.
                </p>
                <div class="mt-10 flex justify-center gap-4">
                    <a href="{{ route('register') }}" class="px-8 py-4 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all">
                        Start Free Trial
                    </a>
                    <a href="#features" class="px-8 py-4 bg-white text-blue-600 text-lg font-semibold rounded-lg hover:bg-gray-50 shadow-lg hover:shadow-xl transition-all border-2 border-blue-600">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900">Powerful Features</h3>
                <p class="mt-4 text-xl text-gray-600">Everything you need to manage your appointments</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        📅
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Smart Scheduling</h4>
                    <p class="text-gray-600">Automated slot management, conflict detection, and intelligent booking suggestions.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        👥
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Team Management</h4>
                    <p class="text-gray-600">Manage staff schedules, roles, permissions, and availability with ease.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        💳
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Payment Integration</h4>
                    <p class="text-gray-600">Accept payments via eSewa, Khalti, Stripe, or bank transfer seamlessly.</p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        🔔
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Notifications</h4>
                    <p class="text-gray-600">Automated email and in-app notifications for bookings, reminders, and updates.</p>
                </div>

                <!-- Feature 5 -->
                <div class="p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        🎨
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Embeddable Widget</h4>
                    <p class="text-gray-600">Customizable booking widget to embed on your website with your branding.</p>
                </div>

                <!-- Feature 6 -->
                <div class="p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center text-white text-2xl mb-4">
                        📊
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Analytics & Reports</h4>
                    <p class="text-gray-600">Track bookings, revenue, and performance with detailed analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl font-bold text-gray-900">Simple, Transparent Pricing</h3>
                <p class="mt-4 text-xl text-gray-600">Choose the plan that fits your business</p>
            </div>
            
            <div class="grid md:grid-cols-{{ $plans->count() }} gap-8">
                @foreach($plans as $plan)
                <div class="bg-white rounded-2xl shadow-xl p-8 {{ $plan->is_featured ? 'ring-4 ring-blue-600 transform scale-105' : '' }}">
                    @if($plan->is_featured)
                    <div class="text-center mb-4">
                        <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>
                    </div>
                    @endif
                    
                    <div class="text-center">
                        <h4 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h4>
                        <div class="mt-4">
                            <span class="text-5xl font-extrabold text-gray-900">NPR {{ number_format($plan->price) }}</span>
                            <span class="text-gray-600">/{{ $plan->billing_cycle }}</span>
                        </div>
                        <p class="mt-4 text-gray-600">{{ $plan->description }}</p>
                    </div>
                    
                    <ul class="mt-8 space-y-4">
                        @php
                            $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true);
                        @endphp
                        @foreach($features as $feature)
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 {{ $plan->is_featured ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-900' }} font-semibold rounded-lg transition-colors">
                            Get Started
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-4xl font-bold text-white">Ready to Get Started?</h3>
            <p class="mt-4 text-xl text-blue-100">Join hundreds of businesses using our platform</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-blue-600 text-lg font-semibold rounded-lg hover:bg-gray-100 shadow-lg hover:shadow-xl transition-all">
                    Start Your Free Trial
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h5 class="text-lg font-bold mb-4">Appointment Booking System</h5>
                    <p class="text-gray-400">Streamline your business with our powerful booking platform.</p>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Product</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white">Pricing</a></li>
                        <li><a href="{{ route('credits') }}" target="_blank" class="hover:text-white">Credits</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Company</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">About</a></li>
                        <li><a href="#" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Legal</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Appointment Booking System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
