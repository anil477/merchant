<?php

return [
    'host'         => env('QUEUE_BEANSTALKD_HOST', '127.0.0.1'),
    'trigger_tube' => env('QUEUE_BEANSTALKD_TRIGGER_TUBE', 'default'),
];
