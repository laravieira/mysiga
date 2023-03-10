<?php

namespace MySiga\Controller;

use MySiga\MySigaDepartment;
use MySiga\MySigaException;

class DepartmentSemesterById extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($params['id']) || intval($params['id']) < 0 || intval($params['id']) > 1000)
            throw new MySigaException('No valid semester id.');
        return MySigaDepartment::semesterById(intval($params['id']));
    }
}