<?php

namespace Repository;

use DB\MySQL;
use PDO;
use PDOException;

class Pergunta2Repository
{
    private $MySQL;

    public const TABELA = 'pergunta';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    public function getMySQL(): MySQL
    {
        return $this->MySQL;
    }

    public function listarTodasPerguntaRepository(){
        try{
            $consulta = 'SELECT * FROM ' . self::TABELA . ' WHERE fala_proposta IS NOT NULL';
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function sortearPerguntas($quantidade, $categoria)
    {
        try{
            if($categoria == null){
                $consulta = 'SELECT * FROM ' . self::TABELA . ' 
                             WHERE analise_proposta IS NOT NULL 
                             AND analise_gpt IS NOT NULL 
                             AND origem_analise IS NOT NULL 
                             AND fala_proposta IS NOT NULL 
                             AND publica = 1 
                             AND origem_fala = 1 
                             ORDER BY RAND() LIMIT :quantidade';

                $stmt = $this->MySQL->getDb()->prepare($consulta);
                $stmt->bindValue(':quantidade', (int)$quantidade, PDO::PARAM_INT);
            }
            else{
                $consulta = 'SELECT p.* FROM ' . self::TABELA . ' p 
                             INNER JOIN pergunta_categoria pc ON p.id = pc.id_pergunta 
                             INNER JOIN categoria c ON pc.id_categoria = c.id 
                             WHERE c.id = :categoria 
                             AND p.analise_proposta IS NOT NULL 
                             AND p.analise_gpt IS NOT NULL 
                             AND p.origem_analise IS NOT NULL 
                             AND p.fala_proposta IS NOT NULL 
                             AND p.publica = 1 
                             AND p.origem_fala = 1 
                             ORDER BY RAND() LIMIT :quantidade;';

                $stmt = $this->MySQL->getDb()->prepare($consulta);
                $stmt->bindValue(':quantidade', (int)$quantidade, PDO::PARAM_INT);
                $stmt->bindValue(':categoria', (int)$categoria, PDO::PARAM_INT);
            }

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }
}