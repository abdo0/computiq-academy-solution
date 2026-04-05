<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleSwitchController extends Controller
{
    public function roles(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'active_role' => $user->resolvedActiveRole(),
                'available_roles' => $user->availableAppRoles(),
            ],
        ]);
    }

    public function switchRole(Request $request): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys(User::appRoleMap()))],
        ]);

        /** @var User $user */
        $user = $request->user();
        $availableRoles = $user->availableAppRoles();
        $targetRole = (string) $request->string('role');

        if (! in_array($targetRole, $availableRoles, true)) {
            return response()->json([
                'message' => __('You do not have access to this role.'),
            ], 403);
        }

        $user->update(['active_role' => $targetRole]);

        return response()->json([
            'data' => [
                'active_role' => $targetRole,
                'available_roles' => $availableRoles,
            ],
            'message' => __('Role switched successfully.'),
        ]);
    }
}
