<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'is_active',
        'qr_code_path',
        'account_details',
        'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'account_details' => 'array',
    ];

    /**
     * Get all active payment gateways
     */
    public static function activeGateways()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Get specific gateway settings
     */
    public static function getGateway($gateway)
    {
        return self::where('gateway', $gateway)->first();
    }

    /**
     * Check if gateway is active
     */
    public static function isGatewayActive($gateway)
    {
        $setting = self::where('gateway', $gateway)->first();
        return $setting && $setting->is_active;
    }
}
