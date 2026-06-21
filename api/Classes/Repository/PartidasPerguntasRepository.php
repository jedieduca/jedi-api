<?php

namespace Repository;

use DB\MySQL;
use PDO;

class PartidasPerguntasRepository
{
    private $MySQL;

    public const TABELA = 'partidasPerguntas';

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

            $sqlGeral = "SELECT idPartida, jogador, ". self::TABELA .". nome, MAX(pontuacao) AS pontuacao, 
                    (SUM(qtdAcertos)/(SUM(qtdAcertos)+SUM(qtdErros)))*100 AS percentualAcertos, 
                    MIN(tempoGasto) AS tempoGasto, COUNT(*) AS totalPartidas
                    FROM " . self::TABELA . " 
                    GROUP BY login
                    UNION ALL
                    SELECT idPartida, jogador, ". self::TABELA .". nome, pontuacao, 
                    (SUM(qtdAcertos)/(SUM(qtdAcertos)+SUM(qtdErros)))*100 AS percentualAcertos, 
                    tempoGasto, COUNT(*) AS totalPartidas FROM " . self::TABELA . " WHERE idPartida = :idPartida
                    ORDER BY pontuacao DESC, percentualAcertos DESC, tempoGasto ASC;";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
            $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
            $stmt->execute();
            $rankingCompleto = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $top10 = [];

            // 2. Numerar TODOS e separar o que precisamos
            foreach ($rankingCompleto as $index => $linha) {
                $posicaoAtual = (int)($index + 1);
                // Monta o objeto com a posição correta
                $item = [
                    "idPartida" => (int) $linha['idPartida'],
                    "nome" => $linha['nome'],
                    "jogador" => $linha['jogador'],
                    "pontuacao" => $linha['pontuacao'],
                    "percentualAcertos" => $linha['percentualAcertos'],
                    "tempoGasto" => $linha['tempoGasto'],
                    "totalPartidas" => $linha['totalPartidas'],
                    "posicao" => $posicaoAtual
                ];

                if($item['idPartida'] == $idPartida){
                    $jogadorAtual = $item;

                    $sqlGeral = "SELECT autoAvaliacao, avaliacaoJogo FROM " . self::TABELA . " WHERE idPartida = :idPartida";
                    $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
                    $stmt->bindValue(':idPartida', $idPartida, PDO::PARAM_INT);
                    $stmt->execute();
                    $avaliacao = $stmt->fetch(\PDO::FETCH_ASSOC);

                    $jogadorAtual['autoAvaliacao'] = $avaliacao['autoAvaliacao'];
                    $jogadorAtual['avaliacaoJogo'] = $avaliacao['avaliacaoJogo'];
                }

                // Se estiver entre os 10 primeiros, vai para o Top 10
                if ($posicaoAtual <= 10) {
                    $top10[] = $item;
                }
            }

            $top10[] = $jogadorAtual;

            return $top10;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function repositoriRankingTurma($email)
    {
        try {
            $jogadorAtual = null;

            // SQL unificado com JOINS acadêmicos e filtro por turma passada via parâmetro
            $sqlGeral = "SELECT 
                        pp.idPartida, pp.jogador, pp.nome, MAX(pp.pontuacao) AS pontuacao, 
                        (SUM(pp.qtdAcertos)/(SUM(pp.qtdAcertos)+SUM(pp.qtdErros)))*100 AS percentualAcertos, 
                        MIN(pp.tempoGasto) AS tempoGasto, COUNT(*) AS totalPartidas
                    FROM " . self::TABELA . " pp
                    INNER JOIN system_user su ON pp.login = su.email
                    INNER JOIN ofertaturmaaluno ota ON su.id = ota.idaluno
                    INNER JOIN turmaoferta tof ON ota.idofertaturma = tof.id
                    INNER JOIN turma t ON tof.idturma = t.id
                    WHERE t.id = :idTurma
                    GROUP BY pp.login

                    UNION ALL

                    SELECT 
                        pp.idPartida, pp.jogador, pp.nome, pp.pontuacao, 
                        (SUM(pp.qtdAcertos)/(SUM(pp.qtdAcertos)+SUM(pp.qtdErros)))*100 AS percentualAcertos, 
                        pp.tempoGasto, COUNT(*) AS totalPartidas 
                    FROM " . self::TABELA . " pp
                    INNER JOIN system_user su ON pp.login = su.email
                    INNER JOIN ofertaturmaaluno ota ON su.id = ota.idaluno
                    INNER JOIN turmaoferta tof ON ota.idofertaturma = tof.id
                    INNER JOIN turma t ON tof.idturma = t.id
                    WHERE pp.idPartida = :idPartida AND t.id = :idTurma
                    GROUP BY pp.idPartida
                    
                    ORDER BY pontuacao DESC, percentualAcertos DESC, tempoGasto ASC";

            $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
            $stmt->bindValue(':idPartida', $idPartida, \PDO::PARAM_INT);
            $stmt->bindValue(':idTurma', $idTurma, \PDO::PARAM_INT);
            $stmt->execute();
            $rankingCompleto = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $top10 = [];

            foreach ($rankingCompleto as $index => $linha) {
                $posicaoAtual = (int)($index + 1);

                $item = [
                    "idPartida" => (int) $linha['idPartida'],
                    "nome" => $linha['nome'],
                    "jogador" => $linha['jogador'],
                    "pontuacao" => $linha['pontuacao'],
                    "percentualAcertos" => $linha['percentualAcertos'],
                    "tempoGasto" => $linha['tempoGasto'],
                    "totalPartidas" => $linha['totalPartidas'],
                    "posicao" => $posicaoAtual
                ];

                if($item['idPartida'] == $idPartida){
                    // Busca dados de avaliação da partida específica
                    $sqlAval = "SELECT autoAvaliacao, avaliacaoJogo FROM " . self::TABELA . " WHERE idPartida = :idPartida";
                    $stmtAval = $this->MySQL->getDb()->prepare($sqlAval);
                    $stmtAval->bindValue(':idPartida', $idPartida, \PDO::PARAM_INT);
                    $stmtAval->execute();
                    $avaliacao = $stmtAval->fetch(\PDO::FETCH_ASSOC);

                    $item['autoAvaliacao'] = $avaliacao['autoAvaliacao'] ?? null;
                    $item['avaliacaoJogo'] = $avaliacao['avaliacaoJogo'] ?? null;

                    $jogadorAtual = $item;
                }

                if ($posicaoAtual <= 10) {
                    $top10[] = $item;
                }
            }

            // Se o jogador não estiver no Top 10, adicionamos ele ao final da lista
            // Usamos um loop simples para verificar se o ID já está no array
            $noTop10 = true;
            foreach ($top10 as $t) {
                if ($t['idPartida'] == $idPartida) {
                    $noTop10 = false;
                    break;
                }
            }

            if ($jogadorAtual && $noTop10) {
                $top10[] = $jogadorAtual;
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
     * @param $tempoGasto
     * @return false|int|mixed|string
     */
    public function repositorySalvarPartida($id, $jogadorEmail, $dataHoraInicio, $nome, $idade, $autoAvaliacao, $avatar, $tempoGasto)
    {
        if ($nome === null) {
            $nome = $jogadorEmail;
        }

        if ($idade === null) {
            $idade = 0;
        }

        try {
            if($id === -1){
                $sqlGeral = "INSERT INTO " . self::TABELA . " (dtJogo, login, tema,jogador, idade, pontuacao, tempoGasto, autoAvaliacao, avaliacaoJogo, nome)
                    SELECT :dataHoraInicio, :jogadorEmail, 17, :avatar, :idade, -1, :tempoGasto, :autoAvaliacao, 'Noob', :nome";
                $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempoGasto', $tempoGasto);
                $stmt->bindParam(':autoAvaliacao', $autoAvaliacao);
                $stmt->bindParam(':nome', $nome);
                $stmt->execute();

                $resultado = $this->MySQL->getDb()->lastInsertId();
            }

            if ($id !== -1){
                $sqlGeral = "UPDATE " . self::TABELA . " 
                    SET `dtJogo` = :dataHoraInicio, 
                        `login` = :jogadorEmail,
                        `jogador` = :avatar, 
                        `idade` = :idade, 
                        `pontuacao` = 150,
                        `qtdAcertos` = (SELECT COUNT(*) FROM logPerguntas lp WHERE lp.idPartida = :id AND lp.tema = 17 AND lp.respCerta = lp.respDada),
                        `qtdErros` = (SELECT COUNT(*) FROM logPerguntas lp WHERE lp.idPartida = :id AND lp.tema = 17 AND lp.respCerta != lp.respDada),
                        `tempoGasto` = :tempoGasto, 
                        `autoAvaliacao` = :autoAvaliacao,
                        `avaliacaoJogo` = 'Pro', 
                        `nome` = :nome 
                        WHERE `idPartida` = :id";

                $stmt = $this->MySQL->getDb()->prepare($sqlGeral);

                $stmt->bindParam(':dataHoraInicio', $dataHoraInicio);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':jogadorEmail', $jogadorEmail);
                $stmt->bindParam(':avatar', $avatar);
                $stmt->bindParam(':idade', $idade);
                $stmt->bindParam(':tempoGasto', $tempoGasto);
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
        $sqlGeral = "(SELECT (p.qtdAcertos / t.total) * 100 FROM partidasPerguntas p JOIN (SELECT COUNT(*) as total FROM logPerguntas WHERE idPartida = :id) t WHERE p.idPartida = :id)";
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
                    `qtdAcertos` = (SELECT COUNT(*) FROM logPerguntas WHERE idPartida = :id AND tema = 17 AND respCerta = respDada),
                    
                    `qtdErros` = (SELECT COUNT(*) FROM logPerguntas WHERE idPartida = :id AND tema = 17 AND respCerta != respDada),
                    
                    `pontuacao` = (
                        SELECT pontos FROM (
                            SELECT (100000 * (p2.qtdAcertos / t.total) + (100 * t.total)) AS pontos 
                            FROM " . self::TABELA . " p2 
                            JOIN (SELECT COUNT(*) as total FROM logPerguntas WHERE idPartida = :id) t 
                            WHERE p2.idPartida = :id
                        ) AS temp
                    ),
                    
                    `avaliacaoJogo` = :avaliacao
                WHERE idPartida = :id";
        $stmt = $this->MySQL->getDb()->prepare($sqlGeral);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':avaliacao', $aval);
        $stmt->execute();
    }
}