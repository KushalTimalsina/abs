<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'gateway_type',
        'credentials',
        'settings',
        'is_active',
        'is_test_mode',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
