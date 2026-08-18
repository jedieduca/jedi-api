<?php

namespace Repository;

use DB\MySQL;
use PDO;

class LogPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'log_perguntas';

    public function __construct() {
        $this->MySQL = new MySQL();
    }

    public function getMySQL()
    {
        return $this->MySQL;
    }

    public function inserirLogPerguntasRepository($idPartida, $jogadorEmail, $dataHoraInicio, $idade, $avatar, $jogadaId, $noticiaId, $avaliacaoCorreta, $tempoResposta, $posicaoAvatar)
    {
        $resultado = -1;
        try {
            // 1. Busca a resposta correta da pergunta
            $respCertaSQL = "SELECT resp_certa FROM pergunta WHERE id = :noticiaId";
            $stmt = $this->MySQL->getDb()->prepare($respCertaSQL);
            $stmt->bindParam(':noticiaId', $noticiaId, PDO::PARAM_INT);
            $stmt->execute();

            $pergunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($pergunta['resp_certa']) && $pergunta['resp_certa'] !== null) {
                $respCertaStr = $pergunta['resp_certa'];

                // 2. Determina qual foi a resposta dada pelo jogador com base na validação booleana
                if ($avaliacaoCorreta === true || $avaliacaoCorreta === 'true' || $avaliacaoCorreta === 1) {
                    $respDada = $respCertaStr;
                } else {
                    $respDada = ($respCertaStr === 'FAKE') ? 'NÃO FAKE' : 'FAKE';
                }

                // 3. Busca o id_usuario em system_user pelo e-mail recebido
                $sqlUser = "SELECT id FROM system_user WHERE email = :email LIMIT 1";
                $stmtUser = $this->MySQL->getDb()->prepare($sqlUser);
                $stmtUser->bindValue(':email', $jogadorEmail);
                $stmtUser->execute();
                $id_usuario = $stmtUser->fetchColumn() ?: 1;

                // 4. INSERT 100% alinhado com as colunas da imagem (sem a coluna 'login')
                $sql = "INSERT INTO " . self::TABELA . " 
                    (id_partida, dt_jogo, id_usuario, jogador, idade, id_tema, num_jogada, id_pergunta, resp_certa, resp_dada, tempo_gasto, realizada_tutor, posicao)
                    VALUES (:idPartida, :dataHoraInicio, :id_usuario, :avatar, :idade, 14, :jogadaId, :noticiaId, :respCerta, :respDada, :tempoResposta, 1, :posicaoAvatar)";

                $stmt = $this->MySQL->getDb()->prepare($sql);
                $stmt->bindParam(':idPartida', $idPartida, PDO::PARAM_INT);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade, PDO::PARAM_INT);
                $stmt->bindParam(':jogadaId', $jogadaId, PDO::PARAM_INT);
                $stmt->bindParam(':noticiaId', $noticiaId, PDO::PARAM_INT);
                $stmt->bindParam(':respCerta', $respCertaStr);
                $stmt->bindParam(':respDada', $respDada);
                $stmt->bindParam(':tempoResposta', $tempoResposta);
                $stmt->bindParam(':posicaoAvatar', $posicaoAvatar, PDO::PARAM_INT);
                $stmt->execute();

                $resultado = $this->MySQL->getDb()->lastInsertId();
            }
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL no LogPerguntas: " . $e->getMessage());
        }

        return $resultado;
    }
}