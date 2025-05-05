<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="EmployeeResource",
     *     type="object",
     *     title="Employee Resource",
     *     description="Employee resource representation",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *     @OA\Property(property="department_id", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time"),
     *     @OA\Property(
     *         property="department",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="IT")
     *     ),
     *     @OA\Property(
     *         property="details",
     *         type="object",
     *         @OA\Property(property="designation", type="string", example="Developer"),
     *         @OA\Property(property="salary", type="number", format="float", example=50000.00),
     *         @OA\Property(property="joined_date", type="string", format="date", example="2023-01-01")
     *     )
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'department'    => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ],
            'details'       => [
                'designation' => $this->details->designation,
                'salary'      => $this->details->salary,
                'address'     => $this->details->address,
                'joined_date' => $this->details->joined_date,
            ],
            'created_at'    => $this->created_at->toDateTimeString(),
            'updated_at'    => $this->updated_at->toDateTimeString(),
        ];
    }
}
