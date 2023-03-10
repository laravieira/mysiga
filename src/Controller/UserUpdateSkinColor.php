<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserUpdateSkinColor extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($_POST['color']) || intval($_POST['color']) < 1)
            throw new MySigaException('No valid skin color option code.');
        return MySigaUser::setSkinColor(intval($_POST['color']));
    }
}