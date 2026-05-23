<?php

namespace App\Services;

class PhoneNumberService
{
    /**
     * Format phone number to international standard (E.164)
     * 
     * @param string $phoneNumber
     * @param string $defaultCountryCode Default country code (without +)
     * @return string
     */
    public function formatToInternational(string $phoneNumber, string $defaultCountryCode = '94'): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0, replace with country code
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = $defaultCountryCode . substr($cleaned, 1);
        }
        
        // If it doesn't start with country code, add it
        if (!str_starts_with($cleaned, $defaultCountryCode)) {
            $cleaned = $defaultCountryCode . $cleaned;
        }
        
        // Add + prefix for E.164 format
        return '+' . $cleaned;
    }

    /**
     * Format multiple phone numbers
     * 
     * @param array $data Array with phone_number and phone_number_secondary keys
     * @param string $defaultCountryCode
     * @return array
     */
    public function formatPhoneNumbersInData(array $data, string $defaultCountryCode = '94'): array
    {
        if (isset($data['phone_number'])) {
            $data['phone_number'] = $this->formatToInternational($data['phone_number'], $defaultCountryCode);
        }
        
        if (!empty($data['phone_number_secondary'])) {
            $data['phone_number_secondary'] = $this->formatToInternational($data['phone_number_secondary'], $defaultCountryCode);
        }
        
        return $data;
    }
}
