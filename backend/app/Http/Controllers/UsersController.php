<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::latest()->get();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'user_type' => 'required|in:user,admin,super_admin',
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return User::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'sometimes|string|min:8',
            'user_type' => 'sometimes|in:user,admin,super_admin',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function changeRole(Request $request)
    {
        Log::info('changeRole called', ['request' => $request->all()]);

        $currentUser = $request->user();
        Log::info('Authenticated user', ['user' => $currentUser ? $currentUser->id : null]);

        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            Log::warning('Unauthorized attempt to change role', ['user' => $currentUser ? $currentUser->id : null]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'user_type' => 'required|in:user,admin,super_admin',
        ]);
        Log::info('Validated request data', ['validated' => $validated]);

        $email = strtolower(trim($validated['email']));
        $targetUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
        Log::info('Target user fetched', ['targetUser' => $targetUser ? $targetUser->id : null]);

        if (!$targetUser) {
            Log::warning('Target user not found', ['email' => $email]);
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($targetUser->id === $currentUser->id) {
            Log::warning('User attempted to change own role', ['user' => $currentUser->id]);
            return response()->json(['message' => 'You cannot change your own role.'], 403);
        }

        try {
            Log::info('Updating target user role', ['targetUser' => $targetUser->id, 'newRole' => $validated['user_type']]);
            $targetUser->update(['user_type' => $validated['user_type']]);
            Log::info('Role updated successfully', ['targetUser' => $targetUser->id]);
        } catch (\Exception $e) {
            Log::error('Failed to update role', [
                'targetUser' => $targetUser->id ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'user' => $targetUser
        ]);
    }

    public function remove(Request $request)
    {
        $currentUser = $request->user();

        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        // Prevent deleting other superadmins
        if ($user->isSuperAdmin()) {
            return response()->json([
                'message' => 'You cannot delete another superadmin user.'
            ], 403);
        }

        try {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->user_type === 'super_admin') {
            return response()->json([
                'message' => 'Cannot delete a super admin account.'
            ], 403);
        }

        $authUser = Auth::user();
        if ($authUser->id !== $user->id && $authUser->user_type !== 'super_admin') {
            return response()->json([
                'message' => 'You can only delete your own account.'
            ], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}