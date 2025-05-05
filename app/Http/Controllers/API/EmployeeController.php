<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;


class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with('department')->latest()->paginate(25);

        return $this->sendResponse(EmployeeResource::collection($employees), 'Employees retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email',
                'department_id' => 'required|exists:departments,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $employee = DB::transaction(function () use ($input) {
                return Employee::create($input);
            });

            return $this->sendResponse(new EmployeeResource($employee), 'Employee created successfully.', 201);
        } catch (Throwable $e) {
            info('EMPLOYEE_STORE_ERROR', ['error' => $e->getMessage()]);
            return $this->errorResponse($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        try {
            $employee->load('department');

            if (is_null($employee)) {
                return $this->sendError('Employee not found.');
            }

            return $this->sendResponse(new EmployeeResource($employee), 'Employee retrieved successfully.');

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:employees,email,' . $employee->id,
                'department_id' => 'sometimes|required|exists:departments,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            DB::transaction(function () use ($input, $employee) {
                $employee->update($input);
            });

            return $this->sendResponse(new EmployeeResource($employee->fresh('department')), 'Employee updated successfully.');
        } catch (Throwable $e) {
            info('EMPLOYEE_UPDATE_ERROR', ['error' => $e->getMessage()]);
            return $this->errorResponse($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();

            return $this->sendResponse([], 'Employee deleted successfully.', 204);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    protected function errorResponse(Throwable $e): JsonResponse
    {
        // Log::error($e); // optionally log

        $status = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : 500;

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Server Error',
        ], $status);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }
}
