<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ApiException extends HttpResponseException
{
    public function __construct(int $code, $message)
    {
        $data = [
            'success' => false,
            'code' => $code,
        ];
        if ($message)
            $data['message'] = $message;

        parent::__construct(response($data, $code));
    }
}

