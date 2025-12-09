<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Widget Customization - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.widget.embed', $organization) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Get Embed Code
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Customization Form -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customize Your Widget</h3>
                        
                        <form method="POST" action="{{ route('organization.widget.update', $organization) }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <!-- Primary Color -->
                            <div>
                                <x-input-label for="primary_color" value="Primary Color" />
                                <div class="flex items-center space-x-3 mt-1">
                                    <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $widgetSettings->primary_color) }}" class="h-10 w-20 rounded border-gray-300">
                                    <x-text-input type="text" name="primary_color" :value="old('primary_color', $widgetSettings->primary_color)" class="flex-1" placeholder="#3B82F6" />
                                </div>
                                <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                            </div>

                            <!-- Secondary Color -->
                            <div>
                                <x-input-label for="secondary_color" value="Secondary Color" />
                                <div class="flex items-center space-x-3 mt-1">
                                    <input type="color" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $widgetSettings->secondary_color) }}" class="h-10 w-20 rounded border-gray-300">
                                    <x-text-input type="text" name="secondary_color" :value="old('secondary_color', $widgetSettings->secondary_color)" class="flex-1" placeholder="#1E40AF" />
                                </div>
                                <x-input-error :messages="$errors->get('secondary_color')" class="mt-2" />
                            </div>

                            <!-- Font Family -->
                            <div>
                                <x-input-label for="font_family" value="Font Family" />
                                <select id="font_family" name="font_family" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="Inter, sans-serif" {{ $widgetSettings->font_family === 'Inter, sans-serif' ? 'selected' : '' }}>Inter</option>
                                    <option value="Roboto, sans-serif" {{ $widgetSettings->font_family === 'Roboto, sans-serif' ? 'selected' : '' }}>Roboto</option>
                                    <option value="Open Sans, sans-serif" {{ $widgetSettings->font_family === 'Open Sans, sans-serif' ? 'selected' : '' }}>Open Sans</option>
                                    <option value="Poppins, sans-serif" {{ $widgetSettings->font_family === 'Poppins, sans-serif' ? 'selected' : '' }}>Poppins</option>
                                    <option value="system-ui, sans-serif" {{ $widgetSettings->font_family === 'system-ui, sans-serif' ? 'selected' : '' }}>System Default</option>
                                </select>
                                <x-input-error :messages="$errors->get('font_family')" class="mt-2" />
                            </div>

                            <!-- Show Logo -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="show_logo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $widgetSettings->show_logo ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show organization logo in widget</span>
                                </label>
                            </div>

                            <!-- Allowed Domains -->
                            <div>
                                <x-input-label for="allowed_domains" value="Allowed Domains (optional)" />
                                <x-text-input id="allowed_domains" name="allowed_domains" type="text" class="mt-1 block w-full" :value="old('allowed_domains', is_array($widgetSettings->allowed_domains) ? implode(', ', $widgetSettings->allowed_domains) : '')" placeholder="example.com, mysite.com" />
                                <p class="mt-1 text-sm text-gray-500">Comma-separated list. Leave empty to allow all domains.</p>
                                <x-input-error :messages="$errors->get('allowed_domains')" class="mt-2" />
                            </div>

                            <!-- Custom CSS -->
                            <div>
                                <x-input-label for="custom_css" value="Custom CSS (Advanced)" />
                                <textarea id="custom_css" name="custom_css" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm font-mono text-sm" placeholder=".widget-container { ... }">{{ old('custom_css', $widgetSettings->custom_css) }}</textarea>
                                <x-input-error :messages="$errors->get('custom_css')" class="mt-2" />
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('organization.widget.preview', $organization) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                                    Preview Widget →
                                </a>
                                <x-primary-button>
                                    Save Changes
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Live Preview</h3>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <iframe 
                                src="{{ route('organization.widget.preview', $organization) }}" 
                                width="100%" 
                                height="500" 
                                frameborder="0"
                                class="rounded-lg"
                                title="Widget Preview">
                            </iframe>
                        </div>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            This is how your widget will appear on external websites. Save changes to update the preview.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-reload preview iframe after successful save
        @if(session('success'))
            const previewIframe = document.querySelector('iframe[title="Widget Preview"]');
            if (previewIframe) {
                setTimeout(() => {
                    previewIframe.src = previewIframe.src;
                }, 500);
            }
        @endif

        // Live color preview update
        document.getElementById('primary_color').addEventListener('input', function(e) {
            document.querySelector('input[name="primary_color"][type="text"]').value = e.target.value;
        });
        
        document.getElementById('secondary_color').addEventListener('input', function(e) {
            document.querySelector('input[name="secondary_color"][type="text"]').value = e.target.value;
        });
    </script>
</x-app-layout>
