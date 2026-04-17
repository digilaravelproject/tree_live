<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show OTP settings page.
     */
    public function otpSettings()
    {
        $page_title = 'OTP & SMS Settings';
        
        // Fetch all settings in the 'sms_auth' group
        $settings = Setting::where('group', 'sms_auth')->pluck('value', 'key')->toArray();
        
        return view('dashboard.settings.otp', compact('page_title', 'settings'));
    }

    /**
     * Update OTP settings.
     */
    public function updateOtpSettings(Request $request)
    {
        $validated = $request->validate([
            'global_otp_login_enabled' => 'required|in:0,1',
            'sms_provider' => 'required|in:2factor,vilpower',
            'two_factor_api_key' => 'nullable|string',
            'two_factor_template' => 'nullable|string',
            'vilpower_api_key' => 'nullable|string',
            'vilpower_sender_id' => 'nullable|string',
            'vilpower_template_id' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'sms_auth');
        }

        return redirect()->back()->with('success', 'OTP settings updated successfully!');
    }
}
