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

            $sqlGeral = "SELECT id, nome, jogador, MAX(pontuacao) AS pontuacao, 
                    (SUM(qtd_acertos)/(SUM(qtd_acertos)+SUM(qtd_erros)))*100 AS percentual_acertos, 
                    MIN(tempo_gasto) AS tempo_gasto, COUNT(*) AS total_partidas
                    FROM " . self::TABELA . " 
                    GROUP BY login
                    UNION ALL   
                    SELECT id, nome,jogador, pontuacao, 
                    (SUM(qtd_acertos)/(SUM(qtd_acertos)+SUM(qtd_erros)))*100 AS percentual_acertos, 
                    tempo_gasto, COUNT(*) AS total_partidas FROM " . self::TABELA . " WHERE id = :idPartida
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
                    "id_partida" => (int) $linha['id'],
                    "jogador" => $linha['jogador'],
                    "nome" => $linha['nome'],
                    "pontuacao" => $linha['pontuacao'],
                    "percentual_acertos" => $linha['percentual_acertos'],
                    "tempo_gasto" => $linha['tempo_gasto'],
                    "total_partidas" => $linha['total_partidas'],
                    "posicao" => $posicaoAtual
                ];

                if($item['id_partida'] == $idPartida){
                    $jogadorAtual = $item;

                    $sqlGeral = "SELECT auto_avaliacao, avaliacao_jogo FROM " . self::TABELA . " WHERE id = :idPartida";
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
                        pp.id, pp.jogador, pp.nome, MAX(pp.pontuacao) AS pontuacao, 
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

        $idInt = (int) $id;

        try {
            // -------------------------------------------------------------
            // CASO 1: CRIAR NOVA PARTIDA ($id == -1)
            // -------------------------------------------------------------
            if ($idInt === -1) {
                // 1. Busca o ID do usuário em system_user pelo e-mail
                $sqlUser = "SELECT id FROM system_user WHERE email = :email LIMIT 1";
                $stmtUser = $this->MySQL->getDb()->prepare($sqlUser);
                $stmtUser->bindValue(':email', $jogadorEmail);
                $stmtUser->execute();
                $id_usuario = $stmtUser->fetchColumn() ?: 1; // Fallback caso não encontre

                // 2. Insere na tabela partidas_perguntas
                $sqlInsert = "INSERT INTO " . self::TABELA . " 
                    (dt_jogo, id_usuario, login, jogador, id_tema, tutor, nome, idade, pontuacao, tempo_gasto, auto_avaliacao, avaliacao_jogo, finalizado)
                    VALUES (:dataHoraInicio, :id_usuario, :login, :jogador, 14, 0, :nome, :idade, -1, :tempo_gasto, :autoAvaliacao, 'Noob', 0)";

                $stmt = $this->MySQL->getDb()->prepare($sqlInsert);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(':login', $jogadorEmail);
                $stmt->bindParam(':jogador', $avatar);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempo_gasto', $tempo_gasto);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->execute();

                return $this->MySQL->getDb()->lastInsertId();
            }

            // -------------------------------------------------------------
            // CASO 2: ATUALIZAR/FINALIZAR PARTIDA ($id != -1)
            // -------------------------------------------------------------
            if ($idInt !== -1) {
                $sqlUpdate = "UPDATE " . self::TABELA . " 
                    SET `dt_jogo`        = :dataHoraInicio, 
                        `login`          = :jogadorEmail,
                        `jogador`        = :avatar, 
                        `idade`          = :idade, 
                        `pontuacao`      = 150,
                        `qtd_acertos`    = (SELECT COUNT(*) FROM log_perguntas lp WHERE lp.id_partida = :id AND lp.id_tema = 17 AND lp.resp_certa = lp.resp_dada),
                        `qtd_erros`      = (SELECT COUNT(*) FROM log_perguntas lp WHERE lp.id_partida = :id AND lp.id_tema = 17 AND lp.resp_certa != lp.resp_dada),
                        `tempo_gasto`    = :tempo_gasto, 
                        `auto_avaliacao` = :autoAvaliacao,
                        `avaliacao_jogo` = 'Pro', 
                        `nome`           = :nome,
                        `finalizado`     = 1
                    WHERE `id` = :id";

                $stmt = $this->MySQL->getDb()->prepare($sqlUpdate);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':id', $idInt, PDO::PARAM_INT);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempo_gasto', $tempo_gasto);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->bindParam(':nome', $nome);
                $stmt->execute();

                return $idInt;
            }

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL ao salvar/atualizar partida: " . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return void
     */
    public function repositoryAtualizarAcertoseErros($id)
    {
        try {
            $idInt = (int) $id;

            // 1. Calcula a quantidade de acertos, erros e total diretamente no log da partida
            $sqlContagem = "SELECT 
                            COUNT(*) AS total,
                            SUM(CASE WHEN resp_certa = resp_dada THEN 1 ELSE 0 END) AS acertos,
                            SUM(CASE WHEN resp_certa != resp_dada THEN 1 ELSE 0 END) AS erros
                        FROM log_perguntas 
                        WHERE id_partida = :id";

            $stmt = $this->MySQL->getDb()->prepare($sqlContagem);
            $stmt->bindParam(':id', $idInt, PDO::PARAM_INT);
            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            $total   = (int) ($dados['total'] ?? 0);
            $acertos = (int) ($dados['acertos'] ?? 0);
            $erros   = (int) ($dados['erros'] ?? 0);

            // 2. Calcula o percentual e define a avaliação
            $percentAcertos = $total > 0 ? ($acertos / $total) * 100 : 0;

            if ($percentAcertos >= 80.0)      $aval = "Proplayer";
            else if ($percentAcertos >= 60.0) $aval = "Avançado";
            else if ($percentAcertos >= 40.0) $aval = "Casual";
            else if ($percentAcertos >= 20.0) $aval = "Iniciante";
            else                              $aval = "Noob";

            // 3. Formula a pontuação com base nos acertos calculados
            $pontuacao = $total > 0 ? (int)(100000 * ($acertos / $total) + (100 * $total)) : 0;

            // 4. Executa um UPDATE direto e limpo na tabela partidas_perguntas
            $sqlUpdate = "UPDATE " . self::TABELA . " 
                      SET `qtd_acertos`    = :acertos,
                          `qtd_erros`      = :erros,
                          `pontuacao`      = :pontuacao,
                          `avaliacao_jogo` = :avaliacao,
                          `finalizado`     = 1
                      WHERE id = :id";

            $stmt = $this->MySQL->getDb()->prepare($sqlUpdate);
            $stmt->bindParam(':acertos', $acertos, PDO::PARAM_INT);
            $stmt->bindParam(':erros', $erros, PDO::PARAM_INT);
            $stmt->bindParam(':pontuacao', $pontuacao, PDO::PARAM_INT);
            $stmt->bindParam(':avaliacao', $aval);
            $stmt->bindParam(':id', $idInt, PDO::PARAM_INT);
            $stmt->execute();

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro ao atualizar acertos e erros: " . $e->getMessage());
        }
    }
}