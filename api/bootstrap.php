<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 define('HOST', '31.97.27.46');
 define('BANCO', 'jedi-educa-v2');
 define('USER', 'root');
 define('SENHA', 'mys2Edu4Up@2025');

define('DS', DIRECTORY_SEPARATOR);
define('DIR_APP', __DIR__ . DS);

define('DIR_PROJETO', getenv('DIR_PROJETO') ?: '');

if (file_exists('autoload.php')) {
    include 'autoload.php';
} else {
    die('Arquivo de autoload nao encontrado');
}