<?php

namespace App\Http\Controllers;
use App\Models\TimeZone;
use App\Models\Time;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    //
    public function timeSlot(){
            $time = Time::first();
            $s_time = $time['date1']; //Booking date 
            $times = $time['time']; // Booking time
            $date_9 = Carbon::parse($time['9_date']);
            $date_12 = Carbon::parse($time['12_date']);
            $date_3 = Carbon::parse($time['3_date']);
            $date_6 = Carbon::parse($time['6_date']);
            $combileDateTime  = date('Y-m-d H:i:s', strtotime("$s_time $times"));
            $start_time = carbon::parse($combileDateTime);
            
            if($start_time->isToday()){
                if($start_time->toTimeString() >= $date_9->toTimeString() && $start_time->toTimeString() <= $date_12->toTimeString()){
                    $time_zone = Time_Zone::whereNotIn('id', ['1'])->get();
                    return $time_zone;
                } else if($start_time->toTimeString() <= $date_9->toTimeString()){
                    $time_zone = Time_Zone::whereNotIn('id', ['1'])->get();
                    return $time_zone;
                } else if($start_time->toTimeString() >= $date_12->toTimeString() && $start_time->toTimeString() <= $date_3->toTimeString()){
                    $time_zone = Time_Zone::whereNotIn('id', ['1', '2'])->get();
                    return $time_zone;
                }else if($start_time->toTimeString() >= $date_3->toTimeString() && $start_time->toTimeString() <= $date_6->toTimeString()){
                    $time_zone = Time_Zone::whereNotIn('id', ['1', '2', '3'])->get();
                    return $time_zone;
                }else{
                    return 'Today time slot not available.Please choose the next day';
                }
            }else{
                $time_zone = TimeZone::get();
                return $time_zone;
            }
      }
}
