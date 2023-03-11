<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserUpdatePISPASEP implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        $code = !isset($_POST['code'])?null:$_POST['code'];

        return MySigaUser::updatePISPASEP($code);
    }
}