<?php

namespace Repository;

use DB\MySQL;
use PDO;

class PartidasPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'partidas_perguntas';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    /**
     * Auxiliar para buscar o id do usuário no system_user a partir do e-mail/login
     */
    private function buscarIdUsuarioPorEmail($email)
    {
        $sql = "SELECT id FROM system_user WHERE email = :email OR login = :email LIMIT 1";
        $stmt = $this->MySQL->getDb()->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna o ID encontrado ou o ID 1 como fallback se o usuário não existir
        return $user ? (int)$user['id'] : 1;
    }

    /**
     * @param $idPartida
     * @return array
     */
    public function repositoriRanking($idPartidaAtual = null): array
    {
        // 1. Busca os top 10 melhores pontuadores (1 por usuário)
        $sqlRanking = "SELECT 
                p.id AS idPartida,
                COALESCE(p.nome, '') AS nome,
                COALESCE(p.jogador, '') AS jogador,
                CAST(ROUND(p.pontuacao, 1) AS CHAR) AS pontuacao,
                CAST(ROUND((p.qtd_acertos / NULLIF((p.qtd_acertos + p.qtd_erros), 0)) * 100, 4) AS CHAR) AS percentualAcertos,
                CAST(ROUND(p.tempo_gasto, 0) AS CHAR) AS tempoGasto,
                CAST(t.totalPartidas AS CHAR) AS totalPartidas
            FROM partidas_perguntas p
            INNER JOIN (
                SELECT id_usuario, MAX(pontuacao) as max_pontuacao, COUNT(*) as totalPartidas 
                FROM partidas_perguntas 
                GROUP BY id_usuario
            ) t ON p.id_usuario = t.id_usuario AND p.pontuacao = t.max_pontuacao
            GROUP BY p.id_usuario
            ORDER BY p.pontuacao DESC
            LIMIT 10";

        $stmt = $this->MySQL->getDb()->prepare($sqlRanking);
        $stmt->execute();
        $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Mapeia os 10 primeiros atribuindo posições de 1 a 10
        $rankingFinal = array_map(function ($item, $index) {
            return [
                "idPartida"         => (int)$item['idPartida'],
                "nome"              => (string)$item['nome'],
                "jogador"           => (string)$item['jogador'],
                "pontuacao"         => (string)$item['pontuacao'],
                "percentualAcertos" => $item['percentualAcertos'] !== null ? (string)$item['percentualAcertos'] : "0",
                "tempoGasto"        => (string)$item['tempoGasto'],
                "totalPartidas"     => (string)$item['totalPartidas'],
                "posicao"           => $index + 1
            ];
        }, $resultados, array_keys($resultados));

        // 2. Se um ID de partida foi passado, calcula a posição REAL dela no ranking global
        if ($idPartidaAtual !== null && (int)$idPartidaAtual > 0) {
            $idPartidaAtual = (int)$idPartidaAtual;

            // Busca os dados da partida especificada
            $sqlPartidaAtual = "SELECT 
                p.id AS idPartida,
                COALESCE(p.nome, '') AS nome,
                COALESCE(p.jogador, '') AS jogador,
                CAST(ROUND(p.pontuacao, 1) AS CHAR) AS pontuacao,
                CAST(ROUND((p.qtd_acertos / NULLIF((p.qtd_acertos + p.qtd_erros), 0)) * 100, 4) AS CHAR) AS percentualAcertos,
                CAST(ROUND(p.tempo_gasto, 0) AS CHAR) AS tempoGasto,
                CAST((SELECT COUNT(*) FROM partidas_perguntas WHERE id_usuario = p.id_usuario) AS CHAR) AS totalPartidas,
                p.pontuacao AS pontuacao_num
            FROM partidas_perguntas p
            WHERE p.id = :idPartida
            LIMIT 1";

            $stmtAtual = $this->MySQL->getDb()->prepare($sqlPartidaAtual);
            $stmtAtual->bindParam(':idPartida', $idPartidaAtual, \PDO::PARAM_INT);
            $stmtAtual->execute();
            $partidaEspecifica = $stmtAtual->fetch(\PDO::FETCH_ASSOC);

            if ($partidaEspecifica) {
                // Calcula quantas partidas únicas de usuários têm pontuação maior que esta
                $sqlPosicaoReal = "SELECT COUNT(DISTINCT id_usuario) + 1 AS posicao_real 
                               FROM partidas_perguntas 
                               WHERE pontuacao > :pontuacao";

                $stmtPos = $this->MySQL->getDb()->prepare($sqlPosicaoReal);
                $stmtPos->bindValue(':pontuacao', $partidaEspecifica['pontuacao_num']);
                $stmtPos->execute();
                $posicaoReal = (int)$stmtPos->fetchColumn();

                $rankingFinal[] = [
                    "idPartida"         => (int)$partidaEspecifica['idPartida'],
                    "nome"              => (string)$partidaEspecifica['nome'],
                    "jogador"           => (string)$partidaEspecifica['jogador'],
                    "pontuacao"         => (string)$partidaEspecifica['pontuacao'],
                    "percentualAcertos" => $partidaEspecifica['percentualAcertos'] !== null ? (string)$partidaEspecifica['percentualAcertos'] : "0",
                    "tempoGasto"        => (string)$partidaEspecifica['tempoGasto'],
                    "totalPartidas"     => (string)$partidaEspecifica['totalPartidas'],
                    "posicao"           => $posicaoReal // Retorna a posição real exata (ex: 6)
                ];
            }
        }

        return $rankingFinal;
    }

    public function repositoriRankingTurma($email)
    {
        try {
            $sqlGeral = "SELECT 
                pp.id AS id, 
                pp.jogador, 
                su.name AS nome, 
                MAX(pp.pontuacao) AS pontuacao, 
                (SUM(pp.qtd_acertos)/(SUM(pp.qtd_acertos)+SUM(pp.qtd_erros)))*100 AS percentual_acertos, 
                MIN(pp.tempo_gasto) AS tempo_gasto, 
                COUNT(*) AS total_partidas
                FROM " . self::TABELA . " pp
                INNER JOIN system_user su ON (pp.id_usuario = su.id OR pp.login = su.email)
                INNER JOIN oferta_turma_aluno ota ON su.id = ota.id_aluno
                INNER JOIN turma_oferta tof ON ota.id_oferta_turma = tof.id
                INNER JOIN turma t ON tof.id_turma = t.id
                WHERE t.id = (
                    SELECT t2.id FROM turma t2 
                    INNER JOIN turma_oferta tof2 ON t2.id = tof2.id_turma
                    INNER JOIN oferta_turma_aluno ota2 ON tof2.id_oferta_turma = ota2.id_oferta_turma
                    INNER JOIN system_user su2 ON ota2.id_aluno = su2.id
                    WHERE su2.email = :email LIMIT 1
                )
                GROUP BY su.id
                ORDER BY pontuacao DESC, percentual_acertos DESC, tempo_gasto ASC";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $rankingCompleto = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $top10 = [];

            foreach ($rankingCompleto as $index => $linha) {
                $posicaoAtual = (int)($index + 1);

                $item = [
                    "id_partida" => (int) $linha['id'],
                    "nome" => $linha['nome'],
                    "jogador" => $linha['jogador'],
                    "pontuacao" => $linha['pontuacao'],
                    "percentual_acertos" => $linha['percentual_acertos'],
                    "tempo_gasto" => $linha['tempo_gasto'],
                    "total_partidas" => $linha['total_partidas'],
                    "posicao" => $posicaoAtual
                ];

                if ($posicaoAtual <= 10) {
                    $top10[] = $item;
                }
            }

            return $top10;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro no Ranking da Turma: " . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @param $jogadorEmail
     * @param $dataHoraInicio
     * @param $nome
     * @param $idade
     * @param $autoAvaliacao
     * @param $avatar
     * @param $tempo_gasto
     * @return false|int|mixed|string
     */
    public function repositorySalvarPartida($id, $jogadorEmail, $dataHoraInicio, $nome, $idade, $autoAvaliacao, $avatar, $tempo_gasto)
    {
        if ($nome === null) {
            $nome = $jogadorEmail;
        }

        if ($idade === null) {
            $idade = 0;
        }

        // Buscar o ID correspondente ao usuário no system_user
        $idUsuario = $this->buscarIdUsuarioPorEmail($jogadorEmail);

        if (!$idUsuario) {
            throw new \InvalidArgumentException("Erro: O usuário com e-mail '{$jogadorEmail}' não foi encontrado na tabela system_user.");
        }

        try {
            // 1. CRIAR NOVA PARTIDA (id == -1)
            if ((int)$id === -1) {
                $sqlGeral = "INSERT INTO " . self::TABELA . " 
                (dt_jogo, login, id_usuario, id_tema, nome, jogador, idade, pontuacao, tempo_gasto, auto_avaliacao, avaliacao_jogo)
                VALUES (:dataHoraInicio, :jogadorEmail, :idUsuario, 17, :nome, :avatar, :idade, -1, :tempo_gasto, :autoAvaliacao, 'Noob')";

                $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':idUsuario', $idUsuario, \PDO::PARAM_INT);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade, \PDO::PARAM_INT);
                $stmt->bindParam(':tempo_gasto', $tempo_gasto, \PDO::PARAM_INT);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->execute();

                return (int)$this->MySQL->getDb()->lastInsertId();
            }

            // 2. ATUALIZAR PARTIDA EXISTENTE (id > 0)
            $sqlGeral = "UPDATE " . self::TABELA . " 
            SET `dt_jogo` = :dataHoraInicio, 
                `login` = :jogadorEmail,
                `id_usuario` = :idUsuario,
                `nome` = :nome,
                `jogador` = :avatar, 
                `idade` = :idade, 
                `tempo_gasto` = :tempo_gasto, 
                `auto_avaliacao` = :autoAvaliacao
            WHERE `id` = :id";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);

            $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':jogadorEmail', $jogadorEmail);
            $stmt->bindParam(':idUsuario', $idUsuario, \PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':avatar', $avatar);
            $stmt->bindParam(':idade', $idade, \PDO::PARAM_INT);
            $stmt->bindParam(':tempo_gasto', $tempo_gasto, \PDO::PARAM_INT);
            $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
            $stmt->execute();

            return (int)$id;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function repositoryAtualizarAcertoseErros($id)
    {
        $sqlGeral = "(SELECT (p.qtd_acertos / t.total) * 100 
                     FROM " . self::TABELA . " p 
                     JOIN (SELECT COUNT(*) as total FROM log_perguntas WHERE id_partida = :id) t 
                     WHERE p.id = :id)";

        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $percentAcertos = $stmt->fetchColumn();

        if ($percentAcertos >= 80.0) {
            $aval = "Proplayer";
        } else if ($percentAcertos >= 60.0 && $percentAcertos < 80.0) {
            $aval = "Avançado";
        } else if ($percentAcertos >= 40.0 && $percentAcertos < 60.0) {
            $aval = "Casual";
        } else if ($percentAcertos >= 20.0 && $percentAcertos < 40.0) {
            $aval = "Iniciante";
        } else {
            $aval = "Noob";
        }

        $sqlGeral = "UPDATE " . self::TABELA . " 
                SET 
                    `qtd_acertos` = (SELECT COUNT(*) FROM log_perguntas WHERE id_partida = :id AND id_tema = 17 AND resp_certa = resp_dada),
                    `qtd_erros` = (SELECT COUNT(*) FROM log_perguntas WHERE id_partida = :id AND id_tema = 17 AND resp_certa != resp_dada),
                    `pontuacao` = (
                        SELECT pontos FROM (
                            SELECT (100000 * (p2.qtd_acertos / t.total) + (100 * t.total)) AS pontos 
                            FROM " . self::TABELA . " p2 
                            JOIN (SELECT COUNT(*) as total FROM log_perguntas WHERE id_partida = :id) t 
                            WHERE p2.id = :id
                        ) AS temp
                    ),
                    `avaliacao_jogo` = :avaliacao
                WHERE id = :id";

        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':avaliacao', $aval);
        $stmt->execute();
    }
}