<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function getHistory($userid, Request $request){

        $perPage = $request->query('per_page', 10);
        $filters = $request->query('filter', []);

        $attendances = Attendance::whereHas('schedule', function($q) use ($userid, $filters) {
            $q->where('user_id', $userid);

            foreach ($filters as $filter) {
                if($filter['field'] == 'role_id'){
                    if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
                        $q->where($filter['field'], $filter['operator'], $filter['value']);
                    }
                }

                if($filter['field'] == 'instantion_id'){
                    if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
                        $q->whereHas('classRoom.instantion', function ($n) use ($filter) {
                            $n->where('id', $filter['operator'], $filter['value']);
                        });
                    }
                }

            }
        })
        ->with('schedule');

        
        foreach ($filters as $filter) {

            if($filter['field'] != 'role_id' && $filter['field'] != 'instantion_id'){
                if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
                    if (is_array($filter['value'])) {
                        if (strtoupper($filter['operator']) === 'IN') {
                            $attendances->whereIn($filter['field'], $filter['value']);
                        } elseif (strtoupper($filter['operator']) === 'NOT IN') {
                            $attendances->whereNotIn($filter['field'], $filter['value']);
                        }
                    } else {
                        if (strtoupper($filter['operator']) === 'OR') {
                            $attendances->orWhere($filter['field'], $filter['value']);
                        } else {
                            $attendances->where($filter['field'], $filter['operator'], $filter['value']);
                        }
                    }
                }
            }
        }


        return response()->json($attendances->paginate($perPage));

    }

    public function getUserRecap($userid,Request $request){

        // $recap = Schedule::whereHas('classRoom.instantion', function ($q) use ($request) {
        //     $q->where('id', $request->instantionid);
        // })
        // ->where('user_id', $userid)
        // ->where('date', '>=', $request->start)
        // ->where('date', '<=', $request->end)
        // ->get();

        $recap = Schedule::when($request->role_id == 1, function($q) use ($request) {
            $q->whereHas('classRoom.instantion', function ($q) use ($request) {
                $q->where('id', $request->instantion_id);
            });
        })
        ->where('role_id', $request->role_id)
        ->where('user_id', $userid)
        ->whereDate('date', '>=', $request->start)
        ->whereDate('date', '<=', $request->end)
        ->get();

        $grouped = $recap
        ->map(function ($e) {

            $start = Carbon::parse($e->start);
            $end = Carbon::parse($e->finish);
            if($e->attendance){
                $checkin = Carbon::parse($e->attendance->created_at);
            }

            $instantion = '-';

            if($e->classRoom?->instantion?->name){
                $instantion = $e->classRoom->instantion->name . ' ' . $e->classRoom->name;
            }

            return [
                'date' => $e->date,
                'class' => $instantion,
                'start' => $start->translatedFormat('H:i'),
                'finish' => $end->translatedFormat('H:i'),
                'duration' => $start->diffInHours($end),
                'check_in' => $e->attendance ? $checkin->translatedFormat('H:i') : null,
                'attendance' => $e->attendance,
            ];
        })
        ->groupBy('date')
        ->map(fn ($e, $date) =>[
            'date' => $date,
            'detail' => $e->values()->all(),
            'hour' => $e->filter(fn ($item) => $item['check_in'] !== null)
            ->sum(fn ($item) => $item['duration'] ?? 0)
        ])
        ->values()
        ->all();

        return response()->json($grouped);

    }

    public function getRecapAll(Request $request){

        Log::info($request->all());

        $recap = Schedule::when($request->role_id == 1, function($q) use ($request) {
            $q->whereHas('classRoom.instantion', function ($q) use ($request) {
                $q->where('id', $request->instantion_id);
            });
        })
        ->where('role_id', $request->role_id)
        ->whereDate('date', '>=', $request->start)
        ->whereDate('date', '<=', $request->finish)
        ->get();

        // $recap = Schedule::all();

        $grouped = $recap
        ->map(function ($e) {

            $start = Carbon::parse($e->start);
            $end = Carbon::parse($e->finish);
            if($e->attendance){
                $checkin = Carbon::parse($e->attendance->created_at);
            }

            $instantion = '-';

            if($e->classRoom?->instantion?->name){
                $instantion = $e->classRoom->instantion->name . ' ' . $e->classRoom->name;
            }

            return [
                'user' => $e->user->name,
                'date' => $e->date,
                'class' => $instantion,
                'start' => $start->translatedFormat('H:i'),
                'finish' => $end->translatedFormat('H:i'),
                'duration' => $start->diffInHours($end),
                'check_in' => $e->attendance ? $checkin->translatedFormat('H:i') : null,
                'attendance' => $e->attendance,
            ];
        })
        ->groupBy('user')
        ->map(fn ($e, $name) =>[
            'name' => $name,
            'total_hour' => $e->sum(fn($n) => $n['duration'] ?? 0),
            'paid_hour' => $e->filter(fn($n) => $n['check_in'] !== null)->sum(fn($n) => $n['duration'] ?? 0),
            'date' => $e->values()->groupBy('date')->map(fn($n, $date) => [
                'date'  => $date,
                'hour' => $n->filter(fn($n) => $n['check_in'] !== null)->sum(fn($n) => $n['duration'] ?? 0),
                'detail' => $n->values()->all(),
            ])->values()->all(),
            // 'detail' => $e->values()->all(),
            // 'hour' => $e->filter(fn ($item) => $item['check_in'] !== null)
            // ->sum(fn ($item) => $item['duration'] ?? 0)
        ])
        ->values()
        ->all();

        return response()->json($grouped);
        
    }

}
