<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndTodoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_returns_token_and_user_payload(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ayesha',
            'email' => 'ayesha@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'token',
            'user' => ['name', 'email'],
        ]);
    }

    public function test_login_returns_token_and_user_payload(): void
    {
        User::factory()->create([
            'name' => 'Ayesha',
            'email' => 'ayesha@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ayesha@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'token',
            'user' => ['name', 'email'],
        ]);
    }

    public function test_authenticated_user_can_manage_todos_with_clean_json_resources(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/todos', [
            'title' => 'Prepare demo',
            'description' => 'Polish the candidate presentation',
            'priority' => 'High',
        ]);

        $createResponse->assertCreated();
        $createResponse->assertJsonFragment([
            'title' => 'Prepare demo',
            'priority' => 'High',
            'completed' => false,
        ]);

        $todoId = Todo::query()->where('user_id', $user->id)->value('id');

        $indexResponse = $this->getJson('/api/todos');

        $indexResponse->assertOk();
        $indexResponse->assertJsonFragment([
            'id' => $todoId,
            'title' => 'Prepare demo',
        ]);
    }

    public function test_authenticated_user_can_search_and_filter_todos_from_the_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Todo::query()->create([
            'user_id' => $user->id,
            'title' => 'Prepare dashboard demo',
            'description' => 'Focus on the task search flow',
            'priority' => 'High',
            'completed' => false,
        ]);

        Todo::query()->create([
            'user_id' => $user->id,
            'title' => 'Ship release notes',
            'description' => 'Mark this as completed',
            'priority' => 'Medium',
            'completed' => true,
        ]);

        $response = $this->getJson('/api/todos?search=demo&status=pending&priority=High');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'title' => 'Prepare dashboard demo',
            'priority' => 'High',
            'completed' => false,
        ]);
    }
}
