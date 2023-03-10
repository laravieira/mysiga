<?php

namespace MySiga\Controller;

interface Controller
{
    public static function execute(string $uri = '/', array $params = []): array;
}