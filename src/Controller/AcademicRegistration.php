<?php

namespace MySiga\Controller;

use MySiga\MySigaAcademic;
use MySiga\MySigaException;

class AcademicRegistration extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(isset($params['view'])) {
            if($params['view'] == 'download')
                return MySigaAcademic::registration(false, true);
            elseif($params['view'] == 'browser')
                return MySigaAcademic::registration(false);
            else
                return MySigaAcademic::registration();
        }
        return MySigaAcademic::registration();
    }
}