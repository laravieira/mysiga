<?php

namespace MySiga\Controller;

use MySiga\MySigaException;
use MySiga\MySigaUser;

class UserUpdateAddress implements Controller
{
    /**
     * @throws MySigaException
     */
    public static function execute(string $uri = '/', array $params = []): array
    {
        $cep        = empty($_POST['cep'])        ?null:$_POST['cep'];
        $address    = empty($_POST['address'])    ?null:$_POST['address'];
        $number     = (empty($_POST['number']) || intval($_POST['number']) < 1)?null:intval($_POST['number']);
        $complement = empty($_POST['complement']) ?null:$_POST['complement'];
        $district   = empty($_POST['district'])   ?null:$_POST['district'];
        $city       = empty($_POST['city'])       ?null:$_POST['city'];
        $state      = empty($_POST['state'])      ?null:$_POST['state'];

        return MySigaUser::updateAddress($cep, $address, $number, $complement, $district, $city, $state);
    }
}