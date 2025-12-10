<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\SearchService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Search Controller
 *
 * Demonstrates classical search algorithms:
 * - Linear Search
 * - Binary Search
 * - Hybrid Search
 */
class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search bookings using various algorithms
     */
    public function searchBookings(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);

        $validated = $request->validate([
            'search_type' => ['required', 'in:linear,binary,advanced'],
            'search_field' => ['required', 'in:booking_number,email,id,date,price'],
            'search_value' => ['required'],
        ]);

        // Get all bookings for the organization
        $bookings = $organization->bookings()
            ->with(['customer', 'service', 'staff'])
            ->get();

        $results = collect();
        $algorithm = '';
        $complexity = '';

        switch ($validated['search_type']) {
            case 'linear':
                // LINEAR SEARCH
                if ($validated['search_field'] === 'booking_number') {
                    $result = $this->searchService->linearSearchBookingByNumber(
                        $bookings,
                        $validated['search_value']
                    );
                    $results = $result ? collect([$result]) : collect();
                    $algorithm = 'Linear Search';
                    $complexity = 'O(n)';
                } elseif ($validated['search_field'] === 'email') {
                    $results = $this->searchService->linearSearchBookingByEmail(
                        $bookings,
                        $validated['search_value']
                    );
                    $algorithm = 'Linear Search';
                    $complexity = 'O(n)';
                }
                break;

            case 'binary':
                // BINARY SEARCH (requires sorted data)
                if ($validated['search_field'] === 'id') {
                    $sorted = $bookings->sortBy('id');
                    $result = $this->searchService->binarySearchBookingById(
                        $sorted,
                        (int) $validated['search_value']
                    );
                    $results = $result ? collect([$result]) : collect();
                    $algorithm = 'Binary Search';
                    $complexity = 'O(log n)';
                } elseif ($validated['search_field'] === 'date') {
                    $sorted = $bookings->sortBy('booking_date');
                    $result = $this->searchService->binarySearchBookingByDate(
                        $sorted,
                        Carbon::parse($validated['search_value'])
                    );
                    $results = $result ? collect([$result]) : collect();
                    $algorithm = 'Binary Search';
                    $complexity = 'O(log n)';
                }
                break;

            case 'advanced':
                // ADVANCED MULTI-CRITERIA SEARCH
                $criteria = [
                    'organization_id' => $organization->id,
                ];

                if ($validated['search_field'] === 'booking_number') {
                    $criteria['booking_number'] = $validated['search_value'];
                } elseif ($validated['search_field'] === 'email') {
                    $criteria['customer_email'] = $validated['search_value'];
                }

                $results = $this->searchService->advancedBookingSearch($criteria);
                $algorithm = 'Advanced Search (Multi-criteria)';
                $complexity = 'O(n) with database indexing';
                break;
        }

        return response()->json([
            'success' => true,
            'algorithm' => $algorithm,
            'complexity' => $complexity,
            'total_records' => $bookings->count(),
            'results_found' => $results->count(),
            'results' => $results->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => $booking->customer_email,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'status' => $booking->status,
                    'total_price' => $booking->total_price,
                ];
            }),
        ]);
    }

    /**
     * Compare Linear vs Binary Search performance
     */
    public function compareSearchPerformance(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);

        $validated = $request->validate([
            'booking_id' => ['required', 'integer'],
        ]);

        $bookings = $organization->bookings()->get();

        $comparison = $this->searchService->compareSearchPerformance(
            $bookings,
            $validated['booking_id']
        );

        return response()->json([
            'success' => true,
            'comparison' => $comparison,
            'explanation' => [
                'linear_search' => 'Checks each element sequentially until found or end reached',
                'binary_search' => 'Divides sorted array in half repeatedly, eliminating half each time',
                'recommendation' => $bookings->count() > 100
                    ? 'Use Binary Search for large datasets (faster)'
                    : 'Both algorithms perform similarly for small datasets',
            ],
        ]);
    }

    /**
     * Advanced search with multiple criteria
     */
    public function advancedSearch(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string'],
            'customer_email' => ['nullable', 'email'],
            'booking_number' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,no_show'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort_by' => ['nullable', 'in:booking_date,total_price,created_at'],
            'sort_order' => ['nullable', 'in:asc,desc'],
        ]);

        $criteria = array_merge(
            ['organization_id' => $organization->id],
            array_filter($validated) // Remove null values
        );

        $results = $this->searchService->advancedBookingSearch($criteria);

        return response()->json([
            'success' => true,
            'total_results' => $results->count(),
            'results' => $results->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => $booking->customer_email,
                    'customer_phone' => $booking->customer_phone,
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'start_time' => $booking->start_time->format('H:i'),
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'total_price' => $booking->total_price,
                    'service' => $booking->service->name ?? null,
                ];
            }),
        ]);
    }

    /**
     * Display search demo page
     */
    public function demo(Organization $organization)
    {
        $this->authorize('view', $organization);

        $bookingsCount = $organization->bookings()->count();

        return view('search.demo', compact('organization', 'bookingsCount'));
    }
}
