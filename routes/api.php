<?php

use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['throttle:60,1'])->group(function () {
    Route::resource('departments', DepartmentController::class);
    Route::apiResource('employees', EmployeeController::class);
});
