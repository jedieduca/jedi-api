<?php

namespace Util;

class RotasUtil
{
    /**
     * @return array
     */
    public static function getRotas(){
        $urls = self::getUrls();

        $request['metodo'] = $_SERVER['REQUEST_METHOD'];
        $request['rota'] = strtoupper($urls[1]);
        $request['recurso'] = $urls[2] ?? null;
        $request['id'] = $urls[3] ?? null;

        $request['id'] = self::converteSimbolos($urls[3] ?? null);
        return $request;
    }

    /**
     * @return array
     */
    public static function getRequest()
    {
        $metodo = $_SERVER['REQUEST_METHOD'];

        if ($metodo === 'POST' || $metodo === 'PUT') {
            // ESSENCIAL: Lê o corpo bruto da requisição (JSON do Insomnia)
            $corpoRaw = file_get_contents('php://input');
            $dadosJson = json_decode($corpoRaw, true);
            // Se o JSON for válido, usa ele. Se não, tenta o $_POST tradicional
            return is_array($dadosJson) ? $dadosJson : $_POST;
        }
        return $_GET;
    }

    /**
     * @return false|string[]
     */
    public static function getUrls(){
        //pera a URI e separa para a URL
        $uri = str_replace('/' . DIR_PROJETO . '/', '', $_SERVER['REQUEST_URI']);
        return explode('/', trim($uri, '/'));
    }

    /**
     * @param $url
     * @return array|string|string[]
     */
    private static function converteSimbolos($url)
    {
        $url = str_replace('&', ' AND ', $url);
        $url = str_replace('|', ' OR ', $url);
        $url = str_replace('%3E', '>', $url);
        $url = str_replace('%3C', '<', $url);
        $url = str_replace('%22', '"', $url);

        return $url;
    }
}