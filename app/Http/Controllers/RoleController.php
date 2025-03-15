<?php

namespace App\Http\Controllers;

use App\Repositories\RoleRepository;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $instantion = $this->repository->all();
        return response()->json($instantion);
    }

}
