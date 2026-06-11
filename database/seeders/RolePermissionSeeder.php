<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // $permissions = [
        //     [
        //         'name'            => 'user.view',
        //         'permission_name' => 'User',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'user.create',
        //         'permission_name' => 'User',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'user.edit',
        //         'permission_name' => 'User',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'user.destroy',
        //         'permission_name' => 'User',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],

        //     [
        //         'name'            => 'role.view',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'role.create',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'role.edit',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'role.destroy',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.view',
        //         'permission_name' => 'Permission',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.create',
        //         'permission_name' => 'Permission',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.edit',
        //         'permission_name' => 'Permission',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.destroy',
        //         'permission_name' => 'Permission',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.group.view',
        //         'permission_name' => 'Permission Group',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.group.create',
        //         'permission_name' => 'Permission Group',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.group.edit',
        //         'permission_name' => 'Permission Group',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'permission.group.destroy',
        //         'permission_name' => 'Permission Group',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],
        // ];

        // $ApiPermissions = [
        //     [
        //         'name'            => 'api.user.view',
        //         'permission_name' => 'User',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.user.create',
        //         'permission_name' => 'User',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.user.edit',
        //         'permission_name' => 'User',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.user.destroy',
        //         'permission_name' => 'User',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],

        //     [
        //         'name'            => 'api.role.view',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'view',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.role.create',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'create',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.role.edit',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'edit',
        //         'group_name'      => 'User Management',
        //     ],
        //     [
        //         'name'            => 'api.role.destroy',
        //         'permission_name' => 'Role',
        //         'permission_type' => 'destroy',
        //         'group_name'      => 'User Management',
        //     ],
        // ];

        /*
        |--------------------------------------------------------------------------
        | WEB
        |--------------------------------------------------------------------------
        */
        // foreach ($permissions as $permission) {
        //     Permission::updateOrCreate(
        //         [
        //             'name'       => $permission['name'],
        //             'guard_name' => 'web',
        //         ],
        //         [
        //             'permission_name' => $permission['permission_name'],
        //             'permission_type' => $permission['permission_type'],
        //             'group_name'      => $permission['group_name'],
        //         ]
        //     );
        // }

        // $webAdmin = Role::firstOrCreate([
        //     'name'       => 'admin',
        //     'guard_name' => 'web',
        // ]);

        // $webAdmin->syncPermissions(
        //     Permission::where('guard_name', 'web')->get()
        // );

        /*
        |--------------------------------------------------------------------------
        | API
        |--------------------------------------------------------------------------
        */
        // foreach ($ApiPermissions as $permission) {
        //     Permission::updateOrCreate(
        //         [
        //             'name'       => $permission['name'],
        //             'guard_name' => 'api',
        //         ],
        //         [
        //             'permission_name' => $permission['permission_name'],
        //             'permission_type' => $permission['permission_type'],
        //             'group_name'      => $permission['group_name'],
        //         ]
        //     );
        // }

        $apiAdmin = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'api',
        ]);

        $apiAdmin->syncPermissions(
            Permission::where('guard_name', 'api')->get()
        );

        // $permissionGroups = PermissionGroup::firstOrCreate([
        //     'group_name' => 'User Management',
        // ]);

    }
}
