<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiVersionMiddleware
{
    /**
     * Supported API versions.
     *
     * @var array<string>
     */
    private array $supportedVersions = ['v1']; // Add more versions as needed

    /**
     * Handle the incoming request and validate the API version.
     *
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Retrieve the 'Accept-Version' header
        $version = $request->header('Accept-Version');

        // If the header is missing, return a 400 Bad Request error
        if (is_null($version)) {
            return $this->errorResponse('The Accept-Version header is missing.');
        }

        // Check if the provided version is in the list of supported versions
        if (!in_array($version, $this->supportedVersions, true)) {
            return $this->errorResponse('The requested API version is not supported.');
        }

        // Continue the request if the version is valid
        return $next($request);
    }

    /**
     * Generate a JSON error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json(['error' => $message], $statusCode);
    }
}
