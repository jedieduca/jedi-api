<?php

namespace Repository;

use DB\MySQL;
use PDO;

class LogPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'logPerguntas';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    public function getMySQL()
    {
        return $this->MySQL;
    }
    public function inserirLogPerguntasRepository($id, $jogadorEmail, $dataHoraInicio, $idade, $avatar, $jogadaId, $noticiaId, $avaliacaoCorreta, $tempoResposta, $posicaoAvatar){
        $resultado = -1;
        try{
            $respCertaSQL = "SELECT pergunta2.respcerta FROM pergunta2 WHERE pergunta2.id = :noticiaId";
            $stmt = $this->MySQL->getDb()->prepare($respCertaSQL);
            $stmt->bindParam(':noticiaId', $noticiaId);
            $stmt->execute();

            $respCerta = $stmt->fetch();

            if ($respCerta['respcerta'] !== null) {

                if ($avaliacaoCorreta === true) {
                    $avaliacaoCorreta = $respCerta['respcerta'];
                } elseif ($avaliacaoCorreta === false) {

                    if ($respCerta['respcerta'] === 'FAKE') {
                        $avaliacaoCorreta = 'NÃO FAKE';
                    } else {
                        $avaliacaoCorreta = 'FAKE';
                    }
                }


                $sql = "INSERT INTO " . self::TABELA . " (dtJogo, idPartida, usuario, idade, tema, jogador, numJogada, pergunta, respCerta, respDada, tempoGasto, posicao)
                VALUES (:dataHoraInicio, :id, :jogadorEmail,:idade,17,:avatar, :jogadaId, :noticiaId,:respCerta, :respDada,:tempoResposta, :posicaoAvatar)";
                $stmt = $this->MySQL->getDb()->prepare($sql);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':jogadaId', $jogadaId);
                $stmt->bindParam(':noticiaId', $noticiaId);
                $stmt->bindParam(':respCerta', $respCerta['respcerta']);
                $stmt->bindParam(':respDada', $avaliacaoCorreta);
                $stmt->bindParam(':tempoResposta', $tempoResposta);
                $stmt->bindParam(':posicaoAvatar', $posicaoAvatar);
                $stmt->execute();

                $resultado = $this->MySQL->getDb()->lastInsertId();
            }
        }
        catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }

        return $resultado;
    }
}