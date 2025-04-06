<?php

namespace App\Http\Controllers;

use App\Repositories\ScheduleRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    protected ScheduleRepository $repository;

    public function __construct(ScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->query('filter', []);
        $sorts = $request->query('sorts', []);
        $instantion = $this->repository->paginate($perPage, $filters, $sorts);

        return response()->json($instantion);
    }

    public function all()
    {
        $instantion = $this->repository->all();
        return response()->json($instantion);
    }

    public function store(Request $request){

        DB::beginTransaction();
        
        try {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);

            $dayMap = [
                'senin' => 'monday',
                'selasa' => 'tuesday',
                'rabu' => 'wednesday',
                'kamis' => 'thursday',
                'jumat' => 'friday',
                'sabtu' => 'saturday',
                'minggu' => 'sunday',
            ];
            //code...
            $period = CarbonPeriod::create($start, $end);
            $insertData = [];

            $requestedDay = $dayMap[$request->day];
        
            foreach ($period as $date) {
                $dayName = strtolower($date->format('l')); // misal: monday, tuesday
                if ($requestedDay === $dayName) {
                    // Cek apakah data sudah ada
                    $exists = DB::table('schedules') // Ganti 'schedules' dengan nama tabel kamu
                        ->where('date', $date->format('Y-m-d'))
                        ->where('user_id', $request->user_id)
                        ->where('role_id', $request->role_id)
                        ->where('class_room_id', $request->class_room_id)
                        ->where('start', $request->start)
                        ->exists();

                    if (!$exists) {
                        $insertData[] = [
                            'date' => $date->format('Y-m-d'),
                            'start' => $request->start,
                            'finish' => $request->finish,
                            'amount' => $request->amount,
                            'role_id' => $request->role_id,
                            'class_room_id' => $request->class_room_id,
                            'user_id' => $request->user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            $this->repository->getModel()->insert($insertData);
            // return response()->json($insertData);
            // $this->repository->create($request->all());
            DB::commit();
            return response()->json(null, 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function update($id, Request $request){
        $this->repository->update($id, $request->all());
        return response()->json(null, 201);
    }

    public function delete($id){
        $this->repository->delete($id);
        return response()->json(null, 204);
    }
}
