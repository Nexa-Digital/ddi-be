<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
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

        Configuration::instance('cloudinary://829686817882984:rKs9-GIUkoJNmOmlnOXSle5qT4c@davkbdap6');

        $upload = new UploadApi();

        $file = $request->file('image');

        $image = $upload->upload($file->getRealPath(),[
            'folder' => 'ddi-app/attendance',
            'resource_type' => 'auto'
        ]);

        $attendance = Attendance::create([
            'schedule_id' => $request->schedule_id,
            'location' => $request->location,
            'image' => $image['url'],
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
