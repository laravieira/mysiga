<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserUpdateEducation implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        $education = !isset($_POST['education'])?null:intval($_POST['education']);

        return MySigaUser::updateEducation($education);
    }
}