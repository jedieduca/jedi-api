<?php

namespace DB;

use PDO;
use PDOException;

class MySQL
{
    private $db;

    public function __construct()
    {
        $host     = defined('HOST') ? HOST : ($_ENV['HOST'] ?? getenv('HOST') ?: '127.0.0.1');
        $banco    = defined('BANCO') ? BANCO : ($_ENV['BANCO'] ?? getenv('BANCO') ?: 'jedi-educa-v2');
        $usuario  = defined('USUARIO') ? USUARIO : ($_ENV['USUARIO'] ?? getenv('USUARIO') ?: 'root');
        $senha    = defined('SENHA') ? SENHA : ($_ENV['SENHA'] ?? getenv('SENHA') ?: 'mys2Edu4Up@2025');

        try {
            $this->db = new PDO(
                "mysql:host={$host};dbname={$banco};charset=utf8",
                $usuario,
                $senha
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Falha ao conectar no banco: " . $e->getMessage());
        }
    }

    public function getDb()
    {
        return $this->db;
    }
}