<?php

namespace App\Http\Controllers;

use App\Repositories\ScheduleRepository;
use Illuminate\Http\Request;

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
        $this->repository->create($request->all());
        return response()->json(null, 201);
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
