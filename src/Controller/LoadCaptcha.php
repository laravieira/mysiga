<?php

namespace MySiga\Controller;

use MySiga\MySiga;
use MySiga\MySigaException;

class LoadCaptcha implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return (new MySiga())->begin(true);
    }
}