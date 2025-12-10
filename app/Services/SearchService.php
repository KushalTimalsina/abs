<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Slot;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Search Service implementing classical search algorithms
 * 
 * This service demonstrates:
 * 1. Linear Search Algorithm - O(n) complexity
 * 2. Binary Search Algorithm - O(log n) complexity
 */
class SearchService
{
    /**
     * LINEAR SEARCH ALGORITHM
     * 
     * Search through unsorted collection sequentially
     * Time Complexity: O(n)
     * Space Complexity: O(1)
     * 
     * Use case: Searching in unsorted data or when data size is small
     */
    public function linearSearchBookingByNumber(Collection $bookings, string $bookingNumber): ?Booking
    {
        // Iterate through each element sequentially
        foreach ($bookings as $booking) {
            // Compare each element with target
            if ($booking->booking_number === $bookingNumber) {
                return $booking; // Found - return immediately
            }
        }
        
        return null; // Not found after checking all elements
    }

    /**
     * LINEAR SEARCH - Find booking by customer email
     * 
     * Time Complexity: O(n)
     * Best Case: O(1) - found at first position
     * Worst Case: O(n) - found at last position or not found
     */
    public function linearSearchBookingByEmail(Collection $bookings, string $email): Collection
    {
        $results = collect();
        
        // Linear scan through all bookings
        foreach ($bookings as $booking) {
            if (strtolower($booking->customer_email) === strtolower($email)) {
                $results->push($booking);
            }
        }
        
        return $results;
    }

    /**
     * LINEAR SEARCH - Find slots by staff name (partial match)
     * 
     * Time Complexity: O(n * m) where m is string comparison length
     */
    public function linearSearchSlotsByStaffName(Collection $slots, string $staffName): Collection
    {
        $results = collect();
        $searchTerm = strtolower($staffName);
        
        foreach ($slots as $slot) {
            if ($slot->assignedStaff && 
                str_contains(strtolower($slot->assignedStaff->name), $searchTerm)) {
                $results->push($slot);
            }
        }
        
        return $results;
    }

    /**
     * BINARY SEARCH ALGORITHM
     * 
     * Search through SORTED collection using divide-and-conquer
     * Time Complexity: O(log n)
     * Space Complexity: O(1) - iterative version
     * 
     * REQUIREMENT: Collection MUST be sorted by the search key
     * 
     * Use case: Searching in large sorted datasets (much faster than linear)
     */
    public function binarySearchBookingById(Collection $bookings, int $targetId): ?Booking
    {
        // Convert to array for index-based access
        $array = $bookings->values()->all();
        $left = 0;
        $right = count($array) - 1;
        
        // Binary search loop
        while ($left <= $right) {
            // Find middle index (prevents integer overflow)
            $mid = $left + (int)(($right - $left) / 2);
            
            $midId = $array[$mid]->id;
            
            // Check if target is at middle
            if ($midId === $targetId) {
                return $array[$mid]; // Found!
            }
            
            // If target is greater, ignore left half
            if ($midId < $targetId) {
                $left = $mid + 1;
            }
            // If target is smaller, ignore right half
            else {
                $right = $mid - 1;
            }
        }
        
        return null; // Not found
    }

    /**
     * BINARY SEARCH - Find slot by start time in sorted collection
     * 
     * Time Complexity: O(log n)
     * 
     * @param Collection $slots - MUST be sorted by start_time
     * @param string $targetTime - Format: 'H:i:s' or 'H:i'
     */
    public function binarySearchSlotByTime(Collection $slots, string $targetTime): ?Slot
    {
        $array = $slots->values()->all();
        $left = 0;
        $right = count($array) - 1;
        
        // Normalize target time
        $targetTimestamp = strtotime($targetTime);
        
        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);
            
            $midTime = strtotime($array[$mid]->start_time);
            
            if ($midTime === $targetTimestamp) {
                return $array[$mid];
            }
            
            if ($midTime < $targetTimestamp) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
        
        return null;
    }

    /**
     * BINARY SEARCH - Find booking by date in sorted collection
     * 
     * Returns the FIRST booking on the target date
     * 
     * @param Collection $bookings - MUST be sorted by booking_date
     */
    public function binarySearchBookingByDate(Collection $bookings, Carbon $targetDate): ?Booking
    {
        $array = $bookings->values()->all();
        $left = 0;
        $right = count($array) - 1;
        $result = null;
        
        $targetDateStr = $targetDate->format('Y-m-d');
        
        // Modified binary search to find first occurrence
        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);
            
            $midDateStr = $array[$mid]->booking_date->format('Y-m-d');
            
            if ($midDateStr === $targetDateStr) {
                $result = $array[$mid];
                // Continue searching in left half for first occurrence
                $right = $mid - 1;
            } elseif ($midDateStr < $targetDateStr) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
        
        return $result;
    }

    /**
     * HYBRID SEARCH - Combines Binary Search with Linear Search
     * 
     * 1. Use binary search to find approximate position (O(log n))
     * 2. Use linear search in nearby range for exact match (O(k))
     * 
     * Total Complexity: O(log n + k) where k is range size
     */
    public function hybridSearchBookingByPrice(Collection $bookings, float $targetPrice, float $tolerance = 0.01): Collection
    {
        // First, sort by price (required for binary search)
        $sorted = $bookings->sortBy('total_price')->values();
        $array = $sorted->all();
        
        $left = 0;
        $right = count($array) - 1;
        $foundIndex = -1;
        
        // Binary search to find approximate position
        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);
            
            $midPrice = $array[$mid]->total_price;
            
            if (abs($midPrice - $targetPrice) <= $tolerance) {
                $foundIndex = $mid;
                break;
            }
            
            if ($midPrice < $targetPrice) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
        
        // If not found, return empty collection
        if ($foundIndex === -1) {
            return collect();
        }
        
        // Linear search in nearby range to find all matches
        $results = collect();
        
        // Search backwards
        for ($i = $foundIndex; $i >= 0; $i--) {
            if (abs($array[$i]->total_price - $targetPrice) <= $tolerance) {
                $results->push($array[$i]);
            } else {
                break;
            }
        }
        
        // Search forwards
        for ($i = $foundIndex + 1; $i < count($array); $i++) {
            if (abs($array[$i]->total_price - $targetPrice) <= $tolerance) {
                $results->push($array[$i]);
            } else {
                break;
            }
        }
        
        return $results;
    }

    /**
     * ADVANCED SEARCH - Multi-criteria search with filtering
     * 
     * Combines multiple search algorithms for complex queries
     */
    public function advancedBookingSearch(array $criteria): Collection
    {
        $query = Booking::query();
        
        // Apply filters
        if (isset($criteria['organization_id'])) {
            $query->where('organization_id', $criteria['organization_id']);
        }
        
        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }
        
        if (isset($criteria['date_from'])) {
            $query->whereDate('booking_date', '>=', $criteria['date_from']);
        }
        
        if (isset($criteria['date_to'])) {
            $query->whereDate('booking_date', '<=', $criteria['date_to']);
        }
        
        if (isset($criteria['min_price'])) {
            $query->where('total_price', '>=', $criteria['min_price']);
        }
        
        if (isset($criteria['max_price'])) {
            $query->where('total_price', '<=', $criteria['max_price']);
        }
        
        // Text search using LIKE (database-level linear search)
        if (isset($criteria['customer_name'])) {
            $query->where('customer_name', 'LIKE', '%' . $criteria['customer_name'] . '%');
        }
        
        if (isset($criteria['customer_email'])) {
            $query->where('customer_email', 'LIKE', '%' . $criteria['customer_email'] . '%');
        }
        
        if (isset($criteria['booking_number'])) {
            $query->where('booking_number', 'LIKE', '%' . $criteria['booking_number'] . '%');
        }
        
        // Sort for potential binary search later
        $sortBy = $criteria['sort_by'] ?? 'booking_date';
        $sortOrder = $criteria['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->get();
    }

    /**
     * PERFORMANCE COMPARISON HELPER
     * 
     * Compares Linear vs Binary search performance
     */
    public function compareSearchPerformance(Collection $bookings, int $targetId): array
    {
        // Ensure collection is sorted for binary search
        $sorted = $bookings->sortBy('id')->values();
        
        // Linear Search
        $linearStart = microtime(true);
        $linearResult = $this->linearSearchBookingById($bookings, $targetId);
        $linearTime = (microtime(true) - $linearStart) * 1000; // Convert to milliseconds
        
        // Binary Search
        $binaryStart = microtime(true);
        $binaryResult = $this->binarySearchBookingById($sorted, $targetId);
        $binaryTime = (microtime(true) - $binaryStart) * 1000;
        
        return [
            'collection_size' => $bookings->count(),
            'target_id' => $targetId,
            'linear_search' => [
                'found' => $linearResult !== null,
                'time_ms' => round($linearTime, 4),
                'complexity' => 'O(n)',
            ],
            'binary_search' => [
                'found' => $binaryResult !== null,
                'time_ms' => round($binaryTime, 4),
                'complexity' => 'O(log n)',
            ],
            'speedup' => $linearTime > 0 ? round($linearTime / $binaryTime, 2) . 'x faster' : 'N/A',
        ];
    }

    /**
     * Helper: Linear search by ID (for comparison)
     */
    private function linearSearchBookingById(Collection $bookings, int $targetId): ?Booking
    {
        foreach ($bookings as $booking) {
            if ($booking->id === $targetId) {
                return $booking;
            }
        }
        return null;
    }
}
