<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LodgingHelperTest extends CIUnitTestCase
{
    public function testHourDurationOptionsMixHourlyAndDailyRanges(): void
    {
        $options = hour_duration_options();

        $this->assertSame('1hr', $options['1']);
        $this->assertSame('23hrs', $options['23']);
        $this->assertSame('1day', $options['24']);
        $this->assertSame('2days', $options['48']);
        $this->assertSame('31days', $options['744']);
        $this->assertArrayNotHasKey('25', $options);
    }

    public function testHourDurationLabelUsesDailyLabelsForFullDays(): void
    {
        $this->assertSame('1hr', hour_duration_label(1));
        $this->assertSame('1day', hour_duration_label(24));
        $this->assertSame('1day 1hr', hour_duration_label(25));
        $this->assertSame('1day 12hrs', hour_duration_label(36));
        $this->assertSame('7days', hour_duration_label(168));
        $this->assertSame('31days', hour_duration_label(744));
    }

    public function testPricingDurationHelpersSplitAndRebuildStoredHours(): void
    {
        $this->assertSame(['days' => '', 'hours' => '1'], pricing_duration_day_hour_parts(1));
        $this->assertSame(['days' => '', 'hours' => '23'], pricing_duration_day_hour_parts(23));
        $this->assertSame(['days' => '1', 'hours' => ''], pricing_duration_day_hour_parts(24));
        $this->assertSame(['days' => '1', 'hours' => '1'], pricing_duration_day_hour_parts(25));
        $this->assertSame(['days' => '31', 'hours' => ''], pricing_duration_day_hour_parts(744));
        $this->assertSame(6, pricing_day_hour_to_hours('', 6));
        $this->assertSame(30, pricing_day_hour_to_hours(1, 6));
        $this->assertSame(72, pricing_day_hour_to_hours(3, ''));
    }
}
