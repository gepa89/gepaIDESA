<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BookController extends Controller
{
    // Common error messages
    private const ERR_BOOK_NOT_FOUND = 'Book not found.';
    private const ERR_FETCH_BOOKS = 'An error occurred while fetching books.';
    private const ERR_FETCH_BOOK = 'An error occurred while fetching the book.';
    private const ERR_CREATE_BOOK = 'An error occurred while creating the book.';
    private const ERR_UPDATE_BOOK = 'An error occurred while updating the book.';
    private const ERR_DELETE_BOOK = 'An error occurred while deleting the book.';
    private const SUCCESS_DELETE_BOOK = 'Book deleted successfully.';

    /**
     * Get a paginated, filtered, and sorted list of books.
     *
     * @group Books
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     *
     * @urlParam per_page int Optional. Number of books per page. Defaults to 10. Example: 20
     * @urlParam title string Optional. Filter by book title. Example: Harry Potter
     * @urlParam isbn string Optional. Filter by book ISBN. Example: 123-4567890123
     * @urlParam order_by string Optional. Field to sort by. Defaults to "id". Example: title
     * @urlParam order_dir string Optional. Sorting direction (asc or desc). Defaults to "asc". Example: desc
     *
     * @response 200 {
     *   "data": [
     *       {
     *           "id": 1,
     *           "title": "Book Title",
     *           "isbn": "123-4567890123",
     *           "published_date": "2022-01-01",
     *           "author": {
     *               "id": 1,
     *               "name": "Author Name"
     *           }
     *       }
     *   ],
     *   "links": {
     *       "first": "http://example.com/api/v1/books?page=1",
     *       "last": "http://example.com/api/v1/books?page=10",
     *       "prev": null,
     *       "next": "http://example.com/api/v1/books?page=2"
     *   },
     *   "meta": {
     *       "current_page": 1,
     *       "last_page": 10,
     *       "per_page": 10,
     *       "total": 100
     *   }
     * }
     * @response 400 {
     *   "message": "Invalid pagination or sorting parameters."
     * }
     * @response 500 {
     *   "message": "An error occurred while fetching books."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate pagination parameter
            $perPage = $this->validatePerPage($request);

            // Get filters and sorting parameters from the request
            $filters = $request->only(['title', 'isbn']);
            $orderBy = $request->input('order_by', 'id');
            $orderDir = $request->input('order_dir', 'asc');

            // Validate sorting direction
            if (!in_array($orderDir, ['asc', 'desc'])) {
                return $this->errorResponse('Invalid order direction. Use "asc" or "desc".', 400);
            }

            // Build query with filters
            $query = Book::with('author');
            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    $query->where($field, 'like', "%$value%");
                }
            }

            // Apply sorting
            $query->orderBy($orderBy, $orderDir);

            // Paginate results
            $books = $query->paginate($perPage);

            return $this->successResponse($books);
        } catch (ValidationException $exception) {
            return $this->errorResponse('Invalid pagination or sorting parameters.', 400);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_FETCH_BOOKS, 500);
        }
    }


    /**
     * Get details of a specific book.
     *
     * @group Books
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Book ID.
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Book Title",
     *   "isbn": "123-4567890123",
     *   "published_date": "2022-01-01",
     *   "author": {
     *       "id": 1,
     *       "name": "Author Name"
     *   }
     * }
     * @response 404 {
     *   "message": "Book not found."
     * }
     * @response 500 {
     *   "message": "An error occurred while fetching the book."
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $book = Book::with('author')->findOrFail($id);
            return $this->successResponse($book);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_BOOK_NOT_FOUND, 404);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_FETCH_BOOK, 500);
        }
    }

    /**
     * Create a new book.
     *
     * @group Books
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     *
     * @bodyParam title string required The title of the book. Example: My Book
     * @bodyParam isbn string required The ISBN of the book. Example: 123-4567890123
     * @bodyParam published_date date required The publication date of the book. Example: 2022-01-01
     * @bodyParam author_id int required The ID of the author. Example: 1
     *
     * @response 201 {
     *   "id": 1,
     *   "title": "My Book",
     *   "isbn": "123-4567890123",
     *   "published_date": "2022-01-01",
     *   "author_id": 1
     * }
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *       "title": ["The title field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "An error occurred while creating the book."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validateBookData($request);
            $book = Book::create($validatedData);
            return $this->successResponse($book, 201);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_CREATE_BOOK, 500);
        }
    }

    /**
     * Update an existing book.
     *
     * @group Books
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Book ID.
     *
     * @bodyParam title string Optional The title of the book. Example: Updated Book Title
     * @bodyParam isbn string Optional The updated ISBN of the book. Example: 123-4567890123
     * @bodyParam published_date date Optional The updated publication date of the book. Example: 2022-01-01
     * @bodyParam author_id int Optional The ID of the updated author. Example: 2
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Updated Book Title",
     *   "isbn": "123-4567890123",
     *   "published_date": "2022-01-01",
     *   "author_id": 2
     * }
     * @response 404 {
     *   "message": "Book not found."
     * }
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *       "title": ["The title field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "An error occurred while updating the book."
     * }
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);
            $validatedData = $this->validateBookData($request, $book->id);
            $book->update($validatedData);
            return $this->successResponse($book);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_BOOK_NOT_FOUND, 404);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_UPDATE_BOOK, 500);
        }
    }

    /**
     * Delete a book.
     *
     * @group Books
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Book ID.
     *
     * @response 200 {
     *   "message": "Book deleted successfully."
     * }
     * @response 404 {
     *   "message": "Book not found."
     * }
     * @response 500 {
     *   "message": "An error occurred while deleting the book."
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();
            return $this->successResponse(['message' => self::SUCCESS_DELETE_BOOK]);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_BOOK_NOT_FOUND, 404);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_DELETE_BOOK, 500);
        }
    }

    /**
     * Validate the pagination parameter.
     *
     * @param Request $request
     * @return int
     * @throws ValidationException
     */
    private function validatePerPage(Request $request): int
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return (int) $request->get('per_page', 10);
    }

    /**
     * Validate book data.
     *
     * @param Request $request
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    private function validateBookData(Request $request, ?int $id = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|max:20',
            'published_date' => 'required|date',
            'author_id' => 'required|exists:authors,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Return a JSON success response.
     *
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Return a JSON error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json(['error' => $message], $statusCode);
    }

    /**
     * Return a validation error response.
     *
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $exception->errors(),
        ], 422);
    }
}
