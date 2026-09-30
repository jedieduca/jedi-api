<?php

namespace DB;

use PDO;
use PDOException;

class MySQL
{
    private $db;

    public function __construct()
    {
        $host    = getenv('DB_HOST') ?: '';
        $banco   = getenv('DB_NAME') ?: '';
        $usuario = getenv('DB_USER') ?: '';
        $senha   = getenv('DB_PASSWORD');

        // Sem valor padrão: se faltar configuração, falha em vez de conectar no banco errado
        if ($host === '' || $banco === '' || $usuario === '' || $senha === false) {
            throw new \InvalidArgumentException('Configuração do banco ausente: defina DB_HOST, DB_NAME, DB_USER e DB_PASSWORD no .env');
        }

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