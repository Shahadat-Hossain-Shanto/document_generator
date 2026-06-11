<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolePermissionSeeder::class);

        $adminApi=User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@trusthaus.io',
            // 'department' => 'IT',
            'mobile' => '01700000001',
            'password' => bcrypt('111111'),
        ]);

        $adminApi->assignRole(Role::where('name','admin')->where('guard_name','api')->first());
    }
}
