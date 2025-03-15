<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class RoleRepository extends BaseRepository {

  public function __construct(Role $model)
  {
    parent::__construct($model);
  }

}