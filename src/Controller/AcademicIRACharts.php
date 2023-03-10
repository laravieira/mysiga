<?php

namespace MySiga\Controller;

use MySiga\MySigaAcademic;
use MySiga\MySigaException;

class AcademicIRACharts implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(sizeof($params)) {
            $avarege = false;
            $detailed = false;
            if (isset($params['a']) && $params['a'] == 'true')
                $avarege = true;
            if (isset($params['d']) && $params['d'] == 'true')
                $detailed = true;
            return MySigaAcademic::iraChart($avarege, $detailed);
        }
        return MySigaAcademic::iraCharts();
    }
}