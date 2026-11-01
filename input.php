<?php
function post_text(string $key, int $maximum): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value) || strlen($value) > $maximum || str_contains($value, "\0") || preg_match('//u', $value) !== 1) {
        http_response_code(422);
        exit('Invalid input.');
    }
    return $value;
}
function safe_product_image(?string $value): string
{
    $value = $value ?? '';
    return preg_match('#^images/[A-Za-z0-9_.-]+$#', $value) === 1
        && !str_contains($value, '..') && is_file(__DIR__ . '/' . $value)
        ? $value : 'images/image.png';
}

// Exact DECIMAL(10,2) input; reject scientific notation and silent sub-cent rounding.
function product_price(string $value): ?string
{
    if (preg_match('/\A(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?\z/', $value) !== 1) return null;
    [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
    $fraction = str_pad($fraction, 2, '0');
    if ($whole === '0' && $fraction === '00') return null;
    return $whole . '.' . $fraction;
}
