<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class User implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return MySigaUser::user();
    }
}