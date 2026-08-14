<?php

namespace Service;

use InvalidArgumentException;
use Repository\GeneralisRepository;
use Repository\PartidasPerguntasRepository;
use Util\ConstantesGenericasUtil;
use Util\ResponseBuilderUtil;

class PartidasPerguntasService
{
    /**
     * @var array|null
     */
    private $dados;
    private $PartidasPerguntasRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->PartidasPerguntasRepository = new PartidasPerguntasRepository();
    }

    /**
     * @return array
     */
    public function listarPartida()
    {
        $id = $this->dados['id'] ?? null;

        $resultado = GeneralisRepository::listarInstancias($id, "partidasPerguntas");
        if($resultado !== null){
            return $resultado;
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LISTAR_TABELA_VAZIA);
    }

    /**
     * @return array
     */
    public function serviceRanking()
    {
        $idPartida = $this->dados['idPartida'] ?? null;

        if($idPartida !== null){
            $resultado = $this->PartidasPerguntasRepository->repositoriRanking($idPartida);

            if(count($resultado) > 0){
                return ResponseBuilderUtil::montarRanking($resultado);
            }

            elseif (count($resultado) === 0){
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RANKING_SEM_REGISTRO);
            }
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RANKING_BODY);
    }

    public function serviceRankingTurma(){

        $email = $this->dados['email'] ?? null;

        if($email !== null){
            $resultado = $this->PartidasPerguntasRepository->repositoriRankingTurma($email);

            if(count($resultado) > 0){
                return ResponseBuilderUtil::montarRanking($resultado);
            }

            elseif (count($resultado) === 0){
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RANKING_SEM_REGISTRO);
            }
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RANKING_BODY);
    }

    /**
     * @return array
     */
    public function serviceSalvarPartida()
    {
        $id = $this->dados['id'] ?? null;
        $jogadorEmail = $this->dados['jogadorEmail'] ?? null;
        $dataHoraInicio = $this->dados['dataHoraInicio'] ?? null;
        $nome = $this->dados['nome'] ?? null;
        $idade = $this->dados['idade'] ?? null;
        $autoAvaliacao = $this->dados['autoAvaliacao'] ?? null;
        $avatar = $this->dados['avatar'] ?? null;
        $tempoGasto = $this->dados['tempoGasto'] ?? null;

        if($id !== null && $jogadorEmail !== null && $dataHoraInicio !== null && $autoAvaliacao !== null && $avatar !== null && $tempoGasto !== null){
            $resultado = $this->PartidasPerguntasRepository->repositorySalvarPartida($id, $jogadorEmail, $dataHoraInicio, $nome, $idade, $autoAvaliacao, $avatar, $tempoGasto);

            if($resultado !== null && $resultado > 0){
                if ($id !== -1){
                    $this->dados['id'] = $resultado;
                    $logPerguntas = new LogPerguntasService($this->dados);
                    $logPerguntas->inserirLogPerguntasService();
                    $this->PartidasPerguntasRepository->repositoryAtualizarAcertoseErros($resultado);
                }

                $jogadas = $this->dados['jogadas'] ?? [];

                $partidaMestre = [
                    'id' => $resultado,
                    'jogadorEmail' => $jogadorEmail,
                    'dataHoraInicio' => $dataHoraInicio,
                    'nome' => $nome,
                    'idade' => $idade,
                    'autoAvaliacao' => $autoAvaliacao,
                    'avatar' => $avatar,
                    'tempoGasto' => $tempoGasto
                ];

                return ResponseBuilderUtil::montarSalvarPartida($partidaMestre, $jogadas);
            }

            else{
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARPARTIDA_SEM_REGISTRO . " Id passado: " . $id);
            }
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARPARTIDA_BODY);
    }
}