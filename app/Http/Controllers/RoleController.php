<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view role', ['only' => ['index']]);
        $this->middleware('permission:add role', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete role', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Role::with(['permissions', 'users']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%$search%");
        }

        if ($request->filled('filter_name')) {
            $query->where('name', 'like', "%{$request->filter_name}%");
        }

        $perPage = (int) $request->input('per_page', 10);
        $roles   = $query->paginate($perPage)->withQueryString();

        return view('roles.index', compact('roles', 'perPage'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role đã được tạo thành công');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role đã được cập nhật thành công');
    }

    public function destroy(Role $role)
    {
        if (strtolower($role->name) === 'admin' || $role->id === 1) {
            return redirect()->route('roles.index')->with('error', 'Không thể xóa vai trò Admin hệ thống.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role đã được xóa thành công');
    }
}
