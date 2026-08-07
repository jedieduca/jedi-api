<?php

namespace Repository;

use DB\MySQL;
use PDO;

class LogPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'log_perguntas';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    public function getMySQL()
    {
        return $this->MySQL;
    }

    /**
     * Busca o id do usuário no system_user usando o e-mail
     */
    private function buscarIdUsuarioPorEmail($email)
    {
        $sql = "SELECT id FROM system_user WHERE email = :email OR login = :email LIMIT 1";
        $stmt = $this->MySQL->getDb()->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? (int)$user['id'] : null;
    }

    public function inserirLogPerguntasRepository($id, $jogadorEmail, $dataHoraInicio, $idade, $avatar, $jogadaId, $noticiaId, $avaliacaoCorreta, $tempoResposta, $posicaoAvatar){
        $resultado = -1;
        try{
            // 1. Busca a resposta correta da pergunta na tabela 'pergunta'
            $respCertaSQL = "SELECT resp_certa FROM pergunta WHERE id = :noticiaId";
            $stmt = $this->MySQL->getDb()->prepare($respCertaSQL);
            $stmt->bindParam(':noticiaId', $noticiaId, PDO::PARAM_INT);
            $stmt->execute();

            $respCerta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($respCerta['resp_certa']) && $respCerta['resp_certa'] !== null) {

                // Trata a resposta dada com base no valor booleano enviado
                if ($avaliacaoCorreta === true || $avaliacaoCorreta === 'true' || $avaliacaoCorreta === 1) {
                    $respDada = $respCerta['resp_certa'];
                } else {
                    $respDada = ($respCerta['resp_certa'] === 'FAKE') ? 'NÃO FAKE' : 'FAKE';
                }

                // Busca o ID do usuário para popular o id_usuario
                $idUsuario = $this->buscarIdUsuarioPorEmail($jogadorEmail);

                // 2. INSERT utilizando ESTRITAMENTE as colunas do print fornecido
                $sql = "INSERT INTO " . self::TABELA . " 
                    (dt_jogo, id_partida, id_usuario, idade, id_tema, jogador, num_jogada, id_pergunta, resp_certa, resp_dada, tempo_gasto, posicao)
                    VALUES 
                    (:dataHoraInicio, :idPartida, :idUsuario, :idade, 17, :avatar, :jogadaId, :noticiaId, :respCerta, :respDada, :tempoResposta, :posicaoAvatar)";

                $stmt = $this->MySQL->getDb()->prepare($sql);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':idPartida', $id, PDO::PARAM_INT);
                $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
                $stmt->bindParam(':idade', $idade, PDO::PARAM_INT);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':jogadaId', $jogadaId, PDO::PARAM_INT);
                $stmt->bindParam(':noticiaId', $noticiaId, PDO::PARAM_INT);
                $stmt->bindParam(':respCerta', $respCerta['resp_certa']);
                $stmt->bindParam(':respDada', $respDada);
                $stmt->bindParam(':tempoResposta', $tempoResposta);
                $stmt->bindParam(':posicaoAvatar', $posicaoAvatar);
                $stmt->execute();

                $resultado = (int)$this->MySQL->getDb()->lastInsertId();
            }
        }
        catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL no LogPerguntas: " . $e->getMessage());
        }

        return $resultado;
    }
}