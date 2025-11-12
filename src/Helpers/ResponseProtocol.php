<?php

namespace Arkenstone\Core\Helpers;

use Arkenstone\Core\Support\Event;

class ResponseProtocol
{
    public static function success($data = null, $message = null, $code = 200)
    {
        Event::dispatch('response.success', [$data, $message, $code]);
        return response()->json(self::formatResponse("success", $data, null, $message), $code);
    }

    public static function error($errors = null, $message = null, $code = 400)
    {
        Event::dispatch('response.error', [$errors, $message, $code]);
        return response()->json(self::formatResponse("error", null, $errors, $message), $code);
    }

    private static function formatResponse($status = "success", $data = null, $errors = null, $message = null)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];
        if ($data) {
            $response['data'] = $data;
        }
        if ($errors) {
            $response['errors'] = $errors;
        }
        return $response;
    }
}
