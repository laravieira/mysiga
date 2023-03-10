<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaLogin;

class LoginRaw extends Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        if(!isset($_POST['cpf']) || strlen($_POST['cpf']) != 11)
            throw new MySigaException('No valid CPF.');
        if(!isset($_POST['response']) || strlen($_POST['response']) < 8)
            throw new MySigaException('No valid password.');
        if(isset($_POST['captcha']) && strlen($_POST['captcha']) < 5)
            throw new MySigaException('No valid captcha.');
        $captcha = $_POST['captcha'] ?? null;
        return MySigaLogin::rawLogin($_POST['cpf'], $_POST['response'], $captcha);
    }
}