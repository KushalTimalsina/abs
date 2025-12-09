<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Widget Embed Code - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.widget.customize', $organization) }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Customization
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">How to Embed Your Booking Widget</h3>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    Copy one of the code snippets below and paste it into your website's HTML where you want the booking widget to appear.
                </p>
            </div>

            <!-- Iframe Embed -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Option 1: Iframe Embed (Recommended)</h3>
                        <button onclick="copyToClipboard('iframe-code')" class="text-sm text-blue-600 hover:text-blue-800">
                            Copy Code
                        </button>
                    </div>
                    <pre id="iframe-code" class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ $iframeCode }}</code></pre>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        ✓ Easy to implement<br>
                        ✓ Works on all platforms<br>
                        ✓ Isolated from your site's CSS
                    </p>
                </div>
            </div>

            <!-- JavaScript Embed -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Option 2: JavaScript Embed</h3>
                        <button onclick="copyToClipboard('js-code')" class="text-sm text-blue-600 hover:text-blue-800">
                            Copy Code
                        </button>
                    </div>
                    <pre id="js-code" class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ $jsCode }}</code></pre>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        ✓ More flexible positioning<br>
                        ✓ Dynamic loading<br>
                        ✓ Better for single-page apps
                    </p>
                </div>
            </div>

            <!-- Widget URL -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Direct Widget URL</h3>
                        <button onclick="copyToClipboard('widget-url')" class="text-sm text-blue-600 hover:text-blue-800">
                            Copy URL
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="widget-url" value="{{ route('widget.show', $organization->slug) }}" readonly class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-sm">
                        <a href="{{ route('widget.show', $organization->slug) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                            Open
                        </a>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        Share this URL directly or use it in the iframe/JavaScript code above.
                    </p>
                </div>
            </div>

            <!-- Preview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Widget Preview</h3>
                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <iframe 
                            src="{{ route('widget.show', $organization->slug) }}" 
                            width="100%" 
                            height="600" 
                            frameborder="0"
                            title="Widget Preview">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Need Help?</h4>
                <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <li>• The widget automatically adapts to your website's theme</li>
                    <li>• Customers can book appointments directly from your website</li>
                    <li>• All bookings appear in your dashboard</li>
                    <li>• You can customize colors and fonts in the <a href="{{ route('organization.widget.customize', $organization) }}" class="text-blue-600 underline">customization page</a></li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.tagName === 'INPUT' ? element.value : element.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                // Show success message
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('text-green-600');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('text-green-600');
                }, 2000);
            });
        }
    </script>
</x-app-layout>
