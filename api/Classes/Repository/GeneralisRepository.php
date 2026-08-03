<?php

namespace Repository;

use DB\MySQL;
use PDO;

class GeneralisRepository
{
    private $MySQL;
    public static function listarInstancias($where, $tabela
    ){
        $MySQL = new MySQL();
        try{
            if($where !== ""){
                $where = " WHERE " . $where;
            }

            $consulta = 'SELECT * FROM '. $tabela . $where;
            $stmt = $MySQL->getDb()->prepare($consulta);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }
}