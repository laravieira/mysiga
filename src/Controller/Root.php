<?php

namespace MySiga\Controller;

use DateTime;
use DateTimeInterface;
use const MySiga\MYSIGA_DOCS;
use const MySiga\MYSIGA_GITHUB;
use const MySiga\MYSIGA_LICENSE;
use const MySiga\MYSIGA_NAME;
use const MySiga\MYSIGA_REDIRECT;
use const MySiga\MYSIGA_VERSION;

class Root implements Controller
{
    public static function execute(string $uri = '/', array $params = []): array
    {
        return array(
            'name'    => MYSIGA_NAME,
            'version' => MYSIGA_VERSION,
            'siga'    => MYSIGA_REDIRECT,
            'github'  => MYSIGA_GITHUB,
            'docs'    => MYSIGA_DOCS,
            'license' => MYSIGA_LICENSE,
            'date'    => (new Datetime('now'))->format(DateTimeInterface::RSS)
        );
    }
}