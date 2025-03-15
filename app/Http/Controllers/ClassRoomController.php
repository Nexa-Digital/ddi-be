<?php

namespace App\Http\Controllers;

use App\Repositories\ClassRoomRepository;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    protected ClassRoomRepository $repository;

    public function __construct(ClassRoomRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->query('filter', []);
        $instantion = $this->repository->paginate($perPage, $filters);

        return response()->json($instantion);
    }

    public function getByInstantion($instantion_id)
    {
        $classrooms = $this->repository->getModel()->where('instantion_id', $instantion_id)->get();
        return response()->json($classrooms);
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
