<?php

namespace Util;

class ResponseBuilderUtil
{
    /**
     * Formata os dados para o endpoint "Sortear Perguntas" / "Listar Perguntas"
     * @param array $dadosBanco
     * @return array
     */
    public static function montarPerguntas(array $dadosBanco): array
    {
        $lista = isset($dadosBanco['id']) || isset($dadosBanco['id_tema']) ? [$dadosBanco] : $dadosBanco;
        $resultado = [];

        foreach ($lista as $linha) {
            $resultado[] = [
                "idtema"           => isset($linha['id_tema']) ? (int)$linha['id_tema'] : (isset($linha['idtema']) ? (int)$linha['idtema'] : null),
                "id"               => isset($linha['id']) ? (int)$linha['id'] : null,
                "pergunta"         => $linha['pergunta'] ?? "",
                "respcerta"        => $linha['resp_certa'] ?? ($linha['respcerta'] ?? ""),
                "resp2"            => $linha['resp_2'] ?? ($linha['resp2'] ?? ""),
                "resp3"            => $linha['resp_3'] ?? ($linha['resp3'] ?? ""),
                "resp4"            => $linha['resp_4'] ?? ($linha['resp4'] ?? ""),
                "caminhoimagem"    => $linha['caminho_imagem'] ?? ($linha['caminhoimagem'] ?? ""),
                "caract_proposta"  => $linha['caract_proposta'] ?? "",
                "analise_proposta" => $linha['analise_proposta'] ?? "",
                "analise_gpt"      => $linha['analise_gpt'] ?? "",
                "analise_gemini"   => $linha['analise_gemini'] ?? "",
                "origem_analise"   => $linha['origem_analise'] ?? "",
                "fala_gpt"         => $linha['fala_gpt'] ?? "",
                "fala_gemini"      => $linha['fala_gemini'] ?? "",
                "origem_fala"      => $linha['origem_fala'] ?? "",
                "fala_proposta"    => $linha['fala_proposta'] ?? "",
                "publica"          => $linha['publica'] ?? ""
            ];
        }

        return $resultado;
    }

    /**
     * Formata a listagem e pontuação para o "Ranking"
     * @param array $dadosBanco
     * @return array
     */
    public static function montarRanking(array $dadosBanco): array
    {
        $lista = isset($dadosBanco['id_partida']) || isset($dadosBanco['idPartida']) || isset($dadosBanco['jogador']) ? [$dadosBanco] : $dadosBanco;
        $resultado = [];

        foreach ($lista as $linha) {
            $resultado[] = [
                "idPartida"         => isset($linha['id_partida']) ? (int)$linha['id_partida'] : (isset($linha['idPartida']) ? (int)$linha['idPartida'] : null),
                "jogador"           => $linha['jogador'] ?? "",
                "pontuacao"         => $linha['pontuacao'] ?? "0",
                "percentualAcertos" => $linha['percentual_acertos'] ?? ($linha['percentualAcertos'] ?? "0%"),
                "tempoGasto"        => $linha['tempo_gasto'] ?? ($linha['tempoGasto'] ?? "00:00"),
                "totalPartidas"     => $linha['total_partidas'] ?? ($linha['totalPartidas'] ?? "0"),
                "posicao"           => isset($linha['posicao']) ? (int)$linha['posicao'] : null
            ];
        }

        return $resultado;
    }

    /**
     * Formata a resposta da rota "Autenticar"
     * @param array $usuario
     * @return array
     */
    public static function montarAutenticar(array $usuario): array
    {
        return [
            "id"           => isset($usuario['id']) ? (int)$usuario['id'] : null,
            "name"         => $usuario['name'] ?? "",
            "login"        => $usuario['login'] ?? "",
            "email"        => $usuario['email'] ?? "",
            "frontpage_id" => isset($usuario['frontpage_id']) ? (int)$usuario['frontpage_id'] : null,
            "active"       => isset($usuario['active']) ? (int)$usuario['active'] : 0
        ];
    }

    /**
     * Formata os dados no momento de "Salvar Partida"
     * @param array $partida
     * @param array $jogadas
     * @return array
     */
    public static function montarSalvarPartida(array $partida, array $jogadas = []): array
    {
        $jogadasFormatadas = [];

        foreach ($jogadas as $jogada) {
            $jogadasFormatadas[] = [
                "jogadaId"         => isset($jogada['jogada_id']) ? (int)$jogada['jogada_id'] : (isset($jogada['jogadaId']) ? (int)$jogada['jogadaId'] : null),
                "noticiaId"        => isset($jogada['noticia_id']) ? (int)$jogada['noticia_id'] : (isset($jogada['noticiaId']) ? (int)$jogada['noticiaId'] : null),
                "avaliacaoCorreta" => isset($jogada['avaliacao_correta']) ? (int)$jogada['avaliacao_correta'] : (isset($jogada['avaliacaoCorreta']) ? (int)$jogada['avaliacaoCorreta'] : null),
                "tempoResposta"    => isset($jogada['tempo_resposta']) ? (int)$jogada['tempo_resposta'] : (isset($jogada['tempoResposta']) ? (int)$jogada['tempoResposta'] : null),
                "posicaoAvatar"    => $jogada['posicao_avatar'] ?? ($jogada['posicaoAvatar'] ?? "")
            ];
        }

        return [
            "id"             => isset($partida['id']) ? (int)$partida['id'] : null,
            "jogadorEmail"   => $partida['jogador_email'] ?? ($partida['jogadorEmail'] ?? ""),
            "dataHoraInicio" => $partida['data_hora_inicio'] ?? ($partida['dataHoraInicio'] ?? date('Y-m-d H:i:s')),
            "nome"           => $partida['nome'] ?? "",
            "idade"          => isset($partida['idade']) ? (int)$partida['idade'] : null,
            "autoAvaliacao"  => $partida['auto_avaliacao'] ?? ($partida['autoAvaliacao'] ?? ""),
            "avatar"         => $partida['avatar'] ?? "",
            "tempoGasto"     => isset($partida['tempo_gasto']) ? (int)$partida['tempoGasto'] : (isset($partida['tempoGasto']) ? (int)$partida['tempoGasto'] : null),
            "jogadas"        => $jogadasFormatadas
        ];
    }
}