<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaLogin;

class LoginChange extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($_POST['oldpassword']) || strlen($_POST['oldpassword']) < 8)
            throw new MySigaException('Old password is invalid.');
        if(!isset($_POST['password']))
            throw new MySigaException('New password is invalid.');

        $uc = preg_match('@[A-Z]@', $_POST['password']);
        $lc = preg_match('@[a-z]@', $_POST['password']);
        $nb = preg_match('@[0-9]@', $_POST['password']);

        if(!$uc || !$lc || !$nb || strlen($_POST['password']) < 8)
            throw new MySigaException('New password is too easy.');

        return MySigaLogin::updatePassword($_POST['oldpassword'], $_POST['password']);
    }
}