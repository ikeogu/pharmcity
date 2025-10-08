<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $city = City::first();
        $user =  User::updateOrCreate([
            'email' => 'admin@pharmcity.com'
        ],[
            'title' => 'Mr.',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'dob' => '2000-01-01',
            'gender' => 'male',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'password' => 'password',
            'city_id' => $city->id,
            'state_id' => $city->state->id,
            'country_id' => $city->state->country->id,
        ]);

        $role = Role::where('name','superadmin')->first();
        $user->assignRole($role->name);
    }
}
