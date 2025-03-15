<?php

namespace App\Repositories;

use App\Models\ClassRoom;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class ClassRoomRepository extends BaseRepository {

  public function __construct(ClassRoom $model)
  {
    parent::__construct($model);
  }

}