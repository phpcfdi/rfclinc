<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Domain;

class VersionDate
{
    private $timestamp;

    /** @var string[] */
    private $ymd;

    public function __construct(int $year, int $month, int $day)
    {
        if (! $this->dateIsValid($year, $month, $day)) {
            throw new \InvalidArgumentException('The combination of year-month-day is not valid');
        }
        $this->ymd = [
            str_pad((string) $year, 4, '0', STR_PAD_LEFT),
            str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            str_pad((string) $day, 2, '0', STR_PAD_LEFT),
        ];
        $this->timestamp = (int) strtotime($this->format() . 'T00:00:00+00:00');
    }

    /**
     * Create a VersionDate based only on the date part information given.
     * It will not consider any information about time or timezone.
     * You can use terms like 'today'.
     *
     * @param string $date
     * @return VersionDate
     */
    public static function createFromString(string $date): self
    {
        $dt = new \DateTime($date);
        return new self((int) $dt->format('Y'), (int) $dt->format('m'), (int) $dt->format('d'));
    }

    /**
     * Create a VersionDate based on the timestamp.
     * The timestamp is considered the seconds since 1970-01-01
     * Only the date part is taken, this function consider the timestamp as UTC
     *
     * @param int $timestamp
     * @return VersionDate
     */
    public static function createFromTimestamp(int $timestamp): self
    {
        return static::createFromString(gmdate('Y-m-d', $timestamp));
    }

    public static function dateIsValid(int $year, int $month, int $day)
    {
        return checkdate($month, $day, $year);
    }

    public function format(string $separator = '-'): string
    {
        return implode($separator, $this->ymd);
    }

    public function timestamp(): int
    {
        return $this->timestamp;
    }

    public function year(): int
    {
        return (int) $this->ymd[0];
    }

    public function month(): int
    {
        return (int) $this->ymd[1];
    }

    public function day(): int
    {
        return (int) $this->ymd[2];
    }

    public function compare(self $to): int
    {
        return $this->timestamp() <=> $to->timestamp();
    }
}
