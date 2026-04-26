<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Bug;
use Illuminate\Http\Request;

class BugsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('limit', 10);
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $severity = $request->query('severity');

        $query = Bug::with(['category', 'createdBy', 'assignedAdmin'])->latest();

        if (!$request->hasAny(['user_id', 'assigned_admin', 'userHistory'])) {
            $query->where('status', '!=', 'closed');
        }

        if ($request->query('user_id')) {
            $query->where('created_by', $request->query('user_id'));
        }

        if ($request->query('assigned_admin')) {
            $query->where('assigned_admin', $request->query('assigned_admin'));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        $bugs = $query->paginate($perPage);

        return response()->json($bugs);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'status'          => 'required|in:open,in_progress,resolved,closed',
            'severity'        => 'required|in:low,medium,high,critical',
            'category_id'     => 'required|exists:categories,id',
            'assigned_admin'  => 'nullable|exists:users,id',
        ]);

        $bug = Bug::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            $bug->load(['category', 'createdBy', 'assignedAdmin']), 201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Bug::with([
            'category',
            'createdBy',
            'assignedAdmin',
            'comments',
            'images',
        ])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bug = Bug::findOrFail($id);

        $validated = $request->validate([
            'title'           => 'sometimes|string|max:255',
            'description'     => 'sometimes|string',
            'status'          => 'sometimes|in:open,in_progress,resolved,closed',
            'severity'        => 'sometimes|in:low,medium,high,critical',
            'category_id'     => 'sometimes|exists:categories,id',
            'assigned_admin'  => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();

        if (in_array($validated['status'] ?? '', ['resolved', 'closed'])) {
            if ($bug->assigned_admin !== $user->id && $user->user_type !== 'super_admin') {
                return response()->json(['message' => 'Only the assigned admin can close this bug.'], 403);
            }
        }

        $bug->update($validated);

        return $bug->load(['category', 'createdBy', 'assignedAdmin']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bug = Bug::findOrFail($id);
        $bug->delete();

        return response()->json(['message' => 'Bug deleted successfully.']);
    }



    public function closeResolved(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        if (!in_array($user->user_type, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $days = $validated['older_than_days'];
        $cutoffDate = now()->subDays($days);

        $count = Bug::where('status', 'resolved')
            ->where('updated_at', '<=', $cutoffDate)
            ->where('assigned_admin', $user->id)
            ->update([
                'status' => 'closed'
            ]);

        return response()->json([
            'message' => "$count bugs closed successfully.",
            'closed_count' => $count
        ]);
    }

    public function deleteClosed(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        if ($user->user_type !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $days = $validated['older_than_days'];
        $cutoffDate = now()->subDays($days);

        $count = Bug::where('status', 'closed')
            ->where('updated_at', '<=', $cutoffDate)
            ->delete();

        return response()->json([
            'message' => "$count bugs deleted successfully.",
            'deleted_count' => $count
        ]);
    }
}
