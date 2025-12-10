<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Organization Settings - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.show', $organization) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                📋 Booking Number Configuration
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Format: <span class="font-mono font-bold text-blue-600 dark:text-blue-400">MASTER.ORG.CENTER.NUMBER</span>
                            </p>

                            <form action="{{ route('organization.settings.booking-numbers.update', $organization) }}" method="POST" id="settingsForm">
                                @csrf
                                @method('PUT')

                                <!-- Master Prefix (Fixed, Read-only) -->
                                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Master Prefix (Fixed)
                                        </label>
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   name="booking_number_show_master" 
                                                   id="booking_number_show_master"
                                                   value="1"
                                                   {{ old('booking_number_show_master', $showMasterPrefix) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="booking_number_show_master" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                Show in booking number
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" 
                                           name="booking_number_master_prefix" 
                                           value="{{ $masterPrefix }}">
                                    
                                    <!-- Display-only field -->
                                    <div class="mt-1 block w-full rounded-md bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 px-3 py-2">
                                        <span class="font-mono font-bold text-gray-900 dark:text-white text-lg">{{ $masterPrefix }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Fixed prefix for all bookings (cannot be edited)
                                    </p>
                                </div>

                                <!-- Organization Code (Read-only) -->
                                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Organization Code (Auto-generated)
                                    </label>
                                    <div class="mt-1 block w-full rounded-md bg-white dark:bg-gray-800 border-blue-300 dark:border-blue-700 px-3 py-2">
                                        <span class="font-mono font-bold text-blue-600 dark:text-blue-400 text-lg">{{ $orgCode }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        Generated from: "{{ $organization->name }}" (Cannot be edited)
                                    </p>
                                </div>

                                <!-- Center Prefix (Custom) -->
                                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                    <label for="booking_number_center_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Center Prefix (Custom) ⭐
                                    </label>
                                    <input type="text" 
                                           name="booking_number_center_prefix" 
                                           id="booking_number_center_prefix"
                                           value="{{ old('booking_number_center_prefix', $centerPrefix) }}"
                                           maxlength="5"
                                           pattern="[A-Z]*"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 uppercase font-mono font-bold"
                                           placeholder="OM (Optional)">
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        Your custom code (e.g., OM, SVC, PRO) - Optional, leave empty if not needed
                                    </p>
                                    @error('booking_number_center_prefix')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Starting Number -->
                                <div class="mb-6">
                                    <label for="booking_number_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Starting Number
                                    </label>
                                    <input type="number" 
                                           name="booking_number_start" 
                                           id="booking_number_start"
                                           value="{{ old('booking_number_start', $currentStart) }}"
                                           min="1"
                                           max="99999"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                                           required>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        First booking will start from this number (1-99999)
                                    </p>
                                    @error('booking_number_start')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Format Selection -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Number Format
                                    </label>
                                    <div class="space-y-3">
                                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $currentFormat === 'dotted' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                            <input type="radio" 
                                                   name="booking_number_format" 
                                                   value="dotted" 
                                                   {{ old('booking_number_format', $currentFormat) === 'dotted' ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                            <div class="ml-3">
                                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Dotted Format (Recommended)</span>
                                                <span class="block text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $masterPrefix }}.{{ $orgCode }}.{{ $centerPrefix ?: 'XX' }}.00001</span>
                                                <span class="block text-xs text-gray-400 dark:text-gray-500">Easy to read, professional</span>
                                            </div>
                                        </label>

                                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $currentFormat === 'compact' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                            <input type="radio" 
                                                   name="booking_number_format" 
                                                   value="compact" 
                                                   {{ old('booking_number_format', $currentFormat) === 'compact' ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                            <div class="ml-3">
                                                <span class="block text-sm font-medium text-gray-900 dark:text-white">Compact Format</span>
                                                <span class="block text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $masterPrefix }}{{ $orgCode }}{{ $centerPrefix ?: 'XX' }}00001</span>
                                                <span class="block text-xs text-gray-400 dark:text-gray-500">Shorter, no special characters</span>
                                            </div>
                                        </label>
                                    </div>
                                    @error('booking_number_format')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end space-x-3">
                                    <button type="button" 
                                            id="previewBtn"
                                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold">
                                        Preview Changes
                                    </button>
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold">
                                        Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="space-y-6">
                    <!-- Structure Diagram -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                📐 Structure
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900 rounded">
                                    <span class="text-gray-600 dark:text-gray-400">Master</span>
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $masterPrefix }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                    <span class="text-gray-600 dark:text-gray-400">Org Code</span>
                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $orgCode }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                    <span class="text-gray-600 dark:text-gray-400">Center</span>
                                    <span class="font-mono font-bold text-green-600 dark:text-green-400">{{ $centerPrefix ?: '(empty)' }}</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-purple-50 dark:bg-purple-900/20 rounded">
                                    <span class="text-gray-600 dark:text-gray-400">Number</span>
                                    <span class="font-mono font-bold text-purple-600 dark:text-purple-400">{{ str_pad($currentStart, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Preview -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                🔍 Sample Booking Numbers
                            </h3>
                            <div id="samplePreview" class="space-y-2">
                                @foreach($samples as $index => $sample)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Booking {{ $index + 1 }}</span>
                                        <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $sample }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Help & Tips -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-3">💡 Tips</h4>
                        <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
                            <li>• <strong>Master:</strong> Fixed prefix (BN, INV, TKT)</li>
                            <li>• <strong>Org Code:</strong> Auto from name (cannot edit)</li>
                            <li>• <strong>Center:</strong> Your custom code (OM, SVC, PRO)</li>
                            <li>• <strong>Number:</strong> Sequential (00001, 00002...)</li>
                            <li>• <strong>Example:</strong> BN.AV.OM.00001</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview Script -->
    <script>
        document.getElementById('previewBtn').addEventListener('click', async function() {
            const masterPrefix = document.querySelector('input[name="booking_number_master_prefix"]').value;
            const showMaster = document.getElementById('booking_number_show_master').checked;
            const centerPrefix = document.getElementById('booking_number_center_prefix').value.toUpperCase();
            const start = parseInt(document.getElementById('booking_number_start').value);
            const format = document.querySelector('input[name="booking_number_format"]:checked').value;

            if (!masterPrefix || !start) {
                alert('Please fill in required fields');
                return;
            }

            try {
                const response = await fetch('{{ route('organization.settings.booking-numbers.preview', $organization) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        master_prefix: masterPrefix,
                        show_master: showMaster,
                        center_prefix: centerPrefix,
                        start: start,
                        format: format
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const previewHtml = data.samples.map((sample, index) => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Booking ${index + 1}</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">${sample}</span>
                        </div>
                    `).join('');

                    document.getElementById('samplePreview').innerHTML = previewHtml;
                }
            } catch (error) {
                console.error('Preview error:', error);
                alert('Failed to generate preview');
            }
        });

        // Auto-uppercase center prefix input only
        document.getElementById('booking_number_center_prefix').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z]/g, '');
        });
    </script>
</x-app-layout>
