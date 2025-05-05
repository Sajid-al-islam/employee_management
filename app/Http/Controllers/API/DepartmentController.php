<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Throwable;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            $departments = Department::select('id','name')->get();

            return response()->json([
                'success' => true,
                'data'    => $departments,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load departments.',
            ], 500);
        }
    }
}
