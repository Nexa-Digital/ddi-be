<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class AdminRepository extends BaseRepository {

  public function __construct(Admin $model)
  {
    parent::__construct($model);
  }

}