<?php

namespace Util;

class RotasUtil
{
    /**
     * @return array
     */
    public static function getRotas()
    {
        $urls = self::getUrls();

        $request['metodo']  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $request['rota']    = strtoupper($urls[0] ?? '');
        $request['recurso'] = $urls[1] ?? null;
        $request['id']      = self::converteSimbolos($urls[2] ?? null);

        return $request;
    }

    /**
     * @return array
     */
    public static function getRequest()
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($metodo === 'POST' || $metodo === 'PUT') {
            // Lê o corpo bruto da requisição (JSON do Insomnia/Postman)
            $corpoRaw = file_get_contents('php://input');
            $dadosJson = json_decode($corpoRaw, true);

            // Se o JSON for válido, usa ele; caso contrário, tenta o $_POST tradicional
            return is_array($dadosJson) ? $dadosJson : $_POST;
        }

        return $_GET;
    }

    /**
     * @return array
     */
    public static function getUrls()
    {
        // Pega a URI da requisição
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        // Remove Query Strings (?param=valor) da URI se existirem
        $uriSemQuery = strtok($requestUri, '?') ?: '';

        // Recupera o diretório do projeto garantindo que não seja null
        $dirProjeto = defined('DIR_PROJETO') ? DIR_PROJETO : (getenv('DIR_PROJETO') ?: '');

        // Remove a pasta do projeto da URI sem passar parâmetros nulos ao str_replace
        if (!empty($dirProjeto)) {
            $uriSemQuery = str_replace('/' . trim($dirProjeto, '/') . '/', '/', $uriSemQuery);
        }

        // Limpa barras das extremidades
        $uriLimpa = trim($uriSemQuery, '/');

        // Retorna o array de segmentos da URL
        return $uriLimpa !== '' ? explode('/', $uriLimpa) : [];
    }

    /**
     * @param mixed $url
     * @return array|string|null
     */
    private static function converteSimbolos($url)
    {
        if ($url === null || !is_string($url)) {
            return $url;
        }

        $url = str_replace('&', ' AND ', $url);
        $url = str_replace('|', ' OR ', $url);
        $url = str_replace('%3E', '>', $url);
        $url = str_replace('%3C', '<', $url);
        $url = str_replace('%22', '"', $url);

        return $url;
    }
}