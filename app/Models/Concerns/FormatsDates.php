<?php

namespace App\Models\Concerns;

use DateTimeInterface;

trait FormatsDates
{
    /**
     * Prepare a date for array / JSON serialization.
     * Output format: 'YYYY-MM-DD' for date-only (00:00:00), or 'YYYY-MM-DD HH:mm:ss' for datetime.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($date->format('H:i:s') === '00:00:00' ? 'Y-m-d' : 'Y-m-d H:i:s');
    }
}
