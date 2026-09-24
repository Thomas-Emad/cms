<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Offer;
use Carbon\Carbon;
use Tests\TestCase;

class DateFormattingTest extends TestCase
{
    public function test_model_date_serialization_formats_as_date_or_datetime(): void
    {
        $offer = new Offer([
            'valid_from' => '2026-09-25',
            'valid_until' => '2026-11-25',
        ]);
        $offer->created_at = Carbon::parse('2026-09-25 10:00:00');

        $serialized = $offer->toArray();

        $this->assertSame('2026-09-25', $serialized['valid_from']);
        $this->assertSame('2026-11-25', $serialized['valid_until']);
        $this->assertSame('2026-09-25 10:00:00', $serialized['created_at']);
    }

    public function test_event_dates_serialize_cleanly(): void
    {
        $event = new Event([
            'start_date' => '2026-09-27',
            'end_date' => '2026-09-28',
        ]);
        $event->created_at = Carbon::parse('2026-09-25 14:30:00');

        $serialized = $event->toArray();

        $this->assertSame('2026-09-27', $serialized['start_date']);
        $this->assertSame('2026-09-28', $serialized['end_date']);
        $this->assertSame('2026-09-25 14:30:00', $serialized['created_at']);
    }

    public function test_carbon_json_serialization_matches_specification(): void
    {
        $dateOnly = Carbon::parse('2026-09-25 00:00:00');
        $dateTime = Carbon::parse('2026-09-25 10:00:00');

        $this->assertSame('"2026-09-25"', json_encode($dateOnly));
        $this->assertSame('"2026-09-25 10:00:00"', json_encode($dateTime));
    }
}
