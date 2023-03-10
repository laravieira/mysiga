<?php

namespace MySiga\Controller;

use DateTime;
use DateTimeInterface;
use const MySiga\MYSIGA_NAME;
use const MySiga\MYSIGA_REDIRECT;
use const MySiga\MYSIGA_VERSION;

class Ping
{
    public static function execute(): array
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