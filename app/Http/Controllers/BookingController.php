<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use App\Services\SlotGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $slotService;

    public function __construct(SlotGenerationService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Display bookings
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);
        
        $query = $organization->bookings()->with(['customer', 'service', 'staff', 'slot']);

        // Text Search Filter (Linear Search at database level)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('booking_number', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('customer_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('customer_email', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('customer_phone', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }

        // Filter by staff (for team members viewing their own bookings)
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at'); // Default sort by created_at
        $sortDirection = $request->input('direction', 'desc'); // Default descending (latest first)
        
        // Validate sort field
        $allowedSortFields = ['id', 'booking_number', 'customer_name', 'booking_date', 'status', 'payment_status', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 20);
        $bookings = $query->paginate($perPage)->withQueryString();
        
        return view('bookings.index', compact('organization', 'bookings'));
    }

    /**
     * Show create booking form
     */
    public function create(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $services = $organization->services()->where('is_active', true)->get();
        
        return view('bookings.create', compact('organization', 'services'));
    }

    /**
     * Get available slots for a service
     */
    public function getAvailableSlots(Organization $organization, Service $service, Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $date = Carbon::parse($validated['date']);
        
        // Generate slots if they don't exist
        $this->slotService->generateSlotsForDate($organization, $service, $date);
        
        // Get available slots
        $slots = $this->slotService->getAvailableSlots(
            $organization,
            $service,
            $date->startOfDay(),
            $date->endOfDay()
        );

        return response()->json([
            'slots' => $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'staff_name' => $slot->assignedStaff->name,
                    'available' => $slot->current_bookings < $slot->max_bookings,
                ];
            }),
        ]);
    }

    /**
     * Store a new booking
     */
    public function store(Request $request, Organization $organization)
{
    $validated = $request->validate([
        'service_id' => ['required', 'exists:services,id'],
        'customer_name' => ['required', 'string', 'max:255'],
        'customer_email' => ['required', 'email', 'max:255'],
        'customer_phone' => ['required', 'string', 'max:20'],
        'booking_date' => ['required', 'date', 'after_or_equal:today'],
        'start_time' => ['required'],
        'notes' => ['nullable', 'string', 'max:1000'],
    ]);

    DB::beginTransaction();
    
    try {
        $service = Service::findOrFail($validated['service_id']);

        // Verify service belongs to organization
        if ($service->organization_id !== $organization->id) {
            throw new \Exception('Invalid service');
        }

        // Find or create customer
        $customer = User::firstOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'user_type' => 'customer',
                'password' => bcrypt(str()->random(16)), // Random password
            ]
        );

        // Calculate end time based on service duration
        $startDateTime = \Carbon\Carbon::parse($validated['booking_date'] . ' ' . $validated['start_time']);
        $endDateTime = $startDateTime->copy()->addMinutes($service->duration);

        // Create booking
        $booking = Booking::create([
            'organization_id' => $organization->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'slot_id' => null, // Manual booking doesn't require slot
            'staff_id' => null, // Can be assigned later
            'booking_date' => $validated['booking_date'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash', // Default to cash for manual bookings
            'total_price' => $service->price,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'notes' => $validated['notes'],
        ]);

        DB::commit();

        return redirect()
            ->route('organization.bookings.show', [$organization, $booking])
            ->with('success', 'Booking created successfully!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to create booking: ' . $e->getMessage());
    }
}
    /**
     * Display booking details
     */
    public function show(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $booking->load(['customer', 'service', 'staff', 'slot', 'payment']);
        
        return view('bookings.show', compact('organization', 'booking'));
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, Organization $organization, Booking $booking)
    {
        $this->authorize('view', $organization);
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,completed,cancelled'],
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        // If cancelling, free up the slot
        if ($validated['status'] === 'cancelled' && in_array($oldStatus, ['pending', 'confirmed'])) {
            $slot = $booking->slot;
            if ($slot) {
                $slot->decrement('current_bookings');
                if ($slot->status === 'booked' && $slot->current_bookings < $slot->max_bookings) {
                    $slot->update(['status' => 'available']);
                }
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Booking status updated successfully');
    }

    /**
     * Cancel booking
     */
    public function cancel(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        // Only allow cancellation of pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()
                ->back()
                ->with('error', 'Cannot cancel this booking');
        }

        DB::beginTransaction();
        
        try {
            $booking->update(['status' => 'cancelled']);

            // Free up the slot
            $slot = $booking->slot;
            if ($slot) {
                $slot->decrement('current_bookings');
                if ($slot->status === 'booked' && $slot->current_bookings < $slot->max_bookings) {
                    $slot->update(['status' => 'available']);
                }
            }

            DB::commit();

            return redirect()
                ->route('bookings.index', $organization)
                ->with('success', 'Booking cancelled successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    /**
     * Confirm booking
     */
    public function confirm(Organization $organization, Booking $booking)
    {
        // Customers cannot confirm bookings
        if (auth()->user()->user_type === 'customer') {
            abort(403, 'Customers are not authorized to confirm bookings.');
        }
        
        // Verify booking belongs to organization
        if ($booking->organization_id !== $organization->id) {
            abort(403);
        }

        // Check if booking can be confirmed
        if ($booking->status !== 'pending') {
            return redirect()
                ->route('organization.bookings.show', [$organization, $booking])
                ->with('error', 'This booking cannot be confirmed.');
        }

        // Update booking status
        $booking->update([
            'status' => 'confirmed',
        ]);

        // TODO: Send confirmation notification to customer

        return redirect()
            ->route('organization.bookings.show', [$organization, $booking])
            ->with('success', 'Booking confirmed successfully.');
    }

    /**
     * Show edit booking form
     */
    public function edit(Organization $organization, Booking $booking)
    {
        // Customers cannot edit bookings
        if (auth()->user()->user_type === 'customer') {
            abort(403, 'Customers are not authorized to edit bookings.');
        }
        
        $this->authorize('view', $organization);
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $services = $organization->services()->where('is_active', true)->get();
        $staff = $organization->users()->where('user_type', 'team')->get();

        return view('bookings.edit', compact('organization', 'booking', 'services', 'staff'));
    }

    /**
     * Update booking
     */
    public function update(Request $request, Organization $organization, Booking $booking)
    {
        // Customers cannot update bookings
        if (auth()->user()->user_type === 'customer') {
            abort(403, 'Customers are not authorized to update bookings.');
        }
        
        $this->authorize('view', $organization);
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'status' => 'required|in:pending,confirmed,completed,cancelled,no_show',
            'notes' => 'nullable|string|max:1000',
        ]);

        $booking->update($validated);

        return redirect()
            ->route('organization.bookings.show', [$organization, $booking])
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Mark booking as completed
     */
    public function complete(Organization $organization, Booking $booking)
    {
        // Customers cannot mark bookings as complete
        if (auth()->user()->user_type === 'customer') {
            abort(403, 'Customers are not authorized to mark bookings as complete.');
        }
        // Verify booking belongs to organization
        if ($booking->organization_id !== $organization->id) {
            abort(403);
        }

        // Check if booking can be completed
        if ($booking->status !== 'confirmed') {
            return redirect()
                ->route('organization.bookings.show', [$organization, $booking])
                ->with('error', 'Only confirmed bookings can be marked as completed.');
        }

        // Update booking status
        $booking->update([
            'status' => 'completed',
        ]);

        // TODO: Send completion notification to customer

        return redirect()
            ->route('organization.bookings.show', [$organization, $booking])
            ->with('success', 'Booking marked as completed successfully.');
    }
}
