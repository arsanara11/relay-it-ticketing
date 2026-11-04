<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('RELAY_ADMIN_PASSWORD');

        if (empty($password)) {
            throw new \RuntimeException(
                'Set RELAY_ADMIN_PASSWORD in your .env file.'
            );
        }

        $admin = User::firstOrNew([
            'email' => 'admin@relay.test',
        ]);

        $admin->name = 'Relay Administrator';
        $admin->role = 'admin';

        if (!$admin->exists) {
            $admin->password = Hash::make($password);
        }

        $admin->save();
    }
}
