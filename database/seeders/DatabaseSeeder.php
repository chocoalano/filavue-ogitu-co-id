<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */
        $roles = [
            'superadmin',
            'admin',
            'developer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Users
        |--------------------------------------------------------------------------
        */
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@ogitu.id',
                'password' => 'password',
                'role' => 'superadmin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@ogitu.id',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Developer',
                'email' => 'developer@ogitu.id',
                'password' => 'password',
                'role' => 'developer',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                [
                    'email' => $userData['email'],
                ],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),

                    /*
                     * Kolom role ini disesuaikan dengan schema users Anda
                     * yang memiliki field `role`.
                     */
                    'role' => $userData['role'],

                    'email_verified_at' => now(),
                ]
            );

            /*
             * Assign role Spatie Permission.
             */
            $user->syncRoles([$userData['role']]);
        }
    }
}
