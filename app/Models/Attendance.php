<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // public $with = ['schedule'];

    public function schedule(){
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
