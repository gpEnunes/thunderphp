<?php
namespace Thunder\Error;
use Thunder\Exceptions\HttpException;
class ErrorHandler
{
    public function __construct(private readonly bool $debug = false){}
    public function register(): void
    {
        set_exception_handler([$this, 'handle']);
    }
    public function handle(\Throwable $e):void
    {
        $statusCode = $e instanceof HttpException
            ? $e->getStatusCode()
            : 500;
        $response = [
            'error' => true,
            'message' => $e->getMessage() ?: 'Server Error',
        ];

        if($this->debug){
            $response['trace'] = $e->getTrace();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}