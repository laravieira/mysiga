<?php

namespace MySiga\Controller;

use MySiga\MySigaAcademic;
use MySiga\MySigaException;

class AcademicSchedule extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(isset($params['code']) && strlen($params['code'])>5 && strlen($params['code'])<15)
            return MySigaAcademic::schedule($params['code']);
        else
            throw new MySigaException('No valid class code.');
    }
}