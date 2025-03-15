<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    public $with = ['classRoom.instantion', 'attendance', 'user', 'role'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function classRoom(){
        return $this->belongsTo(ClassRoom::class);
    }

    public function attendance(){
        return $this->hasOne(Attendance::class);
    }
}
