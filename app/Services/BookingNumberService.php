<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Booking;
use Illuminate\Support\Str;

/**
 * Booking Number Generator Service
 * 
 * Generates human-readable, sequential booking numbers
 * Format: BN.{ORG_SLUG}.{SEQUENCE}
 * 
 * Examples:
 * - BN.AVP.00001 (Adhikari Vlogs Promotion)
 * - BN.ABC.00042 (ABC Company)
 * - BNAVP00001 (compact format)
 */
class BookingNumberService
{
    /**
     * Generate booking number for organization
     * 
     * Format: MASTER.ORG.CENTER.NUMBER
     * Example: BN.AV.OM.00001
     * 
     * @param Organization $organization
     * @param string $format Options: 'dotted' (BN.AV.OM.00001) or 'compact' (BNAVOM00001)
     * @return string
     */
    public function generate(Organization $organization, string $format = 'dotted'): string
    {
        // Get settings
        $settings = $organization->settings ?? [];
        
        // Master prefix (fixed, can be hidden)
        $masterPrefix = $settings['booking_number_master_prefix'] ?? 'BN';
        $showMasterPrefix = $settings['booking_number_show_master'] ?? true;
        
        // Organization code (auto-generated, read-only)
        $orgCode = $this->generateMiniSlug($organization->name);
        
        // Center prefix (custom, editable)
        $centerPrefix = $settings['booking_number_center_prefix'] ?? '';
        
        // Get next sequence number
        $sequence = $this->getNextSequence($organization);
        $sequenceFormatted = str_pad($sequence, 5, '0', STR_PAD_LEFT);
        
        // Build booking number based on format
        if ($format === 'compact') {
            // Compact: BNAVOM00001
            $parts = [];
            if ($showMasterPrefix) {
                $parts[] = $masterPrefix;
            }
            $parts[] = $orgCode;
            if (!empty($centerPrefix)) {
                $parts[] = $centerPrefix;
            }
            $parts[] = $sequenceFormatted;
            
            return implode('', $parts);
        }
        
        // Dotted: BN.AV.OM.00001
        $parts = [];
        if ($showMasterPrefix) {
            $parts[] = $masterPrefix;
        }
        $parts[] = $orgCode;
        if (!empty($centerPrefix)) {
            $parts[] = $centerPrefix;
        }
        $parts[] = $sequenceFormatted;
        
        return implode('.', $parts);
    }

    /**
     * Generate mini slug from organization name
     * 
     * Examples:
     * - "Adhikari Vlogs Promotion" -> "AVP"
     * - "ABC Company" -> "ABC"
     * - "John's Salon" -> "JS"
     * - "Tech Solutions Inc" -> "TSI"
     * 
     * @param string $name
     * @return string
     */
    public function generateMiniSlug(string $name): string
    {
        // Remove special characters and extra spaces
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        
        // Split into words
        $words = explode(' ', $cleaned);
        
        // Strategy 1: Use first letter of each word (up to 4 words)
        if (count($words) >= 2 && count($words) <= 4) {
            $initials = '';
            foreach ($words as $word) {
                if (!empty($word)) {
                    $initials .= strtoupper($word[0]);
                }
            }
            // If we got 2-4 characters, use it
            if (strlen($initials) >= 2 && strlen($initials) <= 4) {
                return $initials;
            }
        }
        
        // Strategy 2: Single word - take first 3-4 characters
        if (count($words) === 1) {
            $word = $words[0];
            $length = min(4, max(3, strlen($word)));
            return strtoupper(substr($word, 0, $length));
        }
        
        // Strategy 3: Many words - take first letter of first 3-4 words
        if (count($words) > 4) {
            $initials = '';
            for ($i = 0; $i < min(4, count($words)); $i++) {
                if (!empty($words[$i])) {
                    $initials .= strtoupper($words[$i][0]);
                }
            }
            return $initials;
        }
        
        // Fallback: Use first 3 characters of cleaned name
        return strtoupper(substr($cleaned, 0, 3));
    }

    /**
     * Get next sequence number for organization
     * 
     * @param Organization $organization
     * @return int
     */
    protected function getNextSequence(Organization $organization): int
    {
        // Get custom starting number from settings
        $settings = $organization->settings ?? [];
        $startingNumber = $settings['booking_number_start'] ?? 1;
        
        // Get the last booking number for this organization
        $lastBooking = Booking::where('organization_id', $organization->id)
            ->whereNotNull('booking_number')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$lastBooking) {
            return $startingNumber; // Use custom starting number for first booking
        }
        
        // Extract sequence number from last booking number
        // Handles both formats: BN.AVP.00042 and BNAVP00042
        $lastNumber = $lastBooking->booking_number;
        
        // Try to extract the numeric part
        if (preg_match('/(\d{5})$/', $lastNumber, $matches)) {
            return (int)$matches[1] + 1;
        }
        
        // Fallback: count all bookings + starting number
        return $organization->bookings()->count() + $startingNumber;
    }

    /**
     * Validate booking number format
     * 
     * @param string $bookingNumber
     * @return bool
     */
    public function isValid(string $bookingNumber): bool
    {
        // Check dotted format: BN.XXX.00001 or BN.XXXX.00001
        if (preg_match('/^BN\.[A-Z]{2,4}\.\d{5}$/', $bookingNumber)) {
            return true;
        }
        
        // Check compact format: BNXXX00001 or BNXXXX00001
        if (preg_match('/^BN[A-Z]{2,4}\d{5}$/', $bookingNumber)) {
            return true;
        }
        
        return false;
    }

    /**
     * Parse booking number to extract components
     * 
     * @param string $bookingNumber
     * @return array|null
     */
    public function parse(string $bookingNumber): ?array
    {
        // Dotted format
        if (preg_match('/^BN\.([A-Z]{2,4})\.(\d{5})$/', $bookingNumber, $matches)) {
            return [
                'format' => 'dotted',
                'prefix' => 'BN',
                'org_slug' => $matches[1],
                'sequence' => (int)$matches[2],
                'full' => $bookingNumber,
            ];
        }
        
        // Compact format
        if (preg_match('/^BN([A-Z]{2,4})(\d{5})$/', $bookingNumber, $matches)) {
            return [
                'format' => 'compact',
                'prefix' => 'BN',
                'org_slug' => $matches[1],
                'sequence' => (int)$matches[2],
                'full' => $bookingNumber,
            ];
        }
        
        return null;
    }

    /**
     * Get booking number format preference from organization settings
     * 
     * @param Organization $organization
     * @return string
     */
    public function getFormatPreference(Organization $organization): string
    {
        $settings = $organization->settings ?? [];
        return $settings['booking_number_format'] ?? 'dotted';
    }

    /**
     * Set booking number format preference for organization
     * 
     * @param Organization $organization
     * @param string $format
     * @return void
     */
    public function setFormatPreference(Organization $organization, string $format): void
    {
        $settings = $organization->settings ?? [];
        $settings['booking_number_format'] = $format;
        $organization->update(['settings' => $settings]);
    }

    /**
     * Generate multiple booking numbers (for testing/preview)
     * 
     * @param Organization $organization
     * @param int $count
     * @param string $format
     * @return array
     */
    public function generateSamples(Organization $organization, int $count = 5, string $format = 'dotted'): array
    {
        $miniSlug = $this->generateMiniSlug($organization->name);
        $startSequence = $this->getNextSequence($organization);
        
        $samples = [];
        for ($i = 0; $i < $count; $i++) {
            $sequence = str_pad($startSequence + $i, 5, '0', STR_PAD_LEFT);
            
            if ($format === 'compact') {
                $samples[] = 'BN' . $miniSlug . $sequence;
            } else {
                $samples[] = 'BN.' . $miniSlug . '.' . $sequence;
            }
        }
        
        return $samples;
    }
}
