@props(['perPage' => 20])

<div class="flex items-center space-x-2">
    <label for="per_page" class="text-sm text-gray-700 dark:text-gray-300">Show:</label>
    <select name="per_page" id="per_page" 
            onchange="this.form.submit()"
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
        <option value="20" {{ request('per_page', $perPage) == 20 ? 'selected' : '' }}>20</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
    </select>
    <span class="text-sm text-gray-700 dark:text-gray-300">entries</span>
</div>
