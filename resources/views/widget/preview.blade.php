<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Preview - {{ $organization->name }}</title>
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
    </style>
</head>
<body class="bg-gray-50 p-4">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
        @if($widgetSettings->show_logo && $organization->logo)
        <div class="text-center mb-4">
            <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="w-20 h-20 rounded-lg mx-auto object-cover">
        </div>
        @endif
        
        <h2 class="text-2xl font-bold text-center mb-2">{{ $organization->name }}</h2>
        <p class="text-center text-gray-600 mb-6">Book Your Appointment</p>
        
        <div class="space-y-3">
            @forelse($services as $service)
            <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-500 transition-colors">
                <div class="font-semibold">{{ $service->name }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ $service->duration }} min • Rs {{ number_format($service->price, 2) }}</div>
            </div>
            @empty
            <p class="text-center text-gray-500 py-4">No services available</p>
            @endforelse
        </div>
        
        <button class="btn-primary w-full text-white py-3 rounded-lg mt-6 font-semibold hover:shadow-lg transition-all">
            Continue
        </button>
    </div>
</body>
</html>
