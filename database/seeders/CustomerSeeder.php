<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'email' => 'tharyvireak@gmail.com',
                'name' => 'Thary Vireak',
                'password' => Hash::make('reak123'),
                'phone' => '+855 71 675 5350',
                'address' => 'Phnom Penh, Cambodia',
                'status' => 'active',
            ],
            [
                'email' => 'john.doe@example.com',
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'phone' => '+1 555 000 1234',
                'address' => 'New York, USA',
                'status' => 'active',
            ],
            [
                'email' => 'jane.smith@example.com',
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'phone' => '+44 20 7946 0958',
                'address' => 'London, UK',
                'status' => 'active',
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'status' => $data['status'],
                ]
            );
        }

        $this->command->info('CustomerSeeder: '.count($customers).' customers seeded.');
    }
}
