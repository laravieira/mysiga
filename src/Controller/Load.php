<?php

namespace MySiga\Controller;

use MySiga\MySiga;
use MySiga\MySigaException;

class Load implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return (new MySiga())->begin();
    }
}