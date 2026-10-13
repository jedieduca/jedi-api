<?php

namespace Repository;

use DB\MySQL;
use PDO;

class PartidasPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'partidas_perguntas';

    // id_usuario gravado quando o e-mail do jogador não é encontrado em system_user (usuário admin);
    // agrupa jogadores diferentes, por isso fica fora dos rankings
    private const ID_USUARIO_NAO_IDENTIFICADO = 1;

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    /**
     * Ranking global: considera as partidas de todos os jogadores
     * @param $idPartida
     * @return array
     */
    public function repositoriRanking($idPartida)
    {
        try {
            return $this->montarRankingDasPartidas(self::TABELA, $idPartida);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * Escola e turma do jogador que fez a partida
     * (system_user -> usuario_escola -> escola -> turma -> turma_aluno)
     * @param $idPartida
     * @return array|false ['id_escola' => N, 'id_turma' => N], ou false se o jogador não tiver vínculo
     */
    public function repositoriVinculoEscolar($idPartida)
    {
        try {
            $sql = "SELECT e.id AS id_escola, t.id AS id_turma
                    FROM " . self::TABELA . " pp
                    INNER JOIN system_user su ON su.id = pp.id_usuario
                    INNER JOIN usuario_escola ue ON su.id = ue.id_usuario
                    INNER JOIN escola e ON ue.id_escola = e.id
                    INNER JOIN turma t ON e.id = t.id_escola
                    INNER JOIN turma_aluno ta ON t.id = ta.id_turma AND su.id = ta.id_aluno
                    WHERE pp.id = :idPartida
                    LIMIT 1";

            $stmt = $this->MySQL->getDb()->prepare($sql);
            $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * Ranking da escola: partidas dos alunos das turmas da escola
     * @param $idPartida
     * @param $idEscola
     * @return array
     */
    public function repositoriRankingEscola($idPartida, $idEscola)
    {
        return $this->montarRankingEscolar($idPartida, 't.id_escola', $idEscola);
    }

    /**
     * Ranking da turma: partidas dos alunos da turma
     * @param $idPartida
     * @param $idTurma
     * @return array
     */
    public function repositoriRankingTurma($idPartida, $idTurma)
    {
        return $this->montarRankingEscolar($idPartida, 't.id', $idTurma);
    }

    /**
     * Filtra as partidas dos alunos vinculados (pela mesma cadeia de repositoriVinculoEscolar)
     * à escola ou turma informada e monta o ranking
     * @param $idPartida
     * @param string $colunaFiltro 't.id_escola' ou 't.id'
     * @param $idFiltro
     * @return array
     */
    private function montarRankingEscolar($idPartida, string $colunaFiltro, $idFiltro)
    {
        try {
            $with = "WITH partidas_grupo AS (
                        SELECT pp.* FROM " . self::TABELA . " pp
                        WHERE pp.id_usuario IN (
                            SELECT su.id FROM system_user su
                            INNER JOIN usuario_escola ue ON su.id = ue.id_usuario
                            INNER JOIN escola e ON ue.id_escola = e.id
                            INNER JOIN turma t ON e.id = t.id_escola
                            INNER JOIN turma_aluno ta ON t.id = ta.id_turma AND su.id = ta.id_aluno
                            WHERE {$colunaFiltro} = :idFiltro
                        )
                    )";

            return $this->montarRankingDasPartidas('partidas_grupo', $idPartida, $with, [':idFiltro' => $idFiltro]);
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * Monta o top 10 a partir de um conjunto de partidas, agrupando os jogadores por id_usuario,
     * e acrescenta, no fim, o resumo da partida atual
     * @param string $origem tabela ou CTE com as partidas consideradas
     * @param $idPartida
     * @param string $with cláusula WITH que define $origem, quando ela for uma CTE
     * @param array $parametros parâmetros extras usados em $with
     * @return array
     */
    private function montarRankingDasPartidas(string $origem, $idPartida, string $with = '', array $parametros = [])
    {
        $jogadorAtual = null;
        $semNaoIdentificado = "id_usuario <> " . self::ID_USUARIO_NAO_IDENTIFICADO;

        // Escola e turma de cada aluno (mesma cadeia de repositoriVinculoEscolar); cada aluno tem no máximo uma turma
        $vinculoEscolar = "(
                    SELECT ta.id_aluno AS id_usuario, MIN(e.nome) AS escola, MIN(t.identificacao) AS turma
                    FROM turma_aluno ta
                    INNER JOIN turma t ON t.id = ta.id_turma
                    INNER JOIN escola e ON e.id = t.id_escola
                    INNER JOIN usuario_escola ue ON ue.id_usuario = ta.id_aluno AND ue.id_escola = e.id
                    GROUP BY ta.id_aluno
                )";

        // 1ª parte: melhor partida de cada jogador (rn = 1) + totais por jogador calculados com GROUP BY
        //   (SUM/MIN/COUNT ... OVER (PARTITION BY) no MariaDB recalcula a partição inteira a cada linha e fica muito lento)
        // 2ª parte: a partida atual, usada apenas para montar o resumo do jogador atual
        // Nas duas: nome do cadastro, escola e turma, usados para identificar o jogador na tela
        $sqlGeral = $with . "
                SELECT 'ranking' AS origem, melhores.id, melhores.nome, melhores.jogador, melhores.pontuacao,
                totais.percentual_acertos, totais.tempo_gasto, totais.total_partidas,
                NULL AS auto_avaliacao, NULL AS avaliacao_jogo,
                su.name AS nome_usuario, vinculo.escola, vinculo.turma
                FROM (
                    SELECT p.id, p.id_usuario, p.nome, p.jogador, p.pontuacao,
                    ROW_NUMBER() OVER (PARTITION BY p.id_usuario ORDER BY p.pontuacao DESC, p.tempo_gasto ASC, p.id DESC) AS rn
                    FROM {$origem} p
                    WHERE p.{$semNaoIdentificado}
                ) melhores
                INNER JOIN (
                    SELECT id_usuario, (SUM(qtd_acertos)/(SUM(qtd_acertos)+SUM(qtd_erros)))*100 AS percentual_acertos,
                    MIN(tempo_gasto) AS tempo_gasto, COUNT(*) AS total_partidas
                    FROM {$origem}
                    WHERE {$semNaoIdentificado}
                    GROUP BY id_usuario
                ) totais ON totais.id_usuario = melhores.id_usuario
                LEFT JOIN system_user su ON su.id = melhores.id_usuario
                LEFT JOIN {$vinculoEscolar} vinculo ON vinculo.id_usuario = melhores.id_usuario
                WHERE melhores.rn = 1
                UNION ALL
                SELECT 'atual' AS origem, a.id, a.nome, a.jogador, a.pontuacao,
                (a.qtd_acertos/(a.qtd_acertos+a.qtd_erros))*100 AS percentual_acertos,
                a.tempo_gasto, 1 AS total_partidas, a.auto_avaliacao, a.avaliacao_jogo,
                su.name AS nome_usuario, vinculo.escola, vinculo.turma
                FROM " . self::TABELA . " a
                LEFT JOIN system_user su ON su.id = a.id_usuario AND a.{$semNaoIdentificado}
                LEFT JOIN {$vinculoEscolar} vinculo ON vinculo.id_usuario = a.id_usuario AND a.{$semNaoIdentificado}
                WHERE a.id = :idPartida
                ORDER BY pontuacao DESC, percentual_acertos DESC, tempo_gasto ASC, origem ASC;";

        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
        foreach ($parametros as $nome => $valor) {
            $stmt->bindValue($nome, $valor, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rankingCompleto = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $top10 = [];
        $posicao = 0;

        // Numera apenas as linhas do ranking; a linha 'atual' recebe a posição que ocuparia
        // (em empate ela vem antes, por causa do "origem ASC")
        foreach ($rankingCompleto as $linha) {
            $item = [
                "id_partida" => (int) $linha['id'],
                "jogador" => $linha['jogador'],
                "nome" => $linha['nome'],
                "pontuacao" => $linha['pontuacao'],
                "percentual_acertos" => $linha['percentual_acertos'],
                "tempo_gasto" => $linha['tempo_gasto'],
                "total_partidas" => $linha['total_partidas'],
                "posicao" => $posicao + 1,
                "nome_usuario" => $linha['nome_usuario'],
                "escola" => $linha['escola'],
                "turma" => $linha['turma']
            ];

            if ($linha['origem'] === 'atual') {
                $jogadorAtual = $item;
                $jogadorAtual['auto_avaliacao'] = $linha['auto_avaliacao'];
                $jogadorAtual['avaliacao_jogo'] = $linha['avaliacao_jogo'];
                continue;
            }

            $posicao++;

            if ($posicao <= 10) {
                $top10[] = $item;
            }
        }

        // O front trata o último item da lista como o jogador atual
        if ($jogadorAtual) {
            $top10[] = $jogadorAtual;
        }

        return $top10;
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