<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request){
        $user = $request->user();

        $schedules = Schedule::whereDate('date', Carbon::now())
        ->whereTime('start', '>',Carbon::now()->format('H:i:s'))
        ->whereTime('finish', '<',Carbon::now()->format('H:i:s'))
        ->where('user_id', $user->id)
        ->get();

        return response()->json($schedules);
    }

    public function current(Request $request){
        $user = $request->user();

        $now = Carbon::now();

        Log::info($now->format('H:i:s'));
        Log::info($now->toDateString());


        $schedule = Schedule::whereDate('date', $now->toDateString())
        ->whereTime('start', '<=',$now->format('H:i:s'))
        ->whereTime('finish', '>=',$now->format('H:i:s'))
        ->where('user_id', $user->id)
        ->first();

        return response()->json($schedule);
    }

    public function store(Request $request){

        $image = explode('base64,', $request->image);
        $image = base64_decode($image[1]);

        if(!Storage::directoryExists('attendance')){
            Storage::createDirectory('attendance');
        }

        $now = Carbon::now();

        $imageName = '/attendance/' . Str::slug($request->user()->name) . '/' . $now->format('d-m-Y--h:i').'.jpeg';

        Storage::disk('public')->put($imageName, $image);

        $attendance = Attendance::create([
            'schedule_id' => $request->schedule_id,
            'location' => $request->location,
            'image' => $imageName,
            'lesson' => $request->lesson ?? ''
        ]);

        return response()->json($attendance);

    }

    public function getTodayHistory(Request $request){
        $user = $request->user();

        $attendance = Attendance::whereHas('schedule', function($q) use ($user){
            $q->where('user_id', $user->id);
        })
        ->with('schedule')
        ->whereDate('created_at', Carbon::now())
        ->latest()
        ->limit(2)
        ->get();

        return response()->json($attendance);

    }

    public function weeklySchedule(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        
        Log::info('Week range: ' . $startOfWeek->toDateString() . ' to ' . $endOfWeek->toDateString());
        
        $schedules = Attendance::whereHas('schedule', function($q) use ($user, $startOfWeek, $endOfWeek){
            $q->where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfWeek->toDateString(), 
                $endOfWeek->toDateString()
            ]);
        })
        ->with('schedule')
        ->latest()
        ->get();
        
        return response()->json($schedules);
    }

    
}
