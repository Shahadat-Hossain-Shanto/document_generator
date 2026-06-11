<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function roleList()
    {
        $roles = Role::where('guard_name', 'api')
            ->get(['id', 'name'])
            ->map(function ($role) {
                return [
                    'id'   => $role->id,
                    'name' => Str::ucfirst($role->name),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Roles fetched successfully.',
            'data'    => $roles
        ], 200);
    }

    public function store(Request $request)
    {
        $validator =Validator::make($request->all(), [
            'name'       => ['required', 'string', 'max:100'],
            'mobile'     => ['nullable', 'string', 'max:20'],
            // 'department' => ['nullable', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::defaults()],
            'role_id'    => ['required', 'exists:roles,id'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'mobile'     => $validated['mobile'] ?? null,
                // 'department' => $validated['department'] ?? null,
                'password'   => Hash::make($validated['password']),
            ]);

            $role = Role::where('id', $validated['role_id'])
                        ->where('guard_name', 'api')
                        ->firstOrFail();

            $user->assignRole($role);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User created successfully.',
                'data'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'User creation failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = User::with('roles:id,name')->findOrFail($id);

        $roles = Role::where('guard_name', 'api')
            ->select('id', 'name')
            ->get()
            ->map(function ($role) {
                return [
                    'id'   => $role->id,
                    'name' => ucfirst($role->name),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => [
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'mobile'     => $user->mobile,
                    // 'department' => $user->department,
                    'role_id'    => $user->roles->first()?->id,
                ],
                'roles' => $roles
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'       => ['required', 'string', 'max:100'],
            'mobile'     => ['nullable', 'string', 'max:20'],
            // 'department' => ['nullable', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'   => ['nullable', 'confirmed', Password::defaults()],
            'role_id'    => ['required', 'exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $user->update([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'mobile'     => $validated['mobile'] ?? null,
                // 'department' => $validated['department'] ?? null,
                'password'   => isset($validated['password'])
                    ? Hash::make($validated['password'])
                    : $user->password,
            ]);

            $role = Role::where('id', $validated['role_id'])
                        ->where('guard_name', 'api')
                        ->firstOrFail();

            $user->syncRoles([$role]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User updated successfully.',
                'data'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'User update failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function data()
    {
        $users = User::whereHas('roles', function ($query) {
                $query->where('guard_name', 'api');
            })
            ->with(['roles' => function ($query) {
                $query->select('id', 'name', 'guard_name')
                    ->where('guard_name', 'api');
            }])
            // ->select('id', 'name', 'email', 'mobile', 'department')
            ->select('id', 'name', 'email', 'mobile')
            ->get()
            ->map(function ($user) {
                return [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'mobile'     => $user->mobile,
                    // 'department' => $user->department,
                    'role'       => $user->roles->map(fn($r) => ucfirst($r->name))->implode(', '),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $users,
        ]);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {

            $user->syncRoles([]);
            $user->syncPermissions([]);

            $user->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User deleted successfully.'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'User deletion failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updatePass(Request $request)
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password updated successfully.',
        ], 200);
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'mobile'     => 'nullable|string|max:20',
            // 'department' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // $user->update($request->only('name', 'email', 'mobile', 'department'));
        $user->update($request->only('name', 'email', 'mobile'));

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'mobile'     => $user->mobile,
                // 'department' => $user->department,
            ],
        ], 200);
    }

}
