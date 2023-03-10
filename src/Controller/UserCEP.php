<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserCEP extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($get['cep']) || strlen($get['cep']) != 8)
            throw new MySigaException('No valid cep code.');
        return MySigaUser::cep($get['cep']);
    }
}