<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserUpdateContact extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        $tel   = !isset($_POST['telephone']) ?null:$_POST['telephone'];
        $cel   = !isset($_POST['celphone'])  ?null:$_POST['celphone'];
        $email = !isset($_POST['email'])     ?null:$_POST['email'];

        return MySigaUser::updateContact($tel, $cel, $email);
    }
}