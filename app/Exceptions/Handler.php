<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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

    public function render($request, Throwable $exception)
    {
        // This will replace our 404 response with a JSON response.
        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Data Not Found',
                'data' => (object)[],
            ], 404);
        }
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Data Not Found',
                'data' => (object)[],
            ], 404);
    });
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found.',
                    'data' => []
                ], 404);
        });
        if ($exception instanceof MethodNotAllowedHttpException && $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Method Not Allowed.',
                'data' => (object)[],
            ], 405);
        }
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Method not Allowed.',
                'data' => []
            ], 405);
    });
    $this->renderable(function (Throwable $e, Request $request) {
        if ($e instanceof QueryException && $e->errorInfo[1] === 1062) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate Data',
                'data' => (object)[],
            ], 422);
        }
    
        // return response()->json([
        //     'success' => false,
        //     'message' => 'Internal Server Error',
        //     'data' => []
        // ], 500);
    });
        return parent::render($request, $exception);
    }
    
    
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
}
