<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * This path is not in the test task, just interested for me. I do that simply.
 * So I'm not sure can this code work in future.
 */
class Holiday extends Command
{
    const HOLIDAYS_URL = 'https://uk.wikipedia.org/wiki/%D0%A1%D0%B2%D1%8F%D1%82%D0%B0_%D1%82%D0%B0_%D0%BF%D0%B0%D0%BC%27%D1%8F%D1%82%D0%BD%D1%96_%D0%B4%D0%BD%D1%96_%D0%B2_%D0%A3%D0%BA%D1%80%D0%B0%D1%97%D0%BD%D1%96';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:initStorage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init holidays storage from Wikipedia';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $holidays = [];
        // hardcode for #text nodes
        $headers = [1 => 'date', 3 => 'title', 5 => 'description'];
        try {
            $page = Http::retry(5, 1000)->get(static::HOLIDAYS_URL)->body();
        } catch (\Exception $e) {
            $this->error(
                'Get page falied for url ' . urldecode(static::HOLIDAYS_URL)
            );
            return false;
        }
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHtml($page);
        $xpath = new \DOMXpath($dom);
        $tables = $xpath->query("//table[contains(@class,'wikitable')]");
        // we have only 1 table for now
        if ($tables->length !== 1) {
            $this->error('Found 0 or more 1 tables');
            return false;
        }
        $content = $tables->item(0);
        $content = $content->getElementsByTagName('tbody')->item(0);

        $freeDayAttr = '#FFE4E1';
        $currentMonth = false;

        foreach ($content->childNodes as $tr) {
            if ($tr->nodeName == '#text') {
                continue;
            }
            $isFreeDay = ($tr->getAttribute('bgcolor') == $freeDayAttr) ? true : false;
            // use 2 because we have #text node
            if ($tr->childNodes->length == 2) {
                $links = $tr->getElementsByTagName('a');
                if ($links->length) {
                    $currentMonth = $links->item(0)->textContent;
                }
            } elseif ($currentMonth) {
                // get holiday info
                $holidayRaw = [];
                $holiday = [];
                foreach ($tr->childNodes as $index => $td) {
                    if ($td->nodeName == '#text') {
                        continue;
                    }
                    $holidayRaw[$headers[$index]] = trim($td->textContent);
                }
                $strDate = $this->_fixChars($holidayRaw['date']);
                $holiday['day'] = null;
                $holiday['is_calculable'] = false;
                $holiday['angoritm'] = null;
                $holiday['week_of_month'] = null;
                $holiday['day_of_week'] = null;
                $holiday['month'] = null;
                try {
                    $date = Carbon::createFromLocaleIsoFormat('D MMMM', 'uk', $strDate);
                    $holiday['day'] = (int) $date->isoFormat('D');
                    $holiday['month'] = (int) $date->isoFormat('M');
                } catch (\Exception $e) {
                    $calculable = [
                        'Масниця' => 'maslenitsa',
                        'Великдень або Пасха' => 'easter',
                        'Трійця (П\'ятидесятниця)' => 'trinitas'
                    ];
                    if (isset(($calculable[$holidayRaw['title']]))) {
                        $holiday['month'] = null;
                        $holiday['is_calculable'] = true;
                        $holiday['angoritm'] = $calculable[$holidayRaw['title']];
                        $holiday['week_of_month'] = null;
                        $holiday['day_of_week'] = null;
                    } else {
                        $items = explode(' ', mb_strtolower($strDate));
                        $holiday['month'] = $this->_decodeMonth($items[2]);
                        $holiday['is_calculable'] = false;
                        $holiday['angoritm'] = null;
                        $holiday['week_of_month'] = $this->_decodeWeek($items[0]);
                        $holiday['day_of_week'] = $this->_decodeDay($items[1]);
                    }
                }
                $holiday['is_free_day'] = $isFreeDay;
                $holiday['title'] = $holidayRaw['title'];
                $holiday['date_raw'] = $holidayRaw['date'];
                $holiday['description'] = $holidayRaw['description'];
                $holidays[] = $holiday;
            }
        }
        Storage::disk('local')->put('holidays.json', json_encode($holidays, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Holidays storage was update with ' . count($holidays) . ' holidays');
    }

    private function _decodeWeek($weekIndex)
    {
        $matrix = [
            'друга' => 2,
            'третя' => 3,
            'перша' => 1,
            'остання' => -1,
            'четверта' => 4
        ];
        return isset($matrix[$weekIndex]) ? $matrix[$weekIndex] : null;
    }

    private function _decodeDay($day)
    {
        $date = Carbon::parseFromLocale($day, 'uk');
        return (int) $date->isoFormat('d');
    }

    private function _decodeMonth($month)
    {
        $date = Carbon::parseFromLocale($month, 'uk');
        return (int) $date->isoFormat('M');
    }

    /**
     * Convert en chars to uk chars
     *
     * @param string $str The string
     * 
     * @return string Fixed string
     */
    private function _fixChars($str)
    {
        $en = ['i', 'c', chr(194) . chr(160)];
        $uk = ['і', 'с', ' '];
        return str_replace($en, $uk, $str);
    }
}
