<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements RepositoryInterface
{
  protected Model $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function all(): Collection
  {
    return $this->model->all();
  }

  public function paginate(int $perPage = 10, array $filters = [], array $sorts = []): LengthAwarePaginator
  {
    $query = $this->model->query();

    // foreach ($filters as $filter) {
    //   if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
    //     if (is_array($filter['value'])) {
    //       if (strtoupper($filter['operator']) === 'IN') {
    //         $query->whereIn($filter['field'], $filter['value']);
    //       } elseif (strtoupper($filter['operator']) === 'NOT IN') {
    //         $query->whereNotIn($filter['field'], $filter['value']);
    //       }
    //     } else {
    //       if (strtoupper($filter['operator']) === 'OR') {
    //         $query->orWhere($filter['field'], $filter['value']);
    //       } else {
    //         $query->where($filter['field'], $filter['operator'], $filter['value']);
    //       }
    //     }
    //   }
    // }

    foreach ($filters as $filter) {
      if (isset($filter['field']) && isset($filter['operator']) && isset($filter['value'])) {
          $operator = strtoupper($filter['operator']);
          $field = $filter['field'];
          $value = $filter['value'];
  
          if (is_array($value)) {
              if ($operator === 'IN') {
                  $query->whereIn($field, $value);
              } elseif ($operator === 'NOT IN') {
                  $query->whereNotIn($field, $value);
              }
          } else {
              if ($operator === 'OR') {
                  $query->orWhere($field, $value); // asumsi operator OR di sini berarti OR EQUALS
              } elseif ($operator === 'LIKE' || $operator === 'NOT LIKE') {
                  $query->where($field, $operator, "%$value%");
              } else {
                  $query->where($field, $operator, $value);
              }
          }
      }
    }

    foreach ($sorts as $sort) {
      if (isset($sort['field'], $sort['direction'])) {
          $direction = strtolower($sort['direction']) === 'desc' ? 'desc' : 'asc';
          $query->orderBy($sort['field'], $direction);
      }
    } 

    return $query->paginate($perPage);
  }

  public function find(int $id): ?Model
  {
    return $this->model->find($id);
  }

  public function create(array $data): Model
  {
    return $this->model->create($data);
  }

  public function update(int $id, array $data): Model
  {
    $model = $this->find($id);
    if ($model) {
      $model->update($data);
      return $model;
    }
    throw new \Exception("Model not found");
  }

  public function delete(int $id): bool
  {
    $model = $this->find($id);
    if ($model) {
      return $model->delete();
    }
    return false;
  }
}
