<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Guru']);
        Role::create(['name' => 'Pembina']);
        Role::create(['name' => 'Satpam']);
        Role::create(['name' => 'Cleaning Service']);
    }
}
