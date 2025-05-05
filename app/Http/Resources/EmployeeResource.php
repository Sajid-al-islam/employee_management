<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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
