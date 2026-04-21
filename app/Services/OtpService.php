<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class OtpService
{
    /**
     * Send OTP to a mobile number using configured SMS provider.
     *
     * @param string $phone
     * @param string $otp
     * @return array
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $provider = Setting::get('sms_provider', '2factor');
        
        try {
            switch ($provider) {
                case '2factor':
                    return $this->sendVia2Factor($phone, $otp);
                case 'vilpower':
                    return $this->sendViaVilpower($phone, $otp);
                default:
                    throw new Exception("Unsupported SMS provider: {$provider}");
            }
        } catch (Exception $e) {
            Log::error("OTP Service Error ({$provider}): " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Failed to send OTP. Please try again later.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Send OTP via 2factor.in
     * 
     * Handles both Default AUTOGEN flow and Custom DLT Template flow.
     * 
     * @param string $phone
     * @param string $otp
     * @return array
     * @throws Exception
     */
    private function sendVia2Factor(string $phone, string $otp): array
    {
        $apiKey = Setting::get('two_factor_api_key');
        $template = Setting::get('two_factor_template');
        $method = Setting::get('two_factor_method', 'SMS'); // New Setting: SMS or VOICE

        if (empty($apiKey)) {
            throw new Exception("2Factor API Key is not configured in settings.");
        }

        $templateUpper = strtoupper(trim($template ?? ''));
        $methodUpper = strtoupper(trim($method ?? 'SMS'));

        Log::info("OtpService: Sending via 2Factor. Method: $methodUpper, Template: $templateUpper");

        if ($methodUpper === 'VOICE') {
            // Voice OTP URL
            $url = "https://2factor.in/API/V1/{$apiKey}/VOICE/{$phone}/{$otp}";
        } else {
            // SMS OTP URL
            if (empty($templateUpper) || $templateUpper === 'AUTOGEN') {
                // Scenario A: Default Transactional SMS
                $url = "https://2factor.in/API/V1/{$apiKey}/SMS/{$phone}/{$otp}/AUTOGEN";
            } else {
                // Scenario B: Custom DLT Template
                // Manual Generation format: API_KEY/SMS/PHONE/OTP/TEMPLATE
                $url = "https://2factor.in/API/V1/{$apiKey}/SMS/{$phone}/{$otp}/{$template}";
            }
        }
        
        Log::info("OtpService: 2Factor URL: $url");

        $response = Http::get($url);

        Log::info("OtpService: 2Factor Response: " . $response->body());

        if ($response->successful()) {
            return [
                'status' => 'success',
                'message' => 'OTP sent successfully (' . $methodUpper . ').',
                'data' => $response->json()
            ];
        }

        throw new Exception("2Factor API returned error: " . $response->body());
    }

    /**
     * Send OTP via vilpower.in (Vi DLT)
     * 
     * @param string $phone
     * @param string $otp
     * @return array
     * @throws Exception
     */
    private function sendViaVilpower(string $phone, string $otp): array
    {
        $apiKey = Setting::get('vilpower_api_key');
        $senderId = Setting::get('vilpower_sender_id');
        $templateId = Setting::get('vilpower_template_id');
        $entityId = Setting::get('vilpower_entity_id');

        if (empty($apiKey)) {
            throw new Exception("Vilpower API Key is not configured in settings.");
        }

        $url = "http://sms.vilpower.in/api/v2/sms/send";

        $response = Http::post($url, [
            'apiKey' => $apiKey,
            'senderId' => $senderId,
            'mobileNo' => $phone,
            'message' => "Your OTP is {$otp}", // Ensure this matches DLT template exactly
            'templateId' => $templateId,
            'entityId' => $entityId,
        ]);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'message' => 'OTP sent successfully.',
                'data' => $response->json()
            ];
        }

        throw new Exception("Vilpower API returned error: " . $response->body());
    }
}
