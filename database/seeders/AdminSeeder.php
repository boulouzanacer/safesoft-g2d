<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::firstOrNew(['email' => 'admin@safesoft.dz']);
        $admin->nom = 'SafeSoft';
        $admin->prenom = 'Admin';
        $admin->password = Hash::make('Admin@123');
        $admin->save();
    }
}
