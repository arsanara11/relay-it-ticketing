<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hardware Repair',
                'description' => 'Diagnostics and repair of physical computer components.',
            ],
            [
                'name' => 'Software Troubleshooting',
                'description' => 'Operating system, application, and software-related issues.',
            ],
            [
                'name' => 'Network Issues',
                'description' => 'Internet connectivity, LAN, Wi-Fi, and network configuration problems.',
            ],
            [
                'name' => 'Preventive Maintenance',
                'description' => 'Routine inspection, cleaning, and preventive device maintenance.',
            ],
            [
                'name' => 'Data Recovery',
                'description' => 'Recovery of lost, deleted, or inaccessible files and data.',
            ],
            [
                'name' => 'Peripheral Repair',
                'description' => 'Repair and troubleshooting of printers, monitors, and other peripherals.',
            ],
        ];

        foreach ($categories as $category) {
            TicketCategory::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
