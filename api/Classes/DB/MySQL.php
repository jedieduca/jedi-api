<?php

namespace Classes\DB;

use PDO;
use PDOException;

class MySQL
{
    private $db = null;

    public function __construct()
    {
        $this->conectar();
    }

    private function conectar()
    {
        try {
            // Configurações cruciais para depuração e codificação
            $opcoes = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ];

            // Tenta a conexão com o IP interno que você colocou no bootstrap
            $this->db = new PDO("mysql:host=" . HOST . ";dbname=" . BANCO, USER, SENHA, $opcoes);

        } catch (PDOException $e) {
            // Força o PHP a parar tudo e te mostrar o erro REAL da conexão no Postman
            header('Content-Type: application/json');
            echo json_encode([
                "erro" => "Falha ao conectar fisicamente no contêiner do banco",
                "detalhes" => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function getDb()
    {
        if ($this->db === null) {
            $this->conectar();
        }
        return $this->db;
    }
}