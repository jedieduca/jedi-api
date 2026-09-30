<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Credenciais do banco vêm do ambiente (.env -> docker-compose.yml). Ver Classes/DB/MySQL.php

define('DS', DIRECTORY_SEPARATOR);
define('DIR_APP', __DIR__ . DS);

define('DIR_PROJETO', getenv('DIR_PROJETO') ?: '');

if (file_exists('autoload.php')) {
    include 'autoload.php';
} else {
    die('Arquivo de autoload nao encontrado');
}

// Dependências do Composer (ex.: PHPMailer). A pasta vendor/ fica fora da raiz pública (api/).
$composerAutoload = dirname(__DIR__) . DS . 'vendor' . DS . 'autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}