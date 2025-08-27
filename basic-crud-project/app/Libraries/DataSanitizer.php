<?php

namespace App\Libraries;

/**
 * Data sanitization utility
 */
class DataSanitizer
{
    /**
     * Sanitize string input
     *
     * @param string $input
     * @return string
     */
    public function sanitizeString(string $input): string
    {
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\x0B"], '', $input);
        
        // Strip HTML and PHP tags
        $input = strip_tags($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return trim($input);
    }

    /**
     * Sanitize numeric input
     *
     * @param mixed $input
     * @return float
     */
    public function sanitizeNumeric($input): float
    {
        // Remove any non-numeric characters except decimal point and minus sign
        $cleaned = preg_replace('/[^0-9.-]/', '', (string)$input);
        
        // Ensure it's a valid number, default to 0.0 if not
        return is_numeric($cleaned) ? (float)$cleaned : 0.0;
    }

    /**
     * Sanitize integer input
     *
     * @param mixed $input
     * @return int
     */
    public function sanitizeInteger($input): int
    {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize array of data
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function sanitizeArray(array $data, array $rules = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $rule = $rules[$key] ?? 'string';
            
            switch ($rule) {
                case 'string':
                    $sanitized[$key] = $this->sanitizeString((string)$value);
                    break;
                case 'numeric':
                    $sanitized[$key] = $this->sanitizeNumeric($value);
                    break;
                case 'integer':
                    $sanitized[$key] = $this->sanitizeInteger($value);
                    break;
                default:
                    $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
