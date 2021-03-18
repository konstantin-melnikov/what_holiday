<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
/**
 * Get holiday by some date.
 */
class HolidayService
{
    const MONDAY = 1;
    /**
     * Illuminate\Support\Collection
     */
    protected $holidays;

    public function __construct()
    {
        $this->holidays = $this->load();
    }

    /**
     * Load holidays from storage
     *
     * @throws \Exception  Parse holidays json failed
     *
     * @return Collection  Holidays collection
     */
    public function load()
    {
        $json = Storage::disk('local')->get('holidays.json');
        $holidays = json_decode($json, true);
        if (empty($holidays)) {
            throw new \Exception("Parse holidays json failed", 0);
        }
        return collect($holidays);
    }

    /**
     * Gets the holidays.
     *
     * @todo Add filter for calculable holidays Like Pascha
     * 
     * @param      string  $date   The date
     *
     * @return     array  The holidays.
     */
    public function getHolidays($date)
    {
        $date = Carbon::parse($date);
        $filters = [
            'fixDay' => $date->format('j n'),
            'floatingDay' => sprintf(
                '%s %s %s', $date->format('n'), $this->_weekOfMonth($date), $date->dayOfWeek
            )
        ];
        if (static::MONDAY === (int) $date->dayOfWeek) {
            $filters['needCheckFreeDay'] = true;
            $subDate = clone $date;
            $subDate->subDay();
            $filters['fixSatDay'] = $subDate->format('j n');
            $filters['floatingSatDay'] = sprintf(
                '%s %s %s', $subDate->format('n'), $this->_weekOfMonth($subDate), $subDate->dayOfWeek
            );
            $subDate->subDay();
            $filters['fixSunDay'] = $subDate->format('j n');
            $filters['floatingSunDay'] = sprintf(
                '%s %s %s', $subDate->format('n'), $this->_weekOfMonth($subDate), $subDate->dayOfWeek
            );
        } else {
            $filters['needCheckFreeDay'] = false;
        }

        $holidays = $this->holidays->filter(function ($holiday) use ($filters) {
            $fixDay = sprintf('%s %s', $holiday['day'], $holiday['month']);
            if ($fixDay === $filters['fixDay']) {
                //Found holiday by fix date
                return true;
            }
            $floatingDay = sprintf(
                '%s %s %s',
                $holiday['month'],
                $holiday['week_of_month'],
                $holiday['day_of_week']
            );
            if ($floatingDay === $filters['floatingDay']) {
                //Found holiday by week of month and day of week
                return true;
            }
            if ($filters['needCheckFreeDay'] && $holiday['is_free_day']) {
                //Check free days
                if ($fixDay === $filters['fixSatDay'] || $fixDay === $filters['fixSunDay']) {
                    //Found freeDay by fix date
                    return true;
                }
                if ($floatingDay === $filters['floatingSatDay'] || $floatingDay === $filters['floatingSunDay']) {
                    //Found freeDay by week of month and day of week
                    return true;
                }
            }

            return false;
        });

        return $holidays->all();
    }

    /**
     * Get number week of month
     *
     * @param Carbon $date The date
     *
     * @return integer  Number of week or -1 for last week
     */
    private function _weekOfMonth($date)
    {
        /**
         * @todo Have February issue when last week of the month can be 4 week of the month.
         * We don't check it because not have holidays with this rule.
         */
        $date = clone $date;
        $weekOfMonth = $date->weekOfMonth;
        $lastweekOfMonth = $date->endOfMonth()->weekOfMonth;
        return ($weekOfMonth === $lastweekOfMonth) ? -1 : (int) $weekOfMonth;
    }
}