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
     * @param $idPartida
     * @return array
     */
    public function repositoriRanking($idPartida)
    {
        try {
            $jogadorAtual = null;

            $sqlGeral = "SELECT id_partida, jogador, MAX(pontuacao) AS pontuacao, 
                    (SUM(qtd_acertos)/(SUM(qtd_acertos)+SUM(qtd_erros)))*100 AS percentual_acertos, 
                    MIN(tempo_gasto) AS tempo_gasto, COUNT(*) AS total_partidas
                    FROM " . self::TABELA . " 
                    GROUP BY login
                    UNION ALL
                    SELECT id_partida, jogador, pontuacao, 
                    (SUM(qtd_acertos)/(SUM(qtd_acertos)+SUM(qtd_erros)))*100 AS percentual_acertos, 
                    tempo_gasto, COUNT(*) AS total_partidas FROM " . self::TABELA . " WHERE id_partida = :idPartida
                    ORDER BY pontuacao DESC, percentual_acertos DESC, tempo_gasto ASC;";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
            $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
            $stmt->execute();
            $rankingCompleto = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $top10 = [];

            // Numerar TODOS e separar o que precisamos
            foreach ($rankingCompleto as $index => $linha) {
                $posicaoAtual = (int)($index + 1);

                $item = [
                    "id_partida" => (int) $linha['id_partida'],
                    "jogador" => $linha['jogador'],
                    "pontuacao" => $linha['pontuacao'],
                    "percentual_acertos" => $linha['percentual_acertos'],
                    "tempo_gasto" => $linha['tempo_gasto'],
                    "total_partidas" => $linha['total_partidas'],
                    "posicao" => $posicaoAtual
                ];

                if($item['id_partida'] == $idPartida){
                    $jogadorAtual = $item;

                    $sqlGeral = "SELECT auto_avaliacao, avaliacao_jogo FROM " . self::TABELA . " WHERE id_partida = :idPartida";
                    $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
                    $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
                    $stmt->execute();
                    $avaliacao = $stmt->fetch(\PDO::FETCH_ASSOC);

                    $jogadorAtual['auto_avaliacao'] = $avaliacao['auto_avaliacao'] ?? null;
                    $jogadorAtual['avaliacao_jogo'] = $avaliacao['avaliacao_jogo'] ?? null;
                }

                if ($posicaoAtual <= 10) {
                    $top10[] = $item;
                }
            }

            if ($jogadorAtual && !in_array($jogadorAtual, $top10, true)) {
                $top10[] = $jogadorAtual;
            }

            return $top10;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function repositoriRankingTurma($email)
    {
        try {
            $jogadorAtual = null;

            // SQL unificado em snake_case com JOIN para buscar o ranking da turma vinculada ao email
            $sqlGeral = "SELECT 
                        pp.id_partida, pp.jogador, pp.nome, MAX(pp.pontuacao) AS pontuacao, 
                        (SUM(pp.qtd_acertos)/(SUM(pp.qtd_acertos)+SUM(pp.qtd_erros)))*100 AS percentual_acertos, 
                        MIN(pp.tempo_gasto) AS tempo_gasto, COUNT(*) AS total_partidas
                    FROM " . self::TABELA . " pp
                    INNER JOIN system_user su ON pp.login = su.email
                    INNER JOIN oferta_turma_aluno ota ON su.id = ota.id_aluno
                    INNER JOIN turma_oferta tof ON ota.id_oferta_turma = tof.id
                    INNER JOIN turma t ON tof.id_turma = t.id
                    WHERE t.id = (
                        SELECT t2.id FROM turma t2 
                        INNER JOIN turma_oferta tof2 ON t2.id = tof2.id_turma
                        INNER JOIN oferta_turma_aluno ota2 ON tof2.id = ota2.id_oferta_turma
                        INNER JOIN system_user su2 ON ota2.id_aluno = su2.id
                        WHERE su2.email = :email LIMIT 1
                    )
                    GROUP BY pp.login
                    ORDER BY pontuacao DESC, percentual_acertos DESC, tempo_gasto ASC";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
            $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            $rankingCompleto = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $top10 = [];

            foreach ($rankingCompleto as $index => $linha) {
                $posicaoAtual = (int)($index + 1);

                $item = [
                    "id_partida" => (int) $linha['id_partida'],
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

        try {
            if($id === -1){
                $sqlGeral = "INSERT INTO " . self::TABELA . " (dt_jogo, login, tema, jogador, idade, pontuacao, tempo_gasto, auto_avaliacao, avaliacao_jogo, nome)
                    VALUES (:dataHoraInicio, :jogadorEmail, 17, :avatar, :idade, -1, :tempo_gasto, :autoAvaliacao, 'Noob', :nome)";

                $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempo_gasto', $tempo_gasto);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->bindParam(':nome', $nome);
                $stmt->execute();

                $resultado = $this->MySQL->getDb()->lastInsertId();
            }

            if ($id !== -1){
                $sqlGeral = "UPDATE " . self::TABELA . " 
                    SET `dt_jogo` = :dataHoraInicio, 
                        `login` = :jogadorEmail,
                        `jogador` = :avatar, 
                        `idade` = :idade, 
                        `pontuacao` = 150,
                        `qtd_acertos` = (SELECT COUNT(*) FROM log_perguntas lp WHERE lp.id_partida = :id AND lp.tema = 17 AND lp.resp_certa = lp.resp_dada),
                        `qtd_erros` = (SELECT COUNT(*) FROM log_perguntas lp WHERE lp.id_partida = :id AND lp.tema = 17 AND lp.resp_certa != lp.resp_dada),
                        `tempo_gasto` = :tempo_gasto, 
                        `auto_avaliacao` = :autoAvaliacao,
                        `avaliacao_jogo` = 'Pro', 
                        `nome` = :nome 
                        WHERE `id_partida` = :id";

                $stmt = $this->MySQL->getDb()->prepare($sqlGeral);

                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempo_gasto', $tempo_gasto);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->bindParam(':nome', $nome);
                $stmt->execute();

                if($stmt->rowCount() <= 0){
                    $resultado = -1;
                }
                else{
                    return $id;
                }
            }
            return $resultado;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return void
     */
    public function repositoryAtualizarAcertoseErros($id)
    {
        $sqlGeral = "(SELECT (p.qtd_acertos / t.total) * 100 FROM " . self::TABELA . " p JOIN (SELECT COUNT(*) as total FROM log_perguntas WHERE id_partida = :id) t WHERE p.id_partida = :id)";
        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $percentAcertos = $stmt->fetchColumn();

        if ($percentAcertos >= 80.0)
            $aval = "Proplayer";
        else if (($percentAcertos >= 60.0) && ($percentAcertos < 80.0))
            $aval = "Avançado";
        else if (($percentAcertos >= 40.0) && ($percentAcertos < 60.0))
            $aval = "Casual";
        else if (($percentAcertos >= 20.0) && ($percentAcertos < 40.0))
            $aval = "Iniciante";
        else if ($percentAcertos < 20.0)
            $aval = "Noob";

        $sqlGeral = "UPDATE " . self::TABELA . " 
                SET 
                    `qtd_acertos` = (SELECT COUNT(*) FROM log_perguntas WHERE id_partida = :id AND tema = 17 AND resp_certa = resp_dada),
                    
                    `qtd_erros` = (SELECT COUNT(*) FROM log_perguntas WHERE id_partida = :id AND tema = 17 AND resp_certa != resp_dada),
                    
                    `pontuacao` = (
                        SELECT pontos FROM (
                            SELECT (100000 * (p2.qtd_acertos / t.total) + (100 * t.total)) AS pontos 
                            FROM " . self::TABELA . " p2 
                            JOIN (SELECT COUNT(*) as total FROM log_perguntas WHERE id_partida = :id) t 
                            WHERE p2.id_partida = :id
                        ) AS temp
                    ),
                    
                    `avaliacao_jogo` = :avaliacao
                WHERE id_partida = :id";

        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':avaliacao', $aval);
        $stmt->execute();
    }
}