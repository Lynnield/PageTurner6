<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsbnRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The ISBN must be a string.');
            return;
        }

        $isbn = preg_replace('/[^0-9Xx]/', '', $value) ?? '';

        if ($isbn === '') {
            $fail('The ISBN is required.');
            return;
        }

        if (strlen($isbn) === 10) {
            if (! $this->isValidIsbn10($isbn)) {
                $fail('The ISBN-10 is invalid.');
            }
            return;
        }

        if (strlen($isbn) === 13) {
            if (! $this->isValidIsbn13($isbn)) {
                $fail('The ISBN-13 is invalid.');
            }
            return;
        }

        $fail('The ISBN must be a valid ISBN-10 or ISBN-13.');
    }

    private function isValidIsbn10(string $isbn): bool
    {
        if (! preg_match('/^[0-9]{9}[0-9Xx]$/', $isbn)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $isbn[$i] * (10 - $i);
        }

        $check = strtoupper($isbn[9]) === 'X' ? 10 : (int) $isbn[9];
        $sum += $check;

        return ($sum % 11) === 0;
    }

    private function isValidIsbn13(string $isbn): bool
    {
        if (! preg_match('/^[0-9]{13}$/', $isbn)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $isbn[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $check = (10 - ($sum % 10)) % 10;

        return $check === (int) $isbn[12];
    }
}

