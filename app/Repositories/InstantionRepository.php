<?php

namespace App\Repositories;

use App\Models\Instantion;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class InstantionRepository extends BaseRepository {

  public function __construct(Instantion $model)
  {
    parent::__construct($model);
  }

}