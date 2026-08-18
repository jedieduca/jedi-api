<?php

namespace Util;

class ResponseBuilderUtil
{
    public static function montarRespostaGenerica($retorno): array
    {
        return [
            "resposta" => $retorno
        ];
    }

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
            "id"           => (String)$usuario['id'],
            "name"         => $usuario['name'] ?? "",
            "login"        => $usuario['login'] ?? "",
            "email"        => $usuario['email'] ?? "",
            "frontpage_id" => (String)$usuario['frontpage_id'],
            "active"       => $usuario['active']
        ];
    }

    /**
     * Formata os dados no momento de "Salvar Partida"
     * @param array $partida
     * @param array $jogadas
     * @return array
     */
    public static function montarSalvarPartida($partida): array
    {
        // Garante extrair o ID caso receba um array, objeto ou valor numérico/string
        $id = is_array($partida) ? ($partida['id'] ?? $partida[0] ?? 0) : $partida;

        return [
            "id" => (int) $id
        ];
    }
}