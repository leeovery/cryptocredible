<?php

use Illuminate\Support\Str;

if (! function_exists('str')) {
    function str(string $value): Illuminate\Support\Stringable
    {
        return Str::of($value);
    }
}

if (! function_exists('is_negative')) {
    function is_negative(int|float|string $number): bool
    {
        return filter_var(
                $number,
                FILTER_VALIDATE_INT | FILTER_VALIDATE_FLOAT,
                ['options' => ['min_range' => 0]]
            ) === false;
    }
}

if (! function_exists('is_positive')) {
    function is_positive(int|float|string $number): bool
    {
        return ! is_negative($number);
    }
}

if (! function_exists('is_fiat')) {
    function is_fiat(string $currency): bool
    {
        return in_array((string) \str($currency)->upper()->trim(), config('app.fiat_currencies'));
    }
}
