<?php

namespace App\Domain;

final class Money
{
    public static function percentage(int $minor, int $bps): int
    {
        if ($minor < 0 || $bps < 0 || $bps > 10000 || $minor > intdiv(PHP_INT_MAX - 5000, 10000)) {
            throw new \InvalidArgumentException('Invalid money or basis points.');
        }

        return intdiv($minor * $bps + 5000, 10000);
    }

    public static function format(int $minor, string $currency = 'USD'): string
    {
        return ($minor < 0 ? '-' : '').$currency.' '.intdiv(abs($minor), 100).'.'.str_pad((string) (abs($minor) % 100), 2, '0', STR_PAD_LEFT);
    }
}
