<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ApiResponser
{

    /**
     * Build success response
     *
     * @param $data
     * @param $code
     *
     */
    public function successResponse($message, $code = Response::HTTP_OK, $data = [])
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ], $code);
    }

    /**
     * Build error response
     *
     * @param $data
     * @param $code
     *
     */
    public function errorResponse($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR, $data = [], $error = [])
    {
        return response()->json([
            'message' => $message,
            'error' => $error,
            'code' => $code,
            'data' => $data
        ], $code);
    }
}
