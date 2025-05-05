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

/**
 * @OA\Tag(
 *     name="Employees",
 *     description="API Endpoints for Employee Management"
 * )
 */

/**
 * @OA\Info(
 *     title="Employee Management API",
 *     version="1.0.0",
 *     description="API for managing employees and their details",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *     name="Employees",
 *     description="Operations about employees"
 * )
 */
class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of employees with optional filters.
     *
     * @OA\Get(
     *     path="/api/employees",
     *     operationId="getEmployees",
     *     tags={"Employees"},
     *     summary="Get list of employees",
     *     description="Returns list of employees with optional filtering, sorting and pagination",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for employee name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="department_id",
     *         in="query",
     *         description="Filter by department ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="salary_min",
     *         in="query",
     *         description="Minimum salary filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="salary_max",
     *         in="query",
     *         description="Maximum salary filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc/desc) by joined date",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/EmployeeResource")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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


        $query->orderBy('employee_details.joined_date', $request->input('order', 'desc'));


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
     * Store a newly created employee in storage.
     *
     * @OA\Post(
     *     path="/api/employees",
     *     operationId="createEmployee",
     *     tags={"Employees"},
     *     summary="Create a new employee",
     *     description="Stores a new employee record with details",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "department_id", "details"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="details",
     *                 type="object",
     *                 required={"designation", "salary", "joined_date"},
     *                 @OA\Property(property="designation", type="string", example="Software Engineer"),
     *                 @OA\Property(property="salary", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="joined_date", type="string", format="date", example="2023-01-15")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Employee created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmployeeResource"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
     * Display the specified employee.
     *
     * @OA\Get(
     *     path="/api/employees/{id}",
     *     operationId="getEmployee",
     *     tags={"Employees"},
     *     summary="Get employee details",
     *     description="Returns employee data by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of employee to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmployeeResource"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Employee $employee)
    {
        try {
            $employee->load('department');

            if (empty($employee)) {
                return $this->sendError('Employee not found.');
            }

            return $this->sendResponse(new EmployeeResource($employee), 'Employee retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the specified employee in storage.
     *
     * @OA\Put(
     *     path="/api/employees/{id}",
     *     operationId="updateEmployee",
     *     tags={"Employees"},
     *     summary="Update existing employee",
     *     description="Updates employee record and returns updated data",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of employee to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="department_id", type="integer", example=2),
     *             @OA\Property(
     *                 property="details",
     *                 type="object",
     *                 @OA\Property(property="designation", type="string", example="Senior Software Engineer"),
     *                 @OA\Property(property="salary", type="number", format="float", example=60000.00),
     *                 @OA\Property(property="joined_date", type="string", format="date", example="2023-01-15")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EmployeeResource"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
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
     * Remove the specified employee from storage.
     *
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     operationId="deleteEmployee",
     *     tags={"Employees"},
     *     summary="Delete an employee",
     *     description="Deletes an employee record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of employee to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Employee deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", example={}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Employee $employee)
    {
        try {
            if (empty($employee)) {
                return $this->sendError('Employee not found.');
            }

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

/**
 * @OA\Schema(
 *     schema="StoreEmployeeRequest",
 *     required={"name", "email", "department_id", "details"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         required={"designation", "salary", "joined_date"},
 *         @OA\Property(property="designation", type="string", example="Software Engineer"),
 *         @OA\Property(property="salary", type="number", format="float", example=50000.00),
 *         @OA\Property(property="joined_date", type="string", format="date", example="2023-01-15")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="UpdateEmployeeRequest",
 *     @OA\Property(property="name", type="string", example="John Doe Updated"),
 *     @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
 *     @OA\Property(property="department_id", type="integer", example=2),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         @OA\Property(property="designation", type="string", example="Senior Software Engineer"),
 *         @OA\Property(property="salary", type="number", format="float", example=60000.00),
 *         @OA\Property(property="joined_date", type="string", format="date", example="2023-01-15")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="EmployeeResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="department_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="department",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         @OA\Property(property="designation", type="string"),
 *         @OA\Property(property="salary", type="number", format="float"),
 *         @OA\Property(property="joined_date", type="string", format="date")
 *     )
 * )
 */
