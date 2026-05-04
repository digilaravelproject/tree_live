<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Handle auto-detection of user location using coordinates.
     */
    public function autoDetect(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $latitude = $request->latitude;
            $longitude = $request->longitude;

            // Call external API to get address (Reverse Geocoding)
            $addressData = $this->getDetailedAddressFromCoordinates($latitude, $longitude);

            if (!$addressData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine address. Please enter manually.'
                ]);
            }

            return response()->json([
                'success' => true,
                'full_address' => $addressData['full_address'],
                'display_name' => $addressData['full_address'], // Fallback
                'location_data' => $addressData
            ]);
        } catch (\Exception $e) {
            Log::error('Location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error while detecting location.'
            ], 500);
        }
    }

    /**
     * Get address from coordinates using OpenStreetMap (Nominatim).
     */
    private function getDetailedAddressFromCoordinates($latitude, $longitude)
    {
        try {
            // Using OpenStreetMap (Nominatim) - Free and requires no API Key for small usage
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'LaravelApp/1.0' // Required by Nominatim
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'addressdetails' => 1,
                    'zoom' => 18
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Construct a readable address string
                $display_name = $data['display_name'] ?? '';

                // If you want to customize the address format:
                $addr = $data['address'] ?? [];
                $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? '';
                $state = $addr['state'] ?? '';
                $postcode = $addr['postcode'] ?? '';

                return [
                    'full_address' => $display_name,
                    'city' => $city,
                    'state' => $state,
                    'zipcode' => $postcode
                ];
            }
        } catch (\Exception $e) {
            // Fail silently and return null
        }

        return null;
    }
}
