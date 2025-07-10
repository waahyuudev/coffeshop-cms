<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ResponseHelper
{
    /**
     * Generate a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success(mixed $data, string $message = 'Success', int $code = 200, ?int $total = null): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        if (!is_null($total)) {
            $response['total'] = $total;
        }

        return response()->json($response, $code);
    }

    /**
     * Generate an error response.
     *
     * @param string $message
     * @param int $code
     * @param array|null $errors
     * @return JsonResponse
     */
    public static function error(string $message, int $code = 500, array $errors = null): JsonResponse
    {
        // Log the error for debugging
        Log::error($message, $errors ?? []);

        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
