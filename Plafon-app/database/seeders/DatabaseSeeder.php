<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Sales User
        User::create([
            'name' => 'Sales Demo',
            'email' => 'sales@demo.com',
            'password' => Hash::make('password'),
            'role' => 'sales'
        ]);

        User::create([
            'name' => 'Sales Demo2',
            'email' => 'sales2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'sales'
        ]);

        // Create Approver 1
        User::create([
            'name' => 'Approver 1',
            'email' => 'approver1@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver1'
        ]);

        // Create Approver 2
        User::create([
            'name' => 'Approver 2',
            'email' => 'approver2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver2'
        ]);

        // Create Approver 3
        User::create([
            'name' => 'Approver 3',
            'email' => 'approver3@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver3'
        ]);

        User::create([
            'name' => 'Viewer Demo',
            'email' => 'viewer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'viewer'
        ]);
    }
}
