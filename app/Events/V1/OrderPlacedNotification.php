<?php

namespace App\Events\V1;

use App\Events\Event;

class OrderPlacedNotification extends Event
{
    private $uID;

    private $orderId;

    public function __construct($uID, $orderId)
    {
        $this->uID     = $uID;
        $this->orderId = $orderId;
    }

    public function getUserId()
    {
        return $this->uID;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }
}
