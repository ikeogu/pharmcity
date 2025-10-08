<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrcreate(['name' => 'superadmin']);
        Role::updateOrcreate(['name' => 'reporter']);
        Role::updateOrcreate(['name' => 'pharmacist']);
        Role::updateOrcreate(['name' => 'doctor']);
        Role::updateOrcreate(['name' => 'consultant']);
    }
}
