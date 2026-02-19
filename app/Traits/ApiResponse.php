<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        $data = null,
        string $message = 'Success',
        int $code = 200,
        $meta = null
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
            'meta'    => $meta,
        ], $code);
    }

    protected function error(
        string $message = 'Error',
        $errors = null,
        int $code = 400
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'meta'    => null,
        ], $code);
    }

    /**
     * Clean pagination response
     */
    protected function paginate(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $message = 'Success'
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $resourceClass::collection($paginator),
            'errors'  => null,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], 200);
    }
}
