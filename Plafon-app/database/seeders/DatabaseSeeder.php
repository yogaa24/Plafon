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
            ]
        );

        // ==========================================
        // APPROVER 2 - TC (5 Users)
        // ==========================================
        $tcUsers = [
            ['email' => 'tc1@demo.com', 'name' => 'TC - Zahro'],
            ['email' => 'tc2@demo.com', 'name' => 'TC - Farida'],
            ['email' => 'tc3@demo.com', 'name' => 'TC - Tiya'],
            ['email' => 'tc4@demo.com', 'name' => 'TC - Tashya'],
            ['email' => 'tc5@demo.com', 'name' => 'TC - Natasya'],
        ];

        foreach ($tcUsers as $tc) {
            User::updateOrCreate(
                ['email' => $tc['email']],
                [
                    'name' => $tc['name'],
                    'password' => Hash::make('karisma'),
                    'role' => 'approver2',
                ]
            );
        }

        // Approver 3
        User::updateOrCreate(
            ['email' => 'approver3@demo.com'],
            [
                'name' => 'Kabag KEU',
                'password' => Hash::make('karisma'),
                'role' => 'approver3',
            ]
        );

        // Approver 4
        User::updateOrCreate(
            ['email' => 'approver4@demo.com'],
            [
                'name' => 'Kadep KEU',
                'password' => Hash::make('karisma'),
                'role' => 'approver4',
            ]
        );

        // Approver 5
        User::updateOrCreate(
            ['email' => 'approver5@demo.com'],
            [
                'name' => 'Kadep KEU & HRD',
                'password' => Hash::make('karisma'),
                'role' => 'approver5',
            ]
        );

        // Approver 6
        User::updateOrCreate(
            ['email' => 'approver6@demo.com'],
            [
                'name' => 'Direksi',
                'password' => Hash::make('karisma'),
                'role' => 'approver6',
            ]
        );

        // ==========================================
        // VIEWER
        // ==========================================
        User::updateOrCreate(
            ['email' => 'sukma@gmail.com'],
            [
                'name' => 'Sukma',
                'password' => Hash::make('karisma'),
                'role' => 'viewer',
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
                ]
            );
        }

        $this->command->info('✅ Seeder selesai!');
        $this->command->info('   - Approver 1: 1 user (Koor SC)');
        $this->command->info('   - Approver 2: 5 users (TC)');
        $this->command->info('   - Approver 3-6: masing-masing 1 user');
        $this->command->info('   - Viewer: 1 user');
        $this->command->info('   - Sales: ' . count($salesList) . ' users');
    }
}