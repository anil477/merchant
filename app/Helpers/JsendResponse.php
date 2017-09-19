<?php

namespace App\Helpers;

use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;

/**
 * This is a response class that conforms to the JSend standard
 * @link          http://labs.omniti.com/labs/jsend
 */
class JsendResponse extends Response
{

    /**
     * Status of the API call; success, fail, or error
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $message;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @var int
     */
    protected $code;

    public function __construct($data = null, $message = null, $code = 200, $status = 'success')
    {
        $this->data = $data;
        if ($message instanceof MessageBag) {
            $this->message = $message->first();
        } else {
            $this->message = $message;
        }
        $this->code = $code;
        if ($this->code >= 200 and $this->code < 300) {
            $this->status = 'success';
        } elseif ($this->code >= 400 and $this->code < 500) {
            $this->status = 'fail';
        } elseif ($this->code >= 500) {
            $this->status = 'error';
        } else {
            $this->status = $status;
        }
        parent::__construct(array_only((array) $this, ['data', 'message', 'status']), $code);
    }

    /**
     * @param        $data
     * @param        $message
     * @param        $code
     * @param string $status
     *
     * @return static
     */
    public static function make($data, $message = null, $code = 200, $status = 'success')
    {
        return new static($data, $message, $code, $status);
    }
}
