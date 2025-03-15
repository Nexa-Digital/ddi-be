<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{

    public $with = ['instantion'];

    public function instantion(){
        return $this->belongsTo(Instantion::class);
    }
}
