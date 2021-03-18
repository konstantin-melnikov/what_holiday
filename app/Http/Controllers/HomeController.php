<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HolidayService;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate(
                ['date' => 'required|date_format:Y-m-d']
            );
        }
        
        $date = $request->input('date', today()->format('Y-m-d'));
        try {
            $holidays = (new HolidayService)->getHolidays($date);
        } catch (\Exception $e) {
            info($e->getMessage());
            return view('home')
                ->with(['date' => $date, 'holidays' => []])
                ->withErrors(['Holidays storage not init']);
        }

        return view('home')->with(
            [
                'date'      => $date,
                'holidays'  => $holidays
            ]
        );
    }
}
