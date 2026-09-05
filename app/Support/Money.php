<?php

namespace App\Support;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Integer-cent money value. All financial math should go through this type.
 */
final class Money implements JsonSerializable, Stringable
{
    public function __construct(public readonly int $cents) {}

    public static function zero(): self
    {
        return new self(0);
    }

    public static function ofCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromDollars(int|float|string $dollars): self
    {
        if (is_string($dollars)) {
            $normalized = str_replace([',', ' ', '$'], '', trim($dollars));

            if ($normalized === '' || ! is_numeric($normalized)) {
                throw new InvalidArgumentException('Invalid dollar amount.');
            }

            $negative = str_starts_with($normalized, '-');
            $normalized = ltrim($normalized, '+-');

            if (! str_contains($normalized, '.')) {
                $cents = (int) $normalized * 100;
            } else {
                [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '0');
                $fraction = str_pad(substr($fraction, 0, 2), 2, '0');
                $cents = ((int) $whole * 100) + (int) $fraction;
            }

            return new self($negative ? -$cents : $cents);
        }

        if (is_int($dollars)) {
            return new self($dollars * 100);
        }

        return self::fromString(number_format($dollars, 2, '.', ''));
    }

    public static function fromString(string $dollars): self
    {
        return self::fromDollars($dollars);
    }

    /**
     * CRA half-up rounding to the nearest cent.
     */
    public static function fromRateProduct(int $cents, string $rate): self
    {
        $product = bcmul((string) $cents, $rate, 8);

        return new self(self::roundHalfUp($product));
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function multiply(string $rate): self
    {
        return self::fromRateProduct($this->cents, $rate);
    }

    public function divideBy(int $divisor): self
    {
        if ($divisor === 0) {
            throw new InvalidArgumentException('Cannot divide money by zero.');
        }

        $quotient = bcdiv((string) $this->cents, (string) $divisor, 8);

        return new self(self::roundHalfUp($quotient));
    }

    public function min(self $other): self
    {
        return $this->cents <= $other->cents ? $this : $other;
    }

    public function max(self $other): self
    {
        return $this->cents >= $other->cents ? $this : $other;
    }

    public function negated(): self
    {
        return new self(-$this->cents);
    }

    public function abs(): self
    {
        return new self(abs($this->cents));
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function lessThan(self $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function lessThanOrEqual(self $other): bool
    {
        return $this->cents <= $other->cents;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function dollars(): string
    {
        $negative = $this->cents < 0;
        $absolute = abs($this->cents);
        $whole = intdiv($absolute, 100);
        $fraction = $absolute % 100;

        return ($negative ? '-' : '').$whole.'.'.str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);
    }

    public function format(string $currency = 'CAD'): string
    {
        $negative = $this->cents < 0;
        $absolute = abs($this->cents);
        $whole = number_format(intdiv($absolute, 100), 0, '.', ',');
        $fraction = str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '').'$'.$whole.'.'.$fraction;
    }

    public function jsonSerialize(): string
    {
        return $this->dollars();
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private static function roundHalfUp(string $value): int
    {
        if (bccomp($value, '0', 8) >= 0) {
            return (int) bcadd($value, '0.5', 0);
        }

        return (int) bcsub($value, '0.5', 0);
    }
}
