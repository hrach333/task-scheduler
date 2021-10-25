<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestCreateTaskTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $response = $this->json('POST', '/api/login', ['email' => 'test@test.ru', 'password' => 'password']);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                'success' => true,
            ]);
    }
}
