<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController as ControllersAttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\InstantionController;
use App\Http\Controllers\Mobile\AttendanceController;
use App\Http\Controllers\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::group(['prefix' => 'instantion'], function(){
        Route::get('/', [InstantionController::class, 'index']);
        Route::get('/all', [InstantionController::class, 'all']);
        Route::post('/', [InstantionController::class, 'store']);
        Route::put('/{id}', [InstantionController::class, 'update']);
        Route::delete('/{id}', [InstantionController::class, 'delete']);
    });
    
    Route::group(['prefix' => 'class-room'], function(){
        Route::get('/', [ClassRoomController::class, 'index']);
        Route::get('/instantion/{instantion_id}', [ClassRoomController::class, 'getByInstantion']);
        Route::post('/', [ClassRoomController::class, 'store']);
        Route::put('/{id}', [ClassRoomController::class, 'update']);
        Route::delete('/{id}', [ClassRoomController::class, 'delete']);
    });
    
    Route::group(['prefix' => 'admin'], function(){
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'delete']);
    });
    
    Route::group(['prefix' => 'user'], function(){
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
    
    Route::group(['prefix' => 'schedule'], function(){
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/{id}', [ScheduleController::class, 'show']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::put('/{id}', [ScheduleController::class, 'update']);
        Route::delete('/{id}', [ScheduleController::class, 'delete']);
    });
    
    Route::group(['prefix' => 'attendance'], function(){
        Route::get('/history/{userid}', [ControllersAttendanceController::class, 'getHistory']);
        Route::post('/recap/{userid}', [ControllersAttendanceController::class, 'getUserRecap']);
        Route::post('/recap-all', [ControllersAttendanceController::class, 'getRecapAll']);
    });
    
    Route::group(['prefix' => 'role'], function(){
        Route::get('/', [RoleController::class, 'index']);
    });
});



Route::group(['prefix' => 'mobile'], function(){

    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::group(['middleware' => 'auth:sanctum'], function(){

        Route::group(['prefix' => 'attendance'], function(){
            Route::get('/', [AttendanceController::class, 'index']);
            Route::get('/current', [AttendanceController::class, 'current']);
            Route::get('/history/today', [AttendanceController::class, 'getTodayHistory']);
            Route::get('/history/week', [AttendanceController::class, 'weeklySchedule']);
            Route::post('/', [AttendanceController::class, 'store']);
        });

    });
});
