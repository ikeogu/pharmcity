<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'pharmacist' => [
                'can_view_drug_list',
                'can_create_drug',
                'can_edit_drug',
                'can_delete_drug',
                'can_view_patient_list',
                'can_prescibe_drug'
            ],
            'reporter' => [
                'can_view_patient_list',
                'can_create_patient',
                'can_edit_patient',
                'can_delete_patient',
            ],
            'superadmin' => [
                'can_view_user_list',
                'can_create_user',
                'can_edit_user',
                'can_delete_user',
            ],
            'doctor' => [
                'can_view_patient_list',
            ],
            'consultant' => [
                'can_view_patient_list',
            ],

        ];

       foreach ($permissions as $roleName => $rolePermissions) {
            // Create or get the role
            $role = Role::firstOrCreate(['name' => $roleName]);

            foreach ($rolePermissions as $permissionName) {
                // Create or get the permission
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                // Assign permission to role
                $role->givePermissionTo($permission);
            }
        }
    }
}
