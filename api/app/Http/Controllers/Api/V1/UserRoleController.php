<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    // GET /v1/admin/roles
    public function listRoles()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json(['data' => $roles]);
    }

    // GET /v1/admin/users
    public function index(Request $request)
    {
        $q     = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $users]);
    }

    // GET /v1/admin/users/{user}/roles
    public function getUserRoles(User $user)
    {
        $roles = $user->roles()
            ->select('roles.id', 'roles.code', 'roles.name')
            ->orderBy('roles.name')
            ->get();

        return response()->json(['data' => $roles]);
    }

    // POST /v1/admin/users/{user}/roles/sync
    public function sync(Request $request, User $user)
    {
        // Accept either role_ids (preferred) OR role_codes (fallback)
        $data = $request->validate([
            'role_ids'     => ['array'],
            'role_ids.*'   => ['integer', 'exists:roles,id'],
            'role_codes'   => ['array'],
            'role_codes.*' => ['string', Rule::exists('roles', 'code')],
        ]);

        $ids = $data['role_ids'] ?? [];

        if (! $ids && ! empty($data['role_codes'])) {
            $ids = Role::whereIn('code', $data['role_codes'])->pluck('id')->all();
        }

        $user->roles()->sync($ids);

        return response()->json(['message' => 'Roles synced', 'role_ids' => $ids]);
    }
}
