<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaLogin;

class Login implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($_POST['cpf']) || strlen($_POST['cpf']) != 11)
            throw new MySigaException('No valid CPF.', 400);
        if(!isset($_POST['password']) || strlen($_POST['password']) < 8)
            throw new MySigaException('No valid password.', 400);
        if(isset($_POST['captcha']) && !intval($_POST['captcha']))
            throw new MySigaException('No valid captcha.', 400);
        $captcha = isset($_POST['captcha']) ? intval($_POST['captcha']) : null;
        return MySigaLogin::login($_POST['cpf'], $_POST['password'], $captcha);
    }
}