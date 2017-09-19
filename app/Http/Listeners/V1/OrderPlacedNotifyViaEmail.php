<?php

namespace App\Http\Listeners\V1;

use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as Guzzle;
use App\Events\V1\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPlacedNotifyViaEmail implements ShouldQueue
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Guzzle
     */
    private $guzzle;

    public function __construct(LoggerInterface $log, Guzzle $guzzle)
    {
        $this->logger = $log;
        $this->guzzle = $guzzle;
    }

    public function handle(OrderPlacedNotification $event)
    {
        $requestBody = [
                'u_id'      => $event->getUserId(),
                'order_id'  => $event->getOrderId()
        ];

        try {
            $request = $this->guzzle->post(app()->make('config')->get('mail_service.host'), [
                'form_params' => $requestBody
            ]);

            $response = json_decode($request->getBody(), true);
            $this->logger->info('mail.service.response', [$requestBody, $response]);
        } catch (Exception $e) {
            $this->logger->error('mail.service.exception', ['error_message' => $e->getMessage()]);
        }
    }
}
