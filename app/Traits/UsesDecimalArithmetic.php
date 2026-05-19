<?php

namespace App\Traits;

trait UsesDecimalArithmetic
{
    protected int $bcmathScale = 8;

    public function addBalance(string $amount): void
    {
        $this->balance = bcadd($this->balance, $amount, $this->bcmathScale);
        $this->save();
    }

    public function subtractBalance(string $amount): void
    {
        if (bccomp($this->balance, $amount, $this->bcmathScale) < 0) {
            throw new \InvalidArgumentException('Insufficient balance');
        }

        $this->balance = bcsub($this->balance, $amount, $this->bcmathScale);
        $this->save();
    }

    public function hasBalance(string $amount): bool
    {
        return bccomp($this->balance, $amount, $this->bcmathScale) >= 0;
    }

    public static function bcAdd(string $a, string $b, int $scale = 8): string
    {
        return bcadd($a, $b, $scale);
    }

    public static function bcSub(string $a, string $b, int $scale = 8): string
    {
        return bcsub($a, $b, $scale);
    }

    public static function bcMul(string $a, string $b, int $scale = 8): string
    {
        return bcmul($a, $b, $scale);
    }

    public static function bcDiv(string $a, string $b, int $scale = 8): string
    {
        return bcdiv($a, $b, $scale);
    }
}
