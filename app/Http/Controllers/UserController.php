<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $filters = $request->query('filters', []);
        $users = $this->repository->paginate($perPage, $filters);

        return response()->json($users);
    }

    public function all()
    {
        $users = $this->repository->all();
        return response()->json($users);
    }

    public function show($id){
        $user = $this->repository->find($id);
        $user->roles;
        return response()->json($user);
    }

    public function store(Request $request){
        try {
            DB::beginTransaction();
            $data = $this->repository->create($request->except('role_id'));
            $data->roles()->sync($request->role_id);
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
