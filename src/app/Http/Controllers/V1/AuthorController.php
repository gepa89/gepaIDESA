<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Author;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthorController extends Controller
{
    // Common error messages
    private const ERR_AUTHOR_NOT_FOUND = 'Author not found.';
    private const ERR_FETCH_AUTHORS = 'An error occurred while fetching authors.';
    private const ERR_FETCH_AUTHOR = 'An error occurred while fetching the author.';
    private const ERR_CREATE_AUTHOR = 'An error occurred while creating the author.';
    private const ERR_UPDATE_AUTHOR = 'An error occurred while updating the author.';
    private const ERR_DELETE_AUTHOR = 'An error occurred while deleting the author.';
    private const SUCCESS_DELETE_AUTHOR = 'Author deleted successfully.';

    /**
     * Get a paginated, filtered, and sorted list of authors.
     *
     * @group Authors
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     *
     * @urlParam per_page int Optional. Number of authors per page. Defaults to 10. Example: 20
     * @urlParam name string Optional. Filter by author name. Example: John
     * @urlParam email string Optional. Filter by author email. Example: john@example.com
     * @urlParam order_by string Optional. Field to sort by. Defaults to "id". Example: name
     * @urlParam order_dir string Optional. Sorting direction (asc or desc). Defaults to "asc". Example: desc
     *
     * @response 200 {
     *   "data": [
     *       {
     *           "id": 1,
     *           "name": "Author Name",
     *           "email": "author@example.com",
     *           "birthdate": "1980-01-01",
     *           "nationality": "American"
     *       }
     *   ],
     *   "links": {
     *       "first": "http://example.com/api/v1/authors?page=1",
     *       "last": "http://example.com/api/v1/authors?page=10",
     *       "prev": null,
     *       "next": "http://example.com/api/v1/authors?page=2"
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
     *   "message": "An error occurred while fetching authors."
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
            $filters = $request->only(['name', 'email']);
            $orderBy = $request->input('order_by', 'id');
            $orderDir = $request->input('order_dir', 'asc');

            // Validate sorting direction
            if (!in_array($orderDir, ['asc', 'desc'])) {
                return $this->errorResponse('Invalid order direction. Use "asc" or "desc".', 400);
            }

            // Build query with filters
            $query = Author::query();
            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    $query->where($field, 'like', "%$value%");
                }
            }

            // Apply sorting
            $query->orderBy($orderBy, $orderDir);

            // Paginate results
            $authors = $query->paginate($perPage);

            return $this->successResponse($authors);
        } catch (ValidationException $exception) {
            return $this->errorResponse('Invalid pagination or sorting parameters.', 400);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_FETCH_AUTHORS, 500);
        }
    }


    /**
     * Get details of a specific author.
     *
     * @group Authors
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Author ID.
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Author Name",
     *   "birthdate": "1980-01-01",
     *   "nationality": "American"
     * }
     * @response 404 {
     *   "message": "Author not found."
     * }
     * @response 500 {
     *   "message": "An error occurred while fetching the author."
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $author = Author::findOrFail($id);
            return $this->successResponse($author);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_AUTHOR_NOT_FOUND, 404);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_FETCH_AUTHOR, 500);
        }
    }

    /**
     * Create a new author.
     *
     * @group Authors
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     *
     * @bodyParam name string required The name of the author. Example: John Doe
     * @bodyParam birthdate date required The birthdate of the author. Example: 1980-01-01
     * @bodyParam nationality string required The nationality of the author. Example: American
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "birthdate": "1980-01-01",
     *   "nationality": "American"
     * }
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *       "name": ["The name field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "An error occurred while creating the author."
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validateAuthorData($request);
            $author = Author::create($validatedData);
            return $this->successResponse($author, 201);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_CREATE_AUTHOR, 500);
        }
    }

    /**
     * Update an existing author.
     *
     * @group Authors
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Author ID.
     *
     * @bodyParam name string Optional The updated name of the author. Example: Jane Doe
     * @bodyParam birthdate date Optional The updated birthdate of the author. Example: 1990-01-01
     * @bodyParam nationality string Optional The updated nationality of the author. Example: British
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Jane Doe",
     *   "birthdate": "1990-01-01",
     *   "nationality": "British"
     * }
     * @response 404 {
     *   "message": "Author not found."
     * }
     * @response 422 {
     *   "message": "Validation failed.",
     *   "errors": {
     *       "name": ["The name field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "An error occurred while updating the author."
     * }
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $author = Author::findOrFail($id);
            $validatedData = $this->validateAuthorData($request, $author->id);
            $author->update($validatedData);
            return $this->successResponse($author);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_AUTHOR_NOT_FOUND, 404);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_UPDATE_AUTHOR, 500);
        }
    }

    /**
     * Delete an author.
     *
     * @group Authors
     *
     * @header Authorization string required The authorization token. Example: Bearer {YOUR_AUTH_TOKEN}
     * @header Content-Type string required The format of the request body. Example: application/json
     * @header Accept-Version string required The version of the API to use. Example: v1
     * 
     * @urlParam id int required Author ID.
     *
     * @response 200 {
     *   "message": "Author deleted successfully."
     * }
     * @response 404 {
     *   "message": "Author not found."
     * }
     * @response 500 {
     *   "message": "An error occurred while deleting the author."
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $author = Author::findOrFail($id);
            $author->delete();
            return $this->successResponse(['message' => self::SUCCESS_DELETE_AUTHOR]);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse(self::ERR_AUTHOR_NOT_FOUND, 404);
        } catch (Throwable $exception) {
            return $this->errorResponse(self::ERR_DELETE_AUTHOR, 500);
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

        $perPage = $request->get('per_page', 10);
        return (int) $perPage;
    }


    /**
     * Validate author data.
     *
     * @param Request $request
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    private function validateAuthorData(Request $request, ?int $id = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'nationality' => 'required|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return (array) $validator->validated();
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
