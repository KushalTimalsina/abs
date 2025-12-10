<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Organization;
use App\Services\BookingNumberService;

class UpdateBookingNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-numbers 
                            {--organization= : Specific organization slug}
                            {--format=dotted : Format (dotted or compact)}
                            {--dry-run : Preview changes without applying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing booking numbers to new format (BN.AVP.00001)';

    protected $bookingNumberService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->bookingNumberService = app(BookingNumberService::class);
        
        $dryRun = $this->option('dry-run');
        $format = $this->option('format');
        $orgSlug = $this->option('organization');

        $this->info('🔄 Updating Booking Numbers to New Format');
        $this->info('Format: ' . $format);
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be saved');
        }

        // Get organizations to process
        if ($orgSlug) {
            $organizations = Organization::where('slug', $orgSlug)->get();
            if ($organizations->isEmpty()) {
                $this->error("Organization '{$orgSlug}' not found!");
                return 1;
            }
        } else {
            $organizations = Organization::all();
        }

        $totalUpdated = 0;

        foreach ($organizations as $organization) {
            $this->info("\n📋 Processing: {$organization->name}");
            $miniSlug = $this->bookingNumberService->generateMiniSlug($organization->name);
            $this->info("   Mini Slug: {$miniSlug}");

            // Get all bookings for this organization, ordered by ID
            $bookings = $organization->bookings()
                ->orderBy('id', 'asc')
                ->get();

            if ($bookings->isEmpty()) {
                $this->warn("   No bookings found");
                continue;
            }

            $this->info("   Found {$bookings->count()} bookings");

            // Update each booking with sequential number
            $sequence = 1;
            $updates = [];

            foreach ($bookings as $booking) {
                $oldNumber = $booking->booking_number;
                
                // Generate new booking number
                $sequenceFormatted = str_pad($sequence, 5, '0', STR_PAD_LEFT);
                
                if ($format === 'compact') {
                    $newNumber = 'BN' . $miniSlug . $sequenceFormatted;
                } else {
                    $newNumber = 'BN.' . $miniSlug . '.' . $sequenceFormatted;
                }

                $updates[] = [
                    'id' => $booking->id,
                    'old' => $oldNumber,
                    'new' => $newNumber,
                ];

                if (!$dryRun) {
                    $booking->update(['booking_number' => $newNumber]);
                }

                $sequence++;
            }

            // Display updates
            $this->table(
                ['ID', 'Old Number', 'New Number'],
                array_map(function($update) {
                    return [
                        $update['id'],
                        $update['old'],
                        '<fg=green>' . $update['new'] . '</>',
                    ];
                }, $updates)
            );

            $totalUpdated += count($updates);

            // Set organization format preference
            if (!$dryRun) {
                $organization->setBookingNumberFormat($format);
                $this->info("   ✅ Set organization format to: {$format}");
            }
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->warn("⚠️  DRY RUN COMPLETE - {$totalUpdated} bookings would be updated");
            $this->info("Run without --dry-run to apply changes");
        } else {
            $this->info("✅ Successfully updated {$totalUpdated} booking numbers!");
        }

        return 0;
    }
}
