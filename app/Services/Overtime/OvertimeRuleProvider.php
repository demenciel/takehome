<?php

namespace App\Services\Overtime;

use App\Support\Province;
use InvalidArgumentException;

class OvertimeRuleProvider
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $data = null;

    /**
     * @return array<string, mixed>
     */
    public function dataset(): array
    {
        return $this->data ??= require resource_path('employment/overtime.php');
    }

    /**
     * @return array<string, mixed>
     */
    public function for(Province $province): array
    {
        $rules = $this->dataset()['jurisdictions'][$province->code()] ?? null;

        if (! is_array($rules)) {
            throw new InvalidArgumentException("No overtime rules for {$province->code()}.");
        }

        return $rules;
    }

    public function retrievedDate(): string
    {
        return (string) $this->dataset()['retrieved_date'];
    }

    public function disclaimer(): string
    {
        return (string) $this->dataset()['disclaimer'];
    }
}
