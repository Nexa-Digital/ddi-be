<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class UserRepository extends BaseRepository {

  public function __construct(User $model)
  {
    parent::__construct($model);
  }

}