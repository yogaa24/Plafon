<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Default Sales User
        User::create([
            'name' => 'Sales Demo',
            'email' => 'sales@demo.com',
            'password' => Hash::make('password'),
            'role' => 'sales'
        ]);

        // Approver 1
        User::create([
            'name' => 'Approver 1',
            'email' => 'approver1@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver1'
        ]);

        // Approver 2
        User::create([
            'name' => 'Approver 2',
            'email' => 'approver2@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver2'
        ]);

        // Approver 3
        User::create([
            'name' => 'Approver 3',
            'email' => 'approver3@demo.com',
            'password' => Hash::make('password'),
            'role' => 'approver3'
        ]);

        // Viewer
        User::create([
            'name' => 'Viewer Demo',
            'email' => 'viewer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'viewer'
        ]);

        /**
         * Tambahan Daftar Sales Baru
         */
        $salesList = [
            'Zakia',
            'Reni',
            'Yuyun',
            'Faris',
            'Ariyani',
            'Hendra',
            'Sheila',
            'Others',
            'general',
        ];

        foreach ($salesList as $name) {
            $email = strtolower(str_replace(' ', '', $name)) . '@demo.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => 'sales',
                    'password' => Hash::make('password'),
                    'is_level3_approver' => false,
                    'approver_name' => null,
                ]
            );
        }
    }
}
