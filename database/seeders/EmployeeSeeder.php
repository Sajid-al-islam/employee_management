<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentIds = Department::pluck('id')->toArray();

        $chunkSize = 1000;
        $totalEmployees = 100000;

        for ($i = 0; $i < $totalEmployees; $i += $chunkSize) {
            $employees = [];
            $employeeDetails = [];
            $batchSize = min($chunkSize, $totalEmployees - $i);


            for ($j = 0; $j < $batchSize; $j++) {
                $employeeId = Str::uuid()->toString();
                $joinedDate = fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d');


                $employees[] = [
                    'id' => $employeeId,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'department_id' => $departmentIds[array_rand($departmentIds)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $employeeDetails[] = [
                    'employee_id' => $employeeId,
                    'designation' => fake()->jobTitle(),
                    'salary' => fake()->randomFloat(2, 30000, 150000),
                    'address' => fake()->address(),
                    'joined_date' => $joinedDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('employees')->insert($employees);
            DB::table('employee_details')->insert($employeeDetails);


            $this->command->info("Inserted {$batchSize} employees. Progress: " . min($i + $chunkSize, $totalEmployees) . "/{$totalEmployees}");
        }
    }
}
