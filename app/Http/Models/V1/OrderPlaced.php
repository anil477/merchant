<?php

namespace App\Http\Models\V1;

use Psr\Log\LoggerInterface;
use App\Events\V1\OrderPlacedNotification;
use Illuminate\Contracts\Events\Dispatcher;

class OrderPlaced
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $log, Dispatcher $dispatch)
    {
        $this->logger      = $log;
        $this->dispatcher  = $dispatch;
    }

    /**
     * @param string $uID
     * @param string $orderId
     * @return collection
     * @throws InvalidUserException
     * @throws InvalidOrderException
     */
    public function handle($uID, $orderId)
    {
        // @todo: to prevent multiple events for the same task being queued
        // store the hash for $uId.$orderId in redis for like 24 hours

        // Dispatch the task
        $this->dispatcher->dispatch(new OrderPlacedNotification($uID, $orderId));
        $this->logger->info('order.placed.mail.notification.queued', ['uID' => $uID, 'orderId' => $orderId]);
    }
}
