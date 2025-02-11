<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('システムエラー: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        });
    }

    /**
     * カスタムエラーレスポンスを返す
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        // APIリクエストかどうかを判定
        if ($request->expectsJson()) {
            Log::error("APIエラー: " . $exception->getMessage(), [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'exception' => $exception,
            ]);

            return response()->json([
                'error' => 'サーバーエラーが発生しました。',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
