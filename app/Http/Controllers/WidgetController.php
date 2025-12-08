<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\WidgetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Display widget customization page
     */
    public function customize(Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $widgetSettings = $organization->widgetSettings ?? new WidgetSettings([
            'organization_id' => $organization->id,
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'font_family' => 'Inter, sans-serif',
            'show_logo' => true,
        ]);
        
        return view('widget.customize', compact('organization', 'widgetSettings'));
    }

    /**
     * Update widget settings
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['required', 'string', 'max:255'],
            'show_logo' => ['boolean'],
            'custom_css' => ['nullable', 'string', 'max:5000'],
            'allowed_domains' => ['nullable', 'string'],
        ]);

        // Parse allowed domains
        if (!empty($validated['allowed_domains'])) {
            $domains = array_map('trim', explode(',', $validated['allowed_domains']));
            $validated['allowed_domains'] = json_encode($domains);
        } else {
            $validated['allowed_domains'] = json_encode([]);
        }

        $validated['show_logo'] = $request->has('show_logo');

        WidgetSettings::updateOrCreate(
            ['organization_id' => $organization->id],
            $validated
        );

        return redirect()
            ->route('widget.customize', $organization)
            ->with('success', 'Widget settings updated successfully');
    }

    /**
     * Show embed code page
     */
    public function embed(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $widgetSettings = $organization->widgetSettings;
        
        if (!$widgetSettings) {
            return redirect()
                ->route('widget.customize', $organization)
                ->with('error', 'Please customize your widget first');
        }

        // Generate embed codes
        $iframeCode = $this->generateIframeCode($organization);
        $jsCode = $this->generateJsCode($organization);
        
        return view('widget.embed', compact('organization', 'widgetSettings', 'iframeCode', 'jsCode'));
    }

    /**
     * Preview widget
     */
    public function preview(Organization $organization)
    {
        $widgetSettings = $organization->widgetSettings;
        $services = $organization->services()->where('is_active', true)->get();
        
        return view('widget.preview', compact('organization', 'widgetSettings', 'services'));
    }

    /**
     * Generate iframe embed code
     */
    private function generateIframeCode(Organization $organization): string
    {
        $url = url("/widget/{$organization->slug}");
        
        return <<<HTML
<!-- Booking Widget for {$organization->name} -->
<iframe 
    src="{$url}" 
    width="100%" 
    height="600" 
    frameborder="0" 
    style="border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"
    title="Book Appointment">
</iframe>
HTML;
    }

    /**
     * Generate JavaScript embed code
     */
    private function generateJsCode(Organization $organization): string
    {
        $widgetId = 'booking-widget-' . Str::random(8);
        $url = url("/widget/{$organization->slug}");
        
        return <<<HTML
<!-- Booking Widget for {$organization->name} -->
<div id="{$widgetId}"></div>
<script>
(function() {
    var iframe = document.createElement('iframe');
    iframe.src = '{$url}';
    iframe.width = '100%';
    iframe.height = '600';
    iframe.frameBorder = '0';
    iframe.style.border = 'none';
    iframe.style.borderRadius = '8px';
    iframe.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    iframe.title = 'Book Appointment';
    
    var container = document.getElementById('{$widgetId}');
    if (container) {
        container.appendChild(iframe);
    }
})();
</script>
HTML;
    }

    /**
     * Display widget (public route)
     */
    public function show($slug)
    {
        $organization = Organization::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();
        
        // Check if organization has active subscription
        if (!$organization->hasActiveSubscription()) {
            abort(403, 'This organization\'s subscription has expired');
        }

        $widgetSettings = $organization->widgetSettings;
        $services = $organization->services()->where('is_active', true)->get();
        
        return view('widget.iframe', compact('organization', 'widgetSettings', 'services'));
    }
}
