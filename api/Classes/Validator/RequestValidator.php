<?php

namespace Validator;

use Service\CategoriaService;
use Service\LogPerguntasService;
use Service\Pergunta2Service;
use Service\SystemUserService;
use Service\PartidasPerguntasService;
use Util\ConstantesGenericasUtil;
use Util\RotasUtil;

class RequestValidator
{
    // Classe responsável por executar os requests
    private $request;
    private $corpoRequest;

    /**
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->corpoRequest = RotasUtil::getRequest();
    }

    /**
     * @return mixed
     */
    public function processarRequest()
    {
        $retorno = null;

        $rota = $this->request['rota'] ?? null;
        if ($rota == null){
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_ROTA_VAZIA);
        }

        // Verifica se o método do request é um dos métodos permitidos
        if (in_array($this->request['metodo'], ConstantesGenericasUtil::TIPO_REQUEST, true)) {
            switch ($this->request['metodo']) {
                case 'POST':
                    if (in_array($rota, ConstantesGenericasUtil::TIPO_POST, true)) {
                        $retorno = $this->post();
                    }
                    else {
                        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA . " - Rota: " . $rota . " - Método: " . $this->request['metodo'] );
                    }
                    break;
                case 'GET':
                    if (in_array($rota, ConstantesGenericasUtil::TIPO_GET, true)) {
                        $retorno = $this->get();
                    }
                    else {
                        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA . " - Rota: " . $rota . " - Método: " . $this->request['metodo'] );
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_METODO_SEM_ROTA . " - Metodo: " . $this->request['metodo']);
            }
        }
        else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_METODO . " - Método: " . $this->request['metodo']);
        }

        return $retorno;
    }

    /**
     * @return array|mixed
     */
    private function get()
    {
        $retorno = null;
        $rota = $this->request['rota'];
        $recurso = $this->request['recurso'];

        switch ($rota) {
            case 'PARTIDASPERGUNTAS':
                $partidasPerguntasService = new PartidasPerguntasService($this->request);

                if ($recurso === 'listarPartida'){
                    $retorno = $partidasPerguntasService->listarPartida();
                }
                break;

            case 'LOGPERGUNTAS':
                $logperguntas = new LogPerguntasService($this->request);

                if ($recurso === 'listarLogPergunta'){
                    $retorno = $logperguntas->listarLogPerguntas();
                }
                break;

            case "PERGUNTA2":
                $pergunta2 = new Pergunta2Service($this->request);

                if ($recurso === 'listarPergunta'){
                    $retorno = $pergunta2->listarPergunta();
                }
                break;

            case "CATEGORIA":
                $categoria = new CategoriaService($this->request);

                if ($recurso === 'listarCategorias'){
                    $retorno = $categoria->listarCategorias();
                }
                break;
        }

        if (is_null($retorno)) {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE . " Recurso: " . $recurso);
        }

        return $retorno;
    }

    /**
     * @return mixed
     */
    private function post()
    {
        $retorno = null;
        $rota = $this->request['rota'];
        $recurso = $this->request['recurso'];

        // Mescla os dados da URL/Router com os dados vindos no Corpo JSON do POST
        $dadosCompletos = array_merge(
            is_array($this->request) ? $this->request : [],
            is_array($this->corpoRequest) ? $this->corpoRequest : []
        );

        switch ($rota) {
            case 'SYSTEM_USER':
                $usuariosService = new SystemUserService($dadosCompletos);

                if ($recurso === 'autenticar'){
                    $retorno = $usuariosService->servicePegarUser();
                }
                elseif ($recurso === 'trocarSenha'){
                    $retorno = $usuariosService->alterarSenhaService();
                }
                elseif ($recurso === 'enviaEmail'){
                    $retorno = $usuariosService->enviarEmailService();
                }
                elseif ($recurso === 'cadastrar'){
                    $retorno = $usuariosService->cadastrarUsuarioService();
                }
                elseif ($recurso === 'recuperarSenha'){
                    $retorno = $usuariosService->recuperarSenhaService();
                }
                break;

            case 'PARTIDASPERGUNTAS':
                $partidasPerguntasService = new PartidasPerguntasService($dadosCompletos);

                if ($recurso === 'ranking'){
                    $retorno = $partidasPerguntasService->serviceRanking();
                }
                elseif ($recurso === 'salvarPartida'){
                    $retorno = $partidasPerguntasService->serviceSalvarPartida();
                }
                elseif ($recurso === 'rankingTurma'){
                    $retorno = $partidasPerguntasService->serviceRankingTurma();
                }
                break;

            case 'PERGUNTA2':
                $pergunta2Service = new Pergunta2Service($dadosCompletos);

                if ($recurso === 'sortearPerguntas'){
                    $retorno = $pergunta2Service->pegarPerguntasService();
                }
                break;
        }

        if (is_null($retorno)) {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE . " Recurso: " . $recurso);
        }

        return $retorno;
    }
}