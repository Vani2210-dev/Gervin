<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view user', ['only' => ['index', 'show']]);
        $this->middleware('permission:add user', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete user', ['only' => ['destroy']]);
        // viewProfile & updateProfile: chỉ cần đăng nhập, không cần quyền
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('user_code', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($request->filled('filter_name')) {
            $query->where('name', 'like', "%{$request->filter_name}%");
        }

        if ($request->filled('filter_email')) {
            $query->where('email', 'like', "%{$request->filter_email}%");
        }

        if ($request->filled('filter_phone')) {
            $query->where('phone', 'like', "%{$request->filter_phone}%");
        }

        if ($request->filled('filter_role_id')) {
            $query->where('role_id', $request->filter_role_id);
        }

        $perPage = (int) $request->input('per_page', 10);
        $users   = $query->latest()->paginate($perPage)->withQueryString();

        return view('users.index', compact('users', 'perPage'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.add', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_code'   => 'nullable|string|max:50|unique:users,user_code',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'role_id'     => 'nullable|exists:roles,id',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'private');
        }

        $user = User::create([
            'user_code' => $request->user_code,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'description' => $request->description,
            'password'  => bcrypt('password'),
            'role_id'   => $request->role_id,
            'avatar'    => $avatarPath,
        ]);

        if ($user->role_id) {
            $role = Role::find($user->role_id);
            $user->assignRole($role->name);
        }

        return redirect()->route('users.index')->with('success', 'Người dùng đã được tạo thành công');
    }

    public function show(User $user)
    {
        $user->load('role');
        $roles = Role::all();
        return view('users.viewProfile', compact('user', 'roles'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'user_code'   => 'nullable|string|max:50|unique:users,user_code,' . $user->id,
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'role_id'     => 'nullable|exists:roles,id',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $oldRoleId = $user->role_id;

        $data = [
            'user_code'   => $request->user_code,
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'description' => $request->description,
            'role_id'     => $request->role_id,
        ];

        if ($request->hasFile('avatar')) {
            $stored = $request->file('avatar')->store('avatars', 'private');
            if ($stored !== false) {
                if ($user->avatar) {
                    Storage::disk('private')->delete($user->avatar);
                }
                $data['avatar'] = $stored;
            }
        }

        $user->update($data);

        if ($request->has('password') && $request->password) {
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        // Remove old role if changed
        if ($oldRoleId) {
            $oldRole = Role::find($oldRoleId);
            if ($oldRole) {
                $user->removeRole($oldRole->name);
            }
        }

        // Assign new role
        if ($user->role_id) {
            $role = Role::find($user->role_id);
            $user->assignRole($role->name);
        }

        return redirect()->route('users.index')->with('success', 'Người dùng đã được cập nhật thành công');
    }

    public function destroy(User $user)
    {
        if ($user->id === 1 || $user->email === 'admin@kbtech.com') {
            return redirect()->route('users.index')->with('error', 'Không thể xóa tài khoản Admin quản trị cao nhất của hệ thống.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Không thể xóa chính mình');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Người dùng đã được xóa thành công');
    }

    public function avatar(User $user)
    {
        if (!$user->avatar || !Storage::disk('private')->exists($user->avatar)) {
            abort(404);
        }

        $path     = storage_path('app/private/' . $user->avatar);
        $mimeType = Storage::disk('private')->mimeType($user->avatar);

        return Response::file($path, [
            'Content-Type'  => $mimeType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    public function codeGenerator()
    {
        return view('aiapplication/codeGenerator');
    }

    public function usersGrid()
    {
        return view('users/usersGrid');
    }

    public function viewProfile()
    {
        $user  = auth()->user()->load('role');
        $roles = Role::all();
        return view('users.viewProfile', compact('user', 'roles'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'avatar'      => 'nullable|image|max:2048',
            'password'    => 'nullable|string|min:8',
        ]);

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'description' => $request->description,
        ];

        if ($request->hasFile('avatar')) {
            $stored = $request->file('avatar')->store('avatars', 'private');
            if ($stored !== false) {
                if ($user->avatar) {
                    Storage::disk('private')->delete($user->avatar);
                }
                $data['avatar'] = $stored;
            }
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('viewProfile')->with('success', 'Cập nhật hồ sơ thành công.');
    }
}
