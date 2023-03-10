<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserCEP implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($params['cep']) || strlen($params['cep']) != 8)
            throw new MySigaException('No valid cep code.');
        return MySigaUser::cep($params['cep']);
    }
}