<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $todos = $request->user()
            ->todos()
            ->latest()
            ->get()
            ->map(fn (Todo $todo) => $this->formatTodo($todo));

        return response()->json($todos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:Low,Medium,High'],
        ]);

        $todo = $request->user()->todos()->create([
            'title' => trim($validated['title']),
            'description' => trim($validated['description'] ?? ''),
            'priority' => $validated['priority'],
            'completed' => false,
        ]);

        return response()->json($this->formatTodo($todo), 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'required', 'in:Low,Medium,High'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('title', $validated)) {
            $validated['title'] = trim($validated['title']);
        }

        if (array_key_exists('description', $validated)) {
            $validated['description'] = trim($validated['description'] ?? '');
        }

        $todo->update($validated);

        return response()->json($this->formatTodo($todo->fresh()));
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

    private function formatTodo(Todo $todo): array
    {
        return [
            'id' => $todo->id,
            'title' => $todo->title,
            'description' => $todo->description ?? '',
            'priority' => $todo->priority,
            'completed' => $todo->completed,
            'createdAt' => $todo->created_at->diffForHumans(),
        ];
    }
}
