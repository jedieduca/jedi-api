<?php

namespace Repository;

use DB\MySQL;
use PDO;

class LogPerguntasRepository
{
    private $MySQL;

    // Tabela padronizada em snake_case
    public const TABELA = 'log_perguntas';

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
            // Coluna resp_certa em snake_case na tabela pergunta2
            $respCertaSQL = "SELECT pergunta2.resp_certa FROM pergunta2 WHERE pergunta2.id = :noticiaId";
            $stmt = $this->MySQL->getDb()->prepare($respCertaSQL);
            $stmt->bindParam(':noticiaId', $noticiaId);
            $stmt->execute();

            $respCerta = $stmt->fetch();

            if (isset($respCerta['resp_certa']) && $respCerta['resp_certa'] !== null) {

                if ($avaliacaoCorreta === true) {
                    $avaliacaoCorreta = $respCerta['resp_certa'];
                } elseif ($avaliacaoCorreta === false) {

                    if ($respCerta['resp_certa'] === 'FAKE') {
                        $avaliacaoCorreta = 'NÃO FAKE';
                    } else {
                        $avaliacaoCorreta = 'FAKE';
                    }
                }

                // INSERT com todas as colunas convertidas para snake_case
                $sql = "INSERT INTO " . self::TABELA . " (dt_jogo, id_partida, usuario, idade, tema, jogador, num_jogada, pergunta, resp_certa, resp_dada, tempo_gasto, posicao)
                VALUES (:dataHoraInicio, :id, :jogadorEmail, :idade, 17, :avatar, :jogadaId, :noticiaId, :respCerta, :respDada, :tempoResposta, :posicaoAvatar)";

                $stmt = $this->MySQL->getDb()->prepare($sql);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':jogadaId', $jogadaId);
                $stmt->bindParam(':noticiaId', $noticiaId);
                $stmt->bindParam(':respCerta', $respCerta['resp_certa']);
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