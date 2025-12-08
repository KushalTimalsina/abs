<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - {{ $organization->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $widgetSettings->primary_color ?? '#3B82F6' }};
            --secondary-color: {{ $widgetSettings->secondary_color ?? '#1E40AF' }};
        }
        body {
            font-family: {{ $widgetSettings->font_family ?? 'Inter, sans-serif' }};
        }
        .btn-primary {
            background-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        {{ $widgetSettings->custom_css ?? '' }}
    </style>
</head>
<body class="bg-gray-50">
    <div class="widget-container max-w-2xl mx-auto p-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center space-x-4">
                @if($widgetSettings->show_logo && $organization->logo)
                <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="w-16 h-16 rounded-lg object-cover">
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h1>
                    @if($organization->description)
                    <p class="text-gray-600 mt-1">{{ $organization->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Service</h2>
            
            @if($services->count() > 0)
            <div class="space-y-3">
                @foreach($services as $service)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                            @if($service->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $service->description }}</p>
                            @endif
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>⏱️ {{ $service->duration }} min</span>
                                <span>💰 Rs {{ number_format($service->price, 2) }}</span>
                            </div>
                        </div>
                        <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:shadow-lg transition-all">
                            Book Now
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>No services available at the moment.</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <p>Powered by Appointment Booking System</p>
        </div>
    </div>

    <script>
        // Widget booking functionality will be implemented in Phase 4
        document.querySelectorAll('.btn-primary').forEach(button => {
            button.addEventListener('click', function() {
                alert('Booking functionality will be available soon!');
            });
        });
    </script>
</body>
</html>
