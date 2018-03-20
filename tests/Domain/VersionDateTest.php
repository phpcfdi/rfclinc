<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Domain;

use PhpCfdi\RfcLinc\Domain\VersionDate;
use PHPUnit\Framework\TestCase;

class VersionDateTest extends TestCase
{
    public function testCreateWithValidData()
    {
        $date = new VersionDate(2015, 1, 13);
        $this->assertSame(2015, $date->year());
        $this->assertSame(1, $date->month());
        $this->assertSame(13, $date->day());
        $this->assertSame('2015-01-13', $date->format());
        $this->assertSame(1421107200, $date->timestamp());
    }

    public function testCreateFromString()
    {
        $date = VersionDate::createFromString('2015-01-13');
        $this->assertSame(2015, $date->year());
        $this->assertSame(1, $date->month());
        $this->assertSame(13, $date->day());
        // 1421107200 is 2015-01-13T00:00:00+00:00
        $this->assertSame(1421107200, $date->timestamp());
        $this->assertSame('2015-01-13', $date->format());
    }

    public function testCreateFromTimeStamp()
    {
        $date = VersionDate::createFromTimestamp(strtotime('2015-01-13'));
        $this->assertSame(2015, $date->year());
        $this->assertSame(1, $date->month());
        $this->assertSame(13, $date->day());
        // 1421107200 is 2015-01-13 UTC
        $this->assertSame(1421107200, $date->timestamp());
        $this->assertSame('2015-01-13', $date->format());
    }

    /**
     * @param string $string
     * @param int $timestamp
     * @param string $expected
     * @testWith ["2015-01-01", 1420070400, "2015-01-01"]
     *           ["2014-12-31T23:00:00-02:00", 1419984000, "2014-12-31"]
     *           ["2014-12-31T23:00:00+02:00", 1419984000, "2014-12-31"]
     */
    public function testCreateFromStringCases(string $string, int $timestamp, string $expected)
    {
        $date = VersionDate::createFromString($string);
        $this->assertSame($timestamp, $date->timestamp());
        $this->assertSame($expected, $date->format());

        $fromTs = VersionDate::createFromTimestamp($date->timestamp());
        $this->assertEquals($date->format(), $fromTs->format());
    }

    public function testDateIsValidWithBadArguments()
    {
        $year = 2018;
        $month = 2;
        $day = 31;
        $this->assertFalse(VersionDate::dateIsValid($year, $month, $day));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('year-month-day');
        new VersionDate($year, $month, $day);
    }
}
