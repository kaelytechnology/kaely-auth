<?php

namespace Kaely\Auth\Exceptions;

use Exception;

class KaelyAuthException extends Exception
{
    /**
     * The exception code.
     */
    protected $code = 500;

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = '', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report()
    {
        \Illuminate\Support\Facades\Log::error('KaelyAuth Exception: ' . $this->getMessage(), [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString()
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'KaelyAuth Error',
                'message' => $this->getMessage(),
                'code' => $this->getCode()
            ], $this->getCode() ?: 500);
        }

        return response()->view('errors.kaely-auth', [
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ], $this->getCode() ?: 500);
    }
} 