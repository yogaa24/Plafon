<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // APPROVERS
        // ==========================================
        
        // Approver 1
        User::updateOrCreate(
            ['email' => 'approver1@demo.com'],
            [
                'name' => 'Koor SC',
                'password' => Hash::make('karisma'),
                'role' => 'approver1',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // Approver 2
        User::updateOrCreate(
            ['email' => 'approver2@demo.com'],
            [
                'name' => 'TC',
                'password' => Hash::make('karisma'),
                'role' => 'approver2',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // Approver 3
        User::updateOrCreate(
            ['email' => 'approver3@demo.com'],
            [
                'name' => 'Kabag',
                'password' => Hash::make('karisma'),
                'role' => 'approver3',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // Approver 4
        User::updateOrCreate(
            ['email' => 'approver4@demo.com'],
            [
                'name' => 'Kadep',
                'password' => Hash::make('karisma'),
                'role' => 'approver4',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // Approver 5
        User::updateOrCreate(
            ['email' => 'HRD KU'],
            [
                'name' => 'Approver Level 5',
                'password' => Hash::make('karisma'),
                'role' => 'approver5',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // Approver 6
        User::updateOrCreate(
            ['email' => 'approver6@demo.com'],
            [
                'name' => 'Direksi',
                'password' => Hash::make('karisma'),
                'role' => 'approver6',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // ==========================================
        // VIEWER
        // ==========================================
        User::updateOrCreate(
            ['email' => 'nila@gmail.com'],
            [
                'name' => 'Nila',
                'password' => Hash::make('karisma'),
                'role' => 'viewer',
                'is_level3_approver' => false,
                'approver_name' => null,
            ]
        );

        // ==========================================
        // SALES
        // ==========================================
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
            $email = strtolower(str_replace(' ', '', $name)) . '@gmail.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => 'sales',
                    'password' => Hash::make('karisma'),
                    'is_level3_approver' => false,
                    'approver_name' => null,
                ]
            );
        }

        $this->command->info('✅ Seeder selesai! Approver 1-6, Viewer, dan Sales berhasil dibuat.');
    }
}