<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->command->info('🟢 Seeding User…');
        $this->call(UserSeeder::class);

        $this->command->info('🟢 Seeding Departments...');
        $this->call(DepartmentSeeder::class);

        $this->command->info('🟢 Seeding Employees...');
        $this->call(EmployeeSeeder::class);


    }
}
