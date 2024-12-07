<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token for authentication
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Set required headers for all requests
        $this->headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept-Version' => 'v1',
        ];
    }

    /**
     * Test fetching a paginated list of authors.
     */
    public function test_can_fetch_paginated_authors()
    {
        Author::factory()->count(15)->create();

        $perPage = 10;
        $response = $this->withHeaders($this->headers)
            ->getJson("/api/authors?per_page={$perPage}");

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('current_page', 1) // Verificar la página actual
                    ->where('per_page', $perPage) // Verificar el número de elementos por página
                    ->where('total', 15) // Verificar el total de elementos creados
                    ->where('last_page', (int) ceil(15 / $perPage)) // Convertir a entero
                    ->has('data', $perPage) // Verificar que haya 10 elementos en la respuesta
                    ->has('data.0.id') // Asegurarse de que los datos incluyen los autores
                    ->has('data.0.name') // Verificar que incluyan el nombre
                    ->etc()
            );
    }


    /**
     * Test fetching a specific author by ID.
     */
    public function test_can_fetch_author_by_id()
    {
        $author = Author::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/authors/{$author->id}");

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('id', $author->id)
                    ->where('name', $author->name)
                    ->where('birthdate', $author->birthdate)
                    ->where('nationality', $author->nationality)
                    ->etc()
            );
    }

    /**
     * Test fetching a non-existent author returns 404.
     */
    public function test_fetching_non_existent_author_returns_404()
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/authors/999');

        $response->assertNotFound()
            ->assertJson(['error' => 'Author not found.']);
    }

    /**
     * Test creating a new author.
     */
    public function test_can_create_author()
    {
        $data = [
            'name' => 'New Author',
            'birthdate' => '1990-01-01',
            'nationality' => 'American',
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/authors', $data);

        $response->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('name', $data['name'])
                    ->where('birthdate', $data['birthdate'])
                    ->where('nationality', $data['nationality'])
                    ->etc()
            );

        $this->assertDatabaseHas('authors', $data);
    }

    /**
     * Test creating an author with invalid data returns validation errors.
     */
    public function test_creating_author_with_invalid_data_returns_errors()
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/authors', []);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Validation failed.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'birthdate' => ['The birthdate field is required.'],
                    'nationality' => ['The nationality field is required.'],
                ],
            ]);
    }

    /**
     * Test updating an existing author.
     */
    public function test_can_update_author()
    {
        $author = Author::factory()->create();
        $data = [
            'name' => 'Updated Author',
            'birthdate' => '1990-01-01', // Campo requerido
            'nationality' => 'Updated Nationality' // Campo requerido
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/authors/{$author->id}", $data);

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('id', $author->id)
                    ->where('name', $data['name'])
                    ->where('birthdate', $data['birthdate'])
                    ->where('nationality', $data['nationality'])
                    ->etc()
            );

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => $data['name'],
            'birthdate' => $data['birthdate'],
            'nationality' => $data['nationality']
        ]);
    }
    /**
     * Test updating a non-existent author returns 404.
     */
    public function test_updating_non_existent_author_returns_404()
    {
        $data = ['name' => 'Non-Existent Author'];

        $response = $this->withHeaders($this->headers)
            ->putJson('/api/authors/999', $data);

        $response->assertNotFound()
            ->assertJson(['error' => 'Author not found.']);
    }

    /** 
     * Test deleting an author.
     */
    public function test_can_delete_author()
    {
        $author = Author::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/authors/{$author->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Author deleted successfully.']);

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    /**
     * Test deleting a non-existent author returns 404. tre
     */
    public function test_deleting_non_existent_author_returns_404()
    {
        $response = $this->withHeaders($this->headers)
            ->deleteJson('/api/authors/999');

        $response->assertNotFound()
            ->assertJson(['error' => 'Author not found.']);
    }
}
