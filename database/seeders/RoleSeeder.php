<?php

namespace Database\Seeders;

use App\Enum\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::query()->create([
            'name' => 'admin',
        ]);

        User::query()
            ->first()
            ->assignRole($admin);

        foreach (Permission::values() as $permission) {
            $p = \App\Models\Permission::query()->create([
                'name' => $permission,
            ]);
            $admin->givePermissionTo($p);
        }
    }
}
