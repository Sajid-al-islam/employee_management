<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeDetail>
 */
class EmployeeDetailFactory extends Factory
{
    protected $model = EmployeeDetail::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'designation' => $this->faker->jobTitle(),
            'salary' => $this->faker->randomFloat(2, 30000, 150000),
            'address' => $this->faker->address(),
            'joined_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
        ];
    }
}
