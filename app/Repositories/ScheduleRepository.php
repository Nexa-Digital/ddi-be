<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class ScheduleRepository extends BaseRepository {

  public function __construct(Schedule $model)
  {
    parent::__construct($model);
  }

}