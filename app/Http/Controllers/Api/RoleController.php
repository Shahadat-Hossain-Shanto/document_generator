<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Role;
use App\Models\RoleHasPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function data()
    {
        $roles = Role::query()
            ->with(['permissions:id,name'])
            ->where('guard_name', 'api')
            ->latest()
            ->get(['id', 'name', 'guard_name']);

        $data = $roles->map(fn ($role) => [
            'id' => $role->id,
            'name' => ucfirst($role->name),
            'guard' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name')->values(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function permissions()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'api')
            ->select('id', 'name', 'permission_name', 'permission_type', 'group_name')
            ->orderBy('permission_name')
            ->get()
            ->groupBy(['group_name', 'permission_name']);

        if ($permissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No permissions found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validator=Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where(
                    fn ($q) => $q->where('guard_name', 'api')
                ),
            ],
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::transaction(function () use ($validated, &$role) {
                $role = Role::create([
                    'name'       => strtolower($validated['name']),
                    'guard_name' => 'api',
                ]);

                $role->syncPermissions(array_map('intval', $validated['permissions']));
            });

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data'    => [
                    'id'         => $role->id,
                    'name'       => $role->name,
                ],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role.',
                'error'   => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        $permissions = Permission::select('id', 'name')->where('guard_name', 'api')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions' => $role->permissions->pluck('id'),
                ],
                'permissions' => $permissions
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::where('guard_name', 'api')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('guard_name', 'api'))
                    ->ignore($role->id),
            ],
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::transaction(function () use ($validated, $role) {
                $role->update([
                    'name' => strtolower($validated['name']),
                ]);

                $role->syncPermissions(array_map('intval', $validated['permissions']));
            });

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data'    => [
                    'id'   => $role->id,
                    'name' => $role->name,
                ],
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'error'   => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $role = Role::where('guard_name', 'api')->find($id);

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($role) {
                $role->permissions()->detach();

                $role->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Role and its permissions deleted successfully.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.',
                'error'   => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

}
