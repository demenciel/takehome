<?php

namespace App\Support;

enum PayFrequency: string
{
    case Annual = 'annual';
    case Monthly = 'monthly';
    case Semimonthly = 'semimonthly';
    case Biweekly = 'biweekly';
    case Weekly = 'weekly';

    public function periods(): int
    {
        return match ($this) {
            self::Annual => 1,
            self::Monthly => 12,
            self::Semimonthly => 24,
            self::Biweekly => 26,
            self::Weekly => 52,
        };
    }

    public function label(): string
    {
        return __('calculator.frequencies.'.$this->value);
    }

    public function adverb(): string
    {
        return __('calculator.frequency_adverbs.'.$this->value);
    }

    /**
     * @return list<self>
     */
    public static function supported(): array
    {
        return self::cases();
    }
}
