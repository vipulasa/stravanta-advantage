<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ServiceInterest: string implements HasLabel
{
    case BusinessAdvantageScan = 'business-advantage-scan';
    case PerformanceAccelerator = 'performance-accelerator';
    case ExecutivePartner = 'executive-partner';

    /**
     * Get the human readable label for the engagement.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::BusinessAdvantageScan => 'Business Advantage Scan',
            self::PerformanceAccelerator => 'Performance Accelerator',
            self::ExecutivePartner => 'Executive Partner',
        };
    }

    /**
     * Get the engagements as value/label pairs for a front end select element.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => ['value' => $case->value, 'label' => $case->getLabel()],
            self::cases(),
        );
    }
}
