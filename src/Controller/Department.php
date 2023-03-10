<?php

namespace MySiga\Controller;

use DateTime;
use MySiga\MySigaDepartment;
use MySiga\MySigaException;

class Department implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($params['id']) || intval($params['id']) < 1)
            throw new MySigaException('No valid department code.');
        $year = intval((new DateTime('now'))->format('Y'));
        if(isset($params['y']) && (strlen($params['y']) > 4  || intval($params['y']) > ($year+1)))
            throw new MySigaException('No valid year.');
        if(isset($params['s']) && (strlen($params['s']) != 1 || intval($params['s']) > 4))
            throw new MySigaException('No valid semester.');
        $year = isset($params['y'])?intval($params['y']):null;
        $semester = $params['s'] ?? null;
        return MySigaDepartment::department(intval($params['id']), $year, $semester);
    }
}