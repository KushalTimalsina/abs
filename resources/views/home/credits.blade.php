<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Credits & Acknowledgments - Appointment Booking System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        📅 Appointment Booking System
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">Home</a>
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

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Credits & Acknowledgments
                </h1>
                <p class="mt-4 text-gray-600">Built with passion and powered by amazing technologies</p>
            </div>

            <div class="space-y-8">
                <!-- System Information -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-blue-600">System Information</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">System Name</p>
                            <p class="font-semibold text-lg">Appointment Booking System</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Version</p>
                            <p class="font-semibold text-lg">1.0.0</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Framework</p>
                            <p class="font-semibold text-lg">Laravel {{ app()->version() }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">PHP Version</p>
                            <p class="font-semibold text-lg">{{ PHP_VERSION }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Database</p>
                            <p class="font-semibold text-lg">MySQL</p>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Release Date</p>
                            <p class="font-semibold text-lg">December 2025</p>
                        </div>
                    </div>
                </div>

                <!-- Developer Credits -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-purple-600">Development Team</h3>
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6">
                        <div class="space-y-4">
                            <div>
                                <p class="font-semibold text-lg">Lead Developer</p>
                                <p class="text-gray-700">Your Name / Your Team</p>
                            </div>
                            <div>
                                <p class="font-semibold text-lg">Project Type</p>
                                <p class="text-gray-700">SaaS Multi-Tenant Appointment Booking Platform</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technologies Used -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-green-600">Technologies & Frameworks</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="font-semibold text-lg mb-4 text-blue-600">Backend</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel {{ app()->version() }}</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> PHP {{ PHP_VERSION }}</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> MySQL Database</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel Socialite (Google OAuth)</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel Queue</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="font-semibold text-lg mb-4 text-purple-600">Frontend</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Tailwind CSS</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Alpine.js</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Blade Templates</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Flowbite Components</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Vite Build Tool</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="font-semibold text-lg mb-4 text-green-600">Payment Gateways</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> eSewa</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Khalti</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Stripe</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Bank Transfer</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="font-semibold text-lg mb-4 text-orange-600">Additional Libraries</h4>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> DomPDF (Invoice Generation)</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel Notifications</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel Policies</li>
                                <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Laravel Middleware</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-orange-600">Key Features</h3>
                    <div class="grid md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Multi-Tenant SaaS Architecture</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Subscription Management</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Team & Staff Management</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Service & Slot Management</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Smart Booking System</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Payment Integration</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Invoice Generation</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Email Notifications</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> In-App Notifications</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Customer Dashboard</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Embeddable Widget</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Google OAuth Login</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Role-Based Permissions</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Dark Mode Support</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Responsive Design</div>
                        <div class="flex items-center bg-gray-50 rounded-lg p-3"><span class="text-green-500 mr-2">✓</span> Analytics & Reports</div>
                    </div>
                </div>

                <!-- Special Thanks -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold mb-6 text-red-600">Special Thanks</h3>
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-lg p-6">
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">❤️</span>
                                <span>Laravel Community for the amazing framework</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">❤️</span>
                                <span>Tailwind CSS team for the utility-first CSS framework</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">❤️</span>
                                <span>Flowbite for beautiful UI components</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">❤️</span>
                                <span>Google for OAuth integration</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">❤️</span>
                                <span>All open-source contributors</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 pt-8 border-t border-gray-200">
                <p class="text-gray-600">
                    &copy; {{ date('Y') }} Appointment Booking System. All rights reserved.
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Built with ❤️ using Laravel
                </p>
            </div>
        </div>
    </div>
</body>
</html>
