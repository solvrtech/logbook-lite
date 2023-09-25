<?php

namespace App\Common;

use DateTime;
use DateTimeImmutable;

class DateTimeHelper
{
    public const FULL_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    /**
     * Format string of datetime to datetime result.
     *
     * @param string $dateTime The string of datetime
     *
     * @return DateTime
     */
    public function strToDateTime(string $dateTime): DateTime
    {
        return (new DateTime())->setTimestamp(strtotime($dateTime));
    }

    /**
     * Format dateTime to string of datetime.
     * Return datetime if withTime is true otherwise return date only
     *
     * @param DateTime|DateTimeImmutable|null $dateTime
     * @param bool $dateOnly
     * @param bool $timeOnly
     * @param bool $tz
     * @return string
     */
    public function dateTimeToStr(
        DateTime|DateTimeImmutable|null $dateTime = null,
        bool $dateOnly = false,
        bool $timeOnly = false,
        bool $tz = false
    ):
    string {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        $timezone = $dateTime->getTimezone()->getName();

        if ($dateOnly) {
            $string = $dateTime->format(self::DATE_FORMAT);

            return $tz ? "{$string} ({$timezone})" : $string;
        }

        if ($timeOnly) {
            $string = $dateTime->format(self::TIME_FORMAT);

            return $tz ? "{$string} ({$timezone})" : $string;
        }

        $string = $dateTime->format(self::FULL_FORMAT);

        return $tz ? "{$string} ({$timezone})" : $string;
    }

    /**
     * Parse a string into a new DateTime object according to the specified format.
     *
     * @param string $strDateTime
     * @param bool $dateOnly
     * @param bool $timeOnly
     *
     * @return DateTime|false
     */
    public function dateTimeFromFormat(
        string $strDateTime,
        bool $dateOnly = false,
        bool $timeOnly = false,
    ): DateTime|false {
        if ($dateOnly) {
            return DateTime::createFromFormat(
                self::DATE_FORMAT,
                $strDateTime
            );
        }

        if ($timeOnly) {
            return DateTime::createFromFormat(
                self::TIME_FORMAT,
                $strDateTime
            );
        }

        return DateTime::createFromFormat(
            self::FULL_FORMAT,
            $strDateTime
        );
    }
}
