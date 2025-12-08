<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'theme',
        'custom_css',
        'custom_js',
        'primary_color',
        'secondary_color',
        'show_logo',
        'show_organization_name',
        'welcome_message',
        'embed_code',
        'is_active',
    ];

    protected $casts = [
        'theme' => 'array',
        'show_logo' => 'boolean',
        'show_organization_name' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get analytics for this widget
     */
    public function analytics()
    {
        return $this->hasMany(WidgetAnalytics::class);
    }

    /**
     * Generate embed code for iframe
     */
    public function generateEmbedCode(): string
    {
        $url = url("/widget/{$this->organization->slug}");
        
        $iframeCode = <<<HTML
<!-- Booking Widget for {$this->organization->name} -->
<iframe 
    src="{$url}" 
    width="100%" 
    height="600" 
    frameborder="0" 
    style="border: none; border-radius: 8px;"
    title="{$this->organization->name} Booking Widget">
</iframe>
HTML;

        $this->embed_code = $iframeCode;
        $this->save();
        
        return $iframeCode;
    }
}
