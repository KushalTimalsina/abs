<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentSettingsController extends Controller
{
    /**
     * Display all payment settings
     */
    public function index()
    {
        $settings = PaymentSetting::all();
        return view('superadmin.payment-settings.index', compact('settings'));
    }

    /**
     * Show edit form for specific gateway
     */
    public function edit($gateway)
    {
        $setting = PaymentSetting::where('gateway', $gateway)->firstOrFail();
        return view('superadmin.payment-settings.edit', compact('setting'));
    }

    /**
     * Update gateway settings
     */
    public function update(Request $request, $gateway)
    {
        $setting = PaymentSetting::where('gateway', $gateway)->firstOrFail();

        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'qr_code' => 'nullable|image|max:2048',
            'account_details' => 'nullable|array',
            'instructions' => 'nullable|string',
        ]);

        // Handle QR code upload
        if ($request->hasFile('qr_code')) {
            // Delete old QR code if exists
            if ($setting->qr_code_path) {
                Storage::disk('public')->delete($setting->qr_code_path);
            }

            $path = $request->file('qr_code')->store('payment-qr', 'public');
            $setting->qr_code_path = $path;
        }

        // Update settings
        $setting->is_active = $request->has('is_active');
        $setting->account_details = $validated['account_details'] ?? $setting->account_details;
        $setting->instructions = $validated['instructions'] ?? $setting->instructions;
        $setting->save();

        return redirect()->route('superadmin.payment-settings.index')
            ->with('success', ucfirst($gateway) . ' settings updated successfully');
    }

    /**
     * Delete QR code
     */
    public function deleteQr($gateway)
    {
        $setting = PaymentSetting::where('gateway', $gateway)->firstOrFail();

        if ($setting->qr_code_path) {
            Storage::disk('public')->delete($setting->qr_code_path);
            $setting->qr_code_path = null;
            $setting->save();
        }

        return redirect()->back()
            ->with('success', 'QR code deleted successfully');
    }
}
