<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario y generar un token para autenticación
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Configurar headers requeridos para todas las solicitudes
        $this->headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept-Version' => 'v1',
        ];
    }

    /**
     * Test fetching a paginated list of books.
     */
    public function test_can_fetch_paginated_books()
    {
        // Crear autores con libros asociados
        Author::factory()
            ->count(10)
            ->hasBooks(1) // Genera 1 libro por autor
            ->create();

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/books?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'isbn',
                        'published_date',
                        'author_id',
                        'created_at',
                        'updated_at',
                        'author' => [
                            'id',
                            'name',
                            'birthdate',
                            'nationality',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
    }


    /**
     * Test fetching a specific book by ID.
     */
    public function test_can_fetch_book_by_id()
    {
        $book = Book::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/books/{$book->id}");

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('id', $book->id)
                    ->where('title', $book->title)
                    ->where('isbn', $book->isbn)
                    ->where('published_date', $book->published_date->format('Y-m-d\TH:i:s.u\Z')) // Formato ajustado
                    ->where('author_id', $book->author_id)
                    ->where('created_at', $book->created_at->format('Y-m-d\TH:i:s.u\Z'))
                    ->where('updated_at', $book->updated_at->format('Y-m-d\TH:i:s.u\Z'))
                    ->has('author')
            );
    }

    /**
     * Test fetching a non-existent book returns 404.
     */
    public function test_fetching_non_existent_book_returns_404()
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/books/999');

        $response->assertNotFound()
            ->assertJson(['error' => 'Book not found.']);
    }

    /**
     * Test creating a new book.
     */
    public function test_can_create_book()
    {
        $author = Author::factory()->create();

        $data = [
            'title' => 'New Book',
            'isbn' => '123-4567890123',
            'published_date' => '2022-01-01',
            'author_id' => $author->id,
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/books', $data);

        $response->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('title', $data['title'])
                    ->where('isbn', $data['isbn'])
                    ->where('author_id', $data['author_id'])
                    ->etc()
            );

        $this->assertDatabaseHas('books', $data);
    }

    /**
     * Test creating a book with invalid data returns validation errors.
     */
    public function test_creating_book_with_invalid_data_returns_errors()
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/books', []);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Validation failed.',
                'errors' => [
                    'title' => ['The title field is required.'],
                    'isbn' => ['The isbn field is required.'],
                    'published_date' => ['The published date field is required.'],
                    'author_id' => ['The author id field is required.'],
                ],
            ]);
    }

    /**
     * Test updating an existing book.
     */
    public function test_can_update_book()
    {
        $book = Book::factory()->create();
        $data = [
            'title' => 'Updated Book Title',
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d\TH:i:s.u\Z'), // Ajustar al formato del endpoint
            'author_id' => $book->author_id,
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/books/{$book->id}", $data);

        $response->assertOk()
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('id', $book->id)
                    ->where('title', $data['title'])
                    ->where('isbn', $data['isbn'])
                    ->where('published_date', $data['published_date']) // Comparación ajustada
                    ->where('author_id', $data['author_id'])
                    ->etc()
            );

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => $data['title']]);
    }

    /**
     * Test updating a non-existent book returns 404.
     */
    public function test_updating_non_existent_book_returns_404()
    {
        $data = ['title' => 'Non-Existent Book'];

        $response = $this->withHeaders($this->headers)
            ->putJson('/api/books/999', $data);

        $response->assertNotFound()
            ->assertJson(['error' => 'Book not found.']);
    }

    /**
     * Test deleting a book.
     */
    public function test_can_delete_book()
    {
        $book = Book::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/books/{$book->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Book deleted successfully.']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * Test deleting a non-existent book returns 404.
     */
    public function test_deleting_non_existent_book_returns_404()
    {
        $response = $this->withHeaders($this->headers)
            ->deleteJson('/api/books/999');

        $response->assertNotFound()
            ->assertJson(['error' => 'Book not found.']);
    }
}
