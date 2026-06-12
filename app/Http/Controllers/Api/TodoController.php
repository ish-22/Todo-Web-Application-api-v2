<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Todo\IndexTodoRequest;
use App\Http\Requests\Todo\StoreTodoRequest;
use App\Http\Requests\Todo\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(IndexTodoRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $todos = $request->user()
            ->todos()
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(($validated['status'] ?? null) && $validated['status'] !== 'all', function ($query, string $status) {
                $query->where('completed', $status === 'completed');
            })
            ->when($validated['priority'] ?? null, function ($query, string $priority) {
                $query->where('priority', $priority);
            })
            ->latest()
            ->get()
            ->map(fn (Todo $todo) => TodoResource::make($todo)->resolve());

        return response()->json($todos);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $todo = $request->user()->todos()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'priority' => $validated['priority'],
            'completed' => false,
        ]);

        return response()->json(TodoResource::make($todo)->resolve(), 201);
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $validated = $request->validated();

        $todo->update($validated);

        return response()->json(TodoResource::make($todo->fresh())->resolve());
    }

    public function destroy(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $todo->delete();

        return response()->json(['message' => 'Todo deleted successfully.']);
    }

    private function authorizeTodo(Request $request, Todo $todo): void
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}
