<?php

if (! function_exists('formatPhoneNumber')) {
    function formatPhoneNumber(string $phone): string
    {
        // Remove everything except digits
        $phone = preg_replace('/\D+/', '', $phone);

        // Already international (855...)
        if (str_starts_with($phone, '855')) {
            return '+' . $phone;
        }

        // Local starting with 0
        if (str_starts_with($phone, '0')) {
            return '+855' . substr($phone, 1);
        }

        // Local without zero
        return '+855' . $phone;
    }
}
