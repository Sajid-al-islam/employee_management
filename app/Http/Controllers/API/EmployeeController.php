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
    public function index(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'search' => 'nullable|string',
            'department_id' => 'nullable|integer|exists:departments,id',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
        ]);

        // Base query with relationships
        $query = Employee::with(['department', 'details'])
            ->latest()
            ->select('employees.*')
            ->join('employee_details', 'employees.id', '=', 'employee_details.employee_id');

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'LIKE', "%{$search}%")
                    ->orWhere('employees.email', 'LIKE', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Salary range filter
        if ($request->filled('salary_min') || $request->filled('salary_max')) {
            $query->where(function ($q) use ($request) {
                if ($request->filled('salary_min')) {
                    $q->where('employee_details.salary', '>=', $request->salary_min);
                }
                if ($request->filled('salary_max')) {
                    $q->where('employee_details.salary', '<=', $request->salary_max);
                }
            });
        }

        // Sorting
        $query->orderBy('employee_details.joined_date', $request->input('order', 'desc'));

        // Pagination
        $perPage = $request->input('per_page', 25);
        $employees = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => EmployeeResource::collection($employees),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'name' => 'required|string',
                'email' => 'required|email|unique:employees,email',
                'department_id' => 'required|exists:departments,id',
                'details.designation' => 'required|string',
                'details.salary' => 'required|numeric',
                'details.joined_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $employeeData = $request->only(['name', 'email', 'department_id']);
            $detailsData = $request->input('details');

            $employee = DB::transaction(function () use ($employeeData, $detailsData) {
                $employee = Employee::create($employeeData);
                $employee->details()->create($detailsData);
                return $employee->load(['department', 'details']);
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
                'details.designation' => 'required|string',
                'details.salary' => 'required|numeric',
                'details.joined_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $employeeData = $request->only(['name', 'email', 'department_id']);
            $detailsData = $request->input('details');

            DB::transaction(function () use ($employeeData, $detailsData, $employee) {
                $employee->update($employeeData);
                $employee->details()->update($detailsData);
            });

            $employee->load(['department', 'details']);

            return $this->sendResponse(new EmployeeResource($employee), 'Employee updated successfully.');
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

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }
}
