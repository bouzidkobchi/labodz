<?php

namespace App\Helpers;

/**
 * Class BarcodeHelper
 * 
 * Provides professional QR code generation using the QRServer API.
 * Designed for institutional healthcare environments with robust error handling.
 */
class BarcodeHelper
{
    /**
     * Generate a Professional QR Code via QRServer API
     * Returns a URL that can be used in <img> tags
     */
    public static function getQRUrl($data, $size = '150x150')
    {
        // Encode data for URL safety
        $encodedData = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data={$encodedData}";
    }

    /**
     * Generate a Professional QR code as a Base64 string (for reliability in PDFs)
     * Includes a strict timeout and SSL bypass to ensure PDF generation doesn't crash.
     */
    public static function getQRBase64($data, $size = '200x200')
    {
        $url = self::getQRUrl($data, $size);
        
        // Define a strict short timeout (2 seconds) to prevent PDF hanging
        // SSL verification is disabled to ensure compatibility in varied hosting environments
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        try {
            // Fetch the image from the API
            $imageContent = @file_get_contents($url, false, $context);
            
            if ($imageContent === false || empty($imageContent)) {
                return null;
            }
            
            return 'data:image/png;base64,' . base64_encode($imageContent);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Legacy method retained for backward compatibility with existing views
     */
    public static function getBarcodeHTML($code, $size = '150x150')
    {
        return self::getQRUrl($code, $size);
    }
}
