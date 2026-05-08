<?php
return [
    'driver' => env('QUEUE_DRIVER', 'database'),
    'table'  => env('QUEUE_TABLE', 'jobs'),
];