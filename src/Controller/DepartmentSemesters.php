<?php

namespace MySiga\Controller;

use MySiga\MySigaDepartment;
use MySiga\MySigaException;

class DepartmentSemesters extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return MySigaDepartment::semesters();
    }
}