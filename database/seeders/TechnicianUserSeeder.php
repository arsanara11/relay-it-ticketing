<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TechnicianUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('RELAY_TECHNICIAN_PASSWORD');

        if (empty($password)) {
            throw new \RuntimeException(
                'Set RELAY_TECHNICIAN_PASSWORD in your .env file.'
            );
        }

        $technician = User::firstOrNew([
            'email' => 'technician@relay.test',
        ]);

        $technician->name = 'Relay Technician';
        $technician->role = 'technician';

        if (!$technician->exists) {
            $technician->password = Hash::make($password);
        }

        $technician->save();
    }
}
