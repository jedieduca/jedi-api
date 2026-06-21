<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

define('HOST', '172.19.0.6');
define('BANCO', 'jedieduca');
define('USER', 'root');
define('SENHA', 'mys2Edu4Up@2025');

define('DS', DIRECTORY_SEPARATOR);
define('DIR_APP', __DIR__ . DS);

define('DIR_PROJETO', 'api/JEDI-API');

if (file_exists('autoload.php')) {
    include 'autoload.php';
} else {
    die('Arquivo de autoload nao encontrado');
}