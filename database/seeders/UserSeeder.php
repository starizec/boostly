<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed default admin and regular users.
     */
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['code' => 'XX'],
            ['name' => 'Unknown'],
        );

        $company = Company::firstOrCreate(
            ['name' => 'Demo Company'],
            [
                'vat_number' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country_id' => $country->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'regular@email.com'],
            [
                'name' => 'Regular User',
                'password' => 'password',
                'role' => 'user',
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ],
        );
    }
}
