<?php

namespace Validator;

use Service\CategoriaService;
use Service\LogPerguntasService;
use Service\Pergunta2Service;
use Util\JsonUtil;
use Service\SystemUserService;
use Service\PartidasPerguntasService;
use Util\ConstantesGenericasUtil;
use Util\RotasUtil;

class RequestValidator
{
    //Classe responsável por executar os request
    private $request;
    private $corpoRequest;

    /**
     * @param $request
     */
    public function __construct($request)
    {

        $this->request = $request;
        $this->corpoRequest = \Util\RotasUtil::getRequest();
    }

    /**
     * @return mixed
     */
    public function processarRequest()
    {
        //Função que própriamente processar o Request enviado
        $retorno = null;

        $rota = $this->request['rota'];
        if ($rota == null){
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_ROTA_VAZIA);
        }

        //Verifica se o método do request é um dos métodos permitidos
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
                    else{
                        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA . " - Rota: " . $rota . " - Método: " . $this->request['metodo'] );
                    }
                break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_METODO_SEM_ROTA . " - Metodo: " . $this->request['metodo']);
            }
        }
        else{
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

                    if($recurso === 'listarPartida'){
                        $retorno = $partidasPerguntasService->listarPartida();
                    }
                break;
                case 'LOGPERGUNTAS':
                    $logperguntas = new LogPerguntasService($this->request);

                    if($recurso === 'listarLogPergunta'){
                        $retorno = $logperguntas->listarLogPerguntas();
                    }
                break;
                case "PERGUNTA2":
                    $pergunta2 = new Pergunta2Service($this->request);

                    if($recurso === 'listarPergunta'){
                        $retorno = $pergunta2->listarPergunta();
                    }
                break;
                case "CATEGORIA":
                    $categoria = new CategoriaService($this->request);

                    if($recurso === 'listarCategorias'){
                        $retorno = $categoria->listarCategorias();
                    }
                break;
        }

        if (is_null($retorno)) {
            throw new \InvalidArgumentException( ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE . " Recurso: " . $recurso);
        }

        return $retorno;
    }

    /**
     * @return string
     */
    private function post()
    {
        $retorno = null;
        $rota = $this->request['rota'];
        $recurso = $this->request['recurso'];

        switch ($rota) {
            case 'SYSTEM_USER':
                $usuariosService = new SystemUserService($this->corpoRequest);

                if($recurso === 'autenticar'){
                    $retorno = $usuariosService->servicePegarUser();
                }
                elseif ($recurso === 'trocarSenha'){
                    $retorno = $usuariosService->alterarSenhaService();
                }
            break;

            case 'PARTIDASPERGUNTAS':
                $partidasPerguntasService = new PartidasPerguntasService($this->corpoRequest);

                if($recurso === 'ranking'){
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
                $pergunta2Service = new Pergunta2Service($this->corpoRequest);

                if($recurso === 'sortearPerguntas'){
                    $retorno = $pergunta2Service->pegarPerguntasService();
                }
            break;
        }

        if (is_null($retorno)) {
            throw new \InvalidArgumentException( ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE . " Recurso: " . $recurso);
        }

        return $retorno;
    }
}