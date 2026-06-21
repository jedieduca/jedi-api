<?php

namespace Repository;

use DB\MySQL;
use PDO;
use PDOException;

class Pergunta2Repository
{

    private $MySQL;

    public const TABELA = 'pergunta2';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    public function getMySQL(): MySQL
    {
        return $this->MySQL;
    }

    public function listarTodasPerguntaRepository(){
        try{
            $consulta = 'SELECT * FROM '. self::TABELA . ' WHERE fala_proposta IS NOT NULL';
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
                $consuta = 'SELECT * FROM ' . self::TABELA .' WHERE analise_proposta IS NOT NULL AND analise_gpt IS NOT NULL AND origem_analise IS NOT NULL AND fala_proposta IS NOT NULL AND publica = 1 AND origem_fala = 1 ORDER BY RAND() LIMIT :quantidade';
                $stmt = $this->MySQL->getDb()->prepare($consuta);
                $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
            }

            else{
                $consuta = 'SELECT * FROM ' . self::TABELA . ', perguntacategoria2, categoria WHERE pergunta2.id = perguntacategoria2.codPerg AND perguntacategoria2.categoria = categoria.id AND categoria.id = :categoria AND analise_proposta IS NOT NULL AND analise_gpt IS NOT NULL AND origem_analise IS NOT NULL AND fala_proposta IS NOT NULL AND publica = 1 AND origem_fala = 1 ORDER BY RAND() LIMIT :quantidade;';
                $stmt = $this->MySQL->getDb()->prepare($consuta);
                $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt->bindValue(':categoria', $categoria, PDO::PARAM_INT);
            }

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }

    }

}