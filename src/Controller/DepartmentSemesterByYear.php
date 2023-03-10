<?php

namespace MySiga\Controller;

use DateTime;
use MySiga\MySigaDepartment;
use MySiga\MySigaException;

class DepartmentSemesterByYear implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        $year = intval((new DateTime('now'))->format('Y'));
        if(!isset($params['y']) || strlen($params['y']) > 4  || intval($params['y']) > ($year+1))
            throw new MySigaException('No valid year.');
        if(!isset($params['s']) || strlen($params['s']) != 1 || intval($params['s']) > 4)
            throw new MySigaException('No valid semester.');
        return MySigaDepartment::semesterByYear(intval($params['y']), intval($params['s']));
    }
}