<?php

namespace MySiga\Controller;

use MySiga\MySigaException;

class NotFound extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return throw new MySigaException('Not Found', 404);
    }
}