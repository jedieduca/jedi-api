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
                "nome"              => $linha['nome'],
                "pontuacao"         => $linha['pontuacao'] ?? "0",
                "percentualAcertos" => $linha['percentual_acertos'] ?? ($linha['percentualAcertos'] ?? "0%"),
                "tempoGasto"        => $linha['tempo_gasto'] ?? ($linha['tempoGasto'] ?? "00:00"),
                "totalPartidas"     => $linha['total_partidas'] ?? ($linha['totalPartidas'] ?? "0"),
                "posicao"           => isset($linha['posicao']) ? (int)$linha['posicao'] : null,
                "autoAvaliacao"     => $linha['auto_avaliacao'] ?? "",
                "avaliacaoJogo"     => $linha['avaliacao_jogo'] ?? "",
                // O nome completo do cadastro não sai da API: só o primeiro nome e a inicial do sobrenome
                "nomeCurto"         => self::nomeCurto($linha['nome_usuario'] ?? ""),
                "escola"            => self::escolaCurta($linha['escola'] ?? ""),
                "turma"             => trim($linha['turma'] ?? "")
            ];
        }

        return $resultado;
    }

    /**
     * Primeiro nome + inicial do último sobrenome: "MARIA EDUARDA FONSECA" -> "Maria F."
     * @param string $nomeCompleto
     * @return string
     */
    private static function nomeCurto(string $nomeCompleto): string
    {
        $nome = mb_convert_case(mb_strtolower(trim($nomeCompleto), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $partes = array_values(array_filter(
            preg_split('/\s+/u', $nome) ?: [],
            fn($parte) => $parte !== '' && !in_array($parte, ['De', 'Da', 'Do', 'Dos', 'Das', 'E'], true)
        ));

        if (count($partes) === 0) {
            return "";
        }

        if (count($partes) === 1) {
            return $partes[0];
        }

        return $partes[0] . ' ' . mb_substr(end($partes), 0, 1, 'UTF-8') . '.';
    }

    /**
     * Nome da escola sem o tipo e com títulos abreviados:
     * "Colégio Estadual Presidente Castelo Branco" -> "Pres. Castelo Branco"
     * @param string $nomeEscola
     * @return string
     */
    private static function escolaCurta(string $nomeEscola): string
    {
        $nome = trim($nomeEscola);
        $nome = preg_replace('/^(Colégio Estadual|Colégio Municipal|Escola Estadual|Escola Municipal|Colégio|Escola)\s+/iu', '', $nome);
        $nome = preg_replace('/\s+-\s+Unidade\s+/iu', ' ', $nome);

        return preg_replace(
            ['/\bPresidente\b/u', '/\bAlmirante\b/u', '/\bProfessora\b/u', '/\bProfessor\b/u'],
            ['Pres.', 'Alm.', 'Profa.', 'Prof.'],
            $nome
        );
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