<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaLogin;

class LoginLogout implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return MySigaLogin::logout();
    }
}