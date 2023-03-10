<?php

namespace MySiga\Controller;

use DateTime;
use DateTimeInterface;
use const MySiga\MYSIGA_NAME;
use const MySiga\MYSIGA_REDIRECT;
use const MySiga\MYSIGA_VERSION;

class Ping implements Controller
{
    public static function execute(string $uri = '/', array $params = []): array
    {
        return array(
            'ping'    => 'pong',
            'name'    => MYSIGA_NAME,
            'version' => MYSIGA_VERSION,
            'siga'    => MYSIGA_REDIRECT,
            'date'    => (new Datetime('now'))->format(DateTimeInterface::RSS)
        );
    }
}