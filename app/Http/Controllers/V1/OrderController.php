<?php

namespace App\Http\Controllers\V1;

use App\Helpers\JsendResponse;
use App\Http\Models\V1\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OrderRequest;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderPlaced $orderPlaced)
    {
        $this->orderPlaced  = $orderPlaced;
    }

    public function accepted(OrderRequest $request)
    {
        $data       = $request->all();
        $details    = $this->orderPlaced->handle($data['u_id'], $data['order_id']);

        /*
         * @todo: Use Transformer for response: https://github.com/spatie/laravel-fractal
         */
        return JsendResponse::make(null, 'User will be notified shortly');
    }
}
