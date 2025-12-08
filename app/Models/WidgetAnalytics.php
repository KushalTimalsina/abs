<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'widget_settings_id',
        'date',
        'views',
        'bookings',
        'conversions',
        'referrer_url',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the widget settings
     */
    public function widgetSettings()
    {
        return $this->belongsTo(WidgetSettings::class);
    }

    /**
     * Calculate conversion rate
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->views == 0) return 0;
        return ($this->bookings / $this->views) * 100;
    }

    /**
     * Increment views
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Increment bookings
     */
    public function incrementBookings()
    {
        $this->increment('bookings');
        $this->conversions = $this->bookings;
        $this->save();
    }
}
