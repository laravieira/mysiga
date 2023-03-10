<?php

namespace MySiga\Controller;

use MySiga\MySigaDepartment;
use MySiga\MySigaException;

class DepartmentRoom implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($params['room']) || intval($params['room']) < 1)
            throw new MySigaException('No valid room code.');
        return MySigaDepartment::room(intval($params['room']));
    }
}