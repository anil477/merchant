<?php

namespace App\Exceptions;

use Exception;
use Psr\Log\LoggerInterface;
use Illuminate\Http\Request;
use App\Helpers\JsendResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * @var int $code
     */
    private $code = 500;

    /**
     * @var string $message
     */
    private $message = 'Whoops, looks like something went wrong';

    /**
     * @var $error
     */
    private $error;

    /**
     * @var $request
     */
    private $request;

    /**
     * @var array $validationErrors
     */
    private $validationErrors   = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Handler constructor.
     * @param LoggerInterface $log
     * @param Request $request
     */
    public function __construct(Container $container, LoggerInterface $log, Request $request)
    {
        parent::__construct($container);
        $this->log     = $log;
        $this->request = $request;
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    public function report(Exception $e)
    {
        $this->setTrackingCode(
            $trackingCode = strtoupper(substr(hash('sha256', openssl_random_pseudo_bytes(64)), 0, 7))
        );

        // Set the Exception message
        $exceptionMessage = $e->getMessage();
        $route            = $this->request->route();

        // Set up an array that will be logged with every log entry
        $logArray = [
            'http'               => [
                'remote_ip' => $this->request->ip(),
                'host'      => $this->request->getHost(),
                'method'    => $this->request->getMethod(),
                'url'       => $this->request->fullUrl(),
                'body'      => $this->request->getContent(),
                'inputs'    => $this->request->all(),
                'headers'   => $this->request->header(),
            ],
            'realm'              => env('APP_ENV'),
            'tracking_code'      => $trackingCode,
            'exception'          => $e,
            'file'               => $e->getFile(),
            'line'               => $e->getLine(),
            'http_response_code' => 500
        ];

        if (! $e instanceof StandardizedErrorResponseException) {
            $this->log->critical($exceptionMessage, $logArray);
            // Send all errors to the apache log file
            error_log($e);

            return;
        }

        // Log the HTTP response code with the log array
        $exceptionCode                  = $e->getCode();
        $logArray['http_response_code'] = $exceptionCode;

        // Log the error based on response code
        switch ($exceptionCode) {
            case ($exceptionCode < 300):
                $this->log->notice($exceptionMessage, $logArray);
                break;
            case ($exceptionCode >= 300 && $exceptionCode < 400):
                $this->log->warning($exceptionMessage, $logArray);
                break;
            case ($exceptionCode >= 400 && $exceptionCode < 500):
                $this->log->error($exceptionMessage, $logArray);
                break;
            case ($exceptionCode >= 500):
                $this->log->critical($exceptionMessage, $logArray);
                break;
            default:
                $this->log->error($exceptionMessage, $logArray);
                break;
        }

        // Send all errors to the apache log file
        error_log($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request    $request
     * @param  \Exception $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $this->request  = $request;
        $this->error    = $e;
        $this->code     = $this->setStatusCode();

        if (method_exists($this->error, 'getValidationErrors')) {
            $this->validationErrors = $this->error->getValidationErrors();
        }

        if ($this->error instanceof ModelNotFoundException) {
            $this->error = new NotFoundHttpException($this->error->getMessage(), $this->error);
        }

        return JsendResponse::make($this->getDebugInfo(), $this->getMessage(), $this->code);
    }

    /**
     * @param string $trackingCode
     */
    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;
    }

    /**
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * @return int|string
     */
    private function setStatusCode()
    {
        if ($this->error instanceof HttpException) {
            return $this->error->getStatusCode();
        }

        if ($this->error->getCode() >= 100 && $this->error->getCode() <= 600) {
            return $this->error->getCode();
        }

        return 500;
    }

    /**
     * Custom messages for certain error codes
     *
     * @return string
     */
    private function getMessage()
    {
        if (! empty($this->error->getMessage())) {
            $this->message  = $this->error->getMessage();
        }

        return $this->message;
    }


    /**
     * Returns the debug info only if app environment is not production
     *
     * @return array
     */
    public function getDebugInfo()
    {
        $info   = [
            'validation'        => $this->validationErrors
        ];

        $info['http']   = [
            'host'          => $this->request->getHost(),
            'method'        => $this->request->getMethod(),
            'url'           => $this->request->fullUrl(),
            'body'          => $this->request->getContent(),
            'inputs'        => $this->request->all(),
            'headers'       => $this->request->header()
        ];

        $info['debug']   = [
            'realm'         => env('APP_ENV'),
            'exception'     => get_class($this->error),
            'file'          => $this->error->getFile(),
            'line'          => $this->error->getLine(),
            'stack_trace'   => $this->error->getTrace()
        ];

        return $info;
    }
}
