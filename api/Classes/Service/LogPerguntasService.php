<?php

namespace Service;

use Repository\GeneralisRepository;
use Repository\LogPerguntasRepository;
use Util\ConstantesGenericasUtil;

class LogPerguntasService
{
    private $dados;
    private $logPerguntasRepository;

    public function __construct($dados = []){
        $this->dados = $dados;
        $this->logPerguntasRepository = new LogPerguntasRepository();
    }
    public function listarLogPerguntas()
    {
        $id = $this->dados['id'] ?? null;

            $resultado = GeneralisRepository::listarInstancias($id, "logPerguntas");

            if($resultado !== null){
                return $resultado;
            }

            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LISTAR_TABELA_VAZIA);

    }
    public function inserirLogPerguntasService(){

        $jogadaAInserir = $this->dados['jogadas'] ?? null;
        $jogadaAInserir = end($jogadaAInserir);

        $id = $this->dados['id'] ?? null;
        $jogadorEmail = $this->dados['jogadorEmail'] ?? null;
        $dataHoraInicio = $this->dados['dataHoraInicio'] ?? null;
        $idade = $this->dados['idade'] ?? null;
        $avatar = $this->dados['avatar'] ?? null;

        $jogadaId = $jogadaAInserir['jogadaId'] ?? null;
        $noticiaId = $jogadaAInserir['noticiaId'] ?? null;
        $avaliacaoCorreta = $jogadaAInserir['avaliacaoCorreta'] ?? null;
        $tempoResposta = $jogadaAInserir['tempoResposta'] ?? null;
        $posicaoAvatar = $jogadaAInserir['posicaoAvatar'] ?? null;

        if($id !== null && $jogadorEmail !== null && $dataHoraInicio !== null && $idade !== null && $avatar !== null && $jogadaId !== null && $noticiaId !== null && $avaliacaoCorreta !== null && $tempoResposta !== null && $posicaoAvatar !== null){
            $resultado = $this->logPerguntasRepository->inserirLogPerguntasRepository($id , $jogadorEmail , $dataHoraInicio, $idade , $avatar , $jogadaId , $noticiaId , $avaliacaoCorreta , $tempoResposta , $posicaoAvatar);

            if($resultado === -1){
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARJOGADA_PERGUNTA_SEM_REGISTRO . " Id Pergunta: " . $noticiaId);
            }

            elseif ($resultado !== null){
                return $resultado;
            }

            else{
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_GENERICO);
            }


        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARJOGADA_BODY);

    }
}