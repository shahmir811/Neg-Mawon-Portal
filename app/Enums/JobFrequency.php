<?php

namespace App\Enums;

enum JobFrequency: string
{
    case OneTime = 'one_time';
    case Weekly = 'weekly';
    case BiWeekly = 'bi_weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-Time',
            self::Weekly => 'Weekly',
            self::BiWeekly => 'Bi-Weekly',
            self::Monthly => 'Monthly',
        };
    }
}
