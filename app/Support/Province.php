<?php

namespace App\Support;

enum Province: string
{
    case Alberta = 'AB';
    case BritishColumbia = 'BC';
    case Manitoba = 'MB';
    case NewBrunswick = 'NB';
    case NewfoundlandAndLabrador = 'NL';
    case NovaScotia = 'NS';
    case Ontario = 'ON';
    case PrinceEdwardIsland = 'PE';
    case Quebec = 'QC';
    case Saskatchewan = 'SK';
    case NorthwestTerritories = 'NT';
    case Nunavut = 'NU';
    case Yukon = 'YT';

    public function code(): string
    {
        return $this->value;
    }

    public function name(): string
    {
        return __('provinces.'.$this->value.'.name');
    }

    public function slug(): string
    {
        return match ($this) {
            self::Alberta => 'alberta',
            self::BritishColumbia => 'british-columbia',
            self::Manitoba => 'manitoba',
            self::NewBrunswick => 'new-brunswick',
            self::NewfoundlandAndLabrador => 'newfoundland-and-labrador',
            self::NovaScotia => 'nova-scotia',
            self::Ontario => 'ontario',
            self::PrinceEdwardIsland => 'prince-edward-island',
            self::Quebec => 'quebec',
            self::Saskatchewan => 'saskatchewan',
            self::NorthwestTerritories => 'northwest-territories',
            self::Nunavut => 'nunavut',
            self::Yukon => 'yukon',
        };
    }

    public function usesQpp(): bool
    {
        return $this === self::Quebec;
    }

    public function adjective(): string
    {
        return __('provinces.'.$this->value.'.adjective');
    }

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $province) {
            if ($province->slug() === $slug) {
                return $province;
            }
        }

        return null;
    }

    public static function fromCode(string $code): ?self
    {
        return self::tryFrom(strtoupper($code));
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
