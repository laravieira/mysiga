<?php

namespace MySiga\Controller;

use MySiga\MySigaAcademic;
use MySiga\MySigaException;

class AcademicHistory implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return MySigaAcademic::history();
    }
}