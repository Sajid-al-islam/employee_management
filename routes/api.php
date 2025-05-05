<?php

use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::resource('departments', DepartmentController::class);

Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('employees', EmployeeController::class);
});
