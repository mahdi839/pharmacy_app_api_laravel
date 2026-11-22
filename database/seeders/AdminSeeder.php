<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Replaces all dashboard admins with a single account.
 *
 * Credentials are read from the environment so they never live in this
 * (public) repository:
 *
 *   ADMIN_EMAIL=... ADMIN_PASSWORD=... php artisan db:seed --class=AdminSeeder --force
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            $this->command?->warn('AdminSeeder skipped: set ADMIN_EMAIL and ADMIN_PASSWORD to seed the admin account.');

            return;
        }

        DB::transaction(function () use ($email, $password): void {
            // Remove every other admin (including the old default account) first,
            // so the unique phone number can be reused.
            Admin::where('gmail', '!=', $email)->delete();

            Admin::updateOrCreate(
                ['gmail' => $email],
                [
                    'name' => env('ADMIN_NAME', 'Med Bangladesh Admin'),
                    'phone' => env('ADMIN_PHONE', '01700000000'),
                    'password' => $password,
                ],
            );
        });

        $this->command?->info("Admin account ready: {$email}");
    }
}
