<?php

namespace App\Http\Listeners\V1;

use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as Guzzle;
use App\Events\V1\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPlacedNotifyViaSms implements ShouldQueue
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
        $details = $this->fetchSMSContent();

        $requestBody = [
                'number'    => $details['number'],
                'body'      => $details['body'],
                'u_id'      => $event->getUserId(),
                'order_id'  => $event->getOrderId(),
                'media'     => $details['media']
        ];

        try {
            $request = $this->guzzle->post(app()->make('config')->get('sms_service.host'), [
            'form_params' => $requestBody
            ]);

            $response = json_decode($request->getBody(), true);
            $this->logger->info('sms.service.response', [$requestBody, $response]);
        } catch (Exception $e) {
            $this->logger->error('sms.service.exception', ['error_message' => $e->getMessage()]);
        }
    }

    private function fetchSMSContent()
    {
        return collect([
            'number' => "9873998765",
            'body'   => 'Your order is on your way!',
            'media'  => 'https://www.goibibo.com/'
        ]);

        // @todo: fetch the SMS details for user
    }
}
