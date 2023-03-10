<?php

namespace MySiga\Controller;

use MySiga\MySigaAcademic;
use MySiga\MySigaException;

class AcademicPreRegistration extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        return MySigaAcademic::preRegistration();
    }
}