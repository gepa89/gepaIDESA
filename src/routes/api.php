<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\AuthController as AuthControllerV1;
use App\Http\Controllers\V1\AuthorController as AuthorControllerV1;
use App\Http\Controllers\V1\BookController as BookControllerV1;

// Authentication routes
/**
 * @group Authentication
 *
 * Endpoints related to user authentication.
 */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthControllerV1::class, 'register']);
    Route::post('/login', [AuthControllerV1::class, 'login']);
});

// Protected routes that require authentication
/**
 * @group Protected Routes
 *
 * Endpoints that are protected by authentication.
 *
 * @middleware auth:sanctum This middleware ensures that only authenticated users can access these routes.
 * @response 401 {
 *   "message": "You are not authenticated. Please log in."
 * }
 */
Route::middleware('auth:sanctum')->group(function () {
    // Version-based route selection
    /**
     * @group API Versioning
     *
     * Middleware to ensure that endpoints are compatible with the specified API version.
     */
    Route::middleware(['api_version'])->group(function () {
        /**
         * Manage authors.
         *
         * CRUD operations for authors (create, list, update, and delete).
         *
         * @group Authors
         * @middleware auth:sanctum
         * @response 401 {
         *   "message": "You are not authenticated. Please log in."
         * }
         */
        Route::apiResource('authors', AuthorControllerV1::class);

        /**
         * Manage books.
         *
         * CRUD operations for books (create, list, update, and delete).
         *
         * @group Books
         * @middleware auth:sanctum
         * @response 401 {
         *   "message": "You are not authenticated. Please log in."
         * }
         */
        Route::apiResource('books', BookControllerV1::class);
    });
});
