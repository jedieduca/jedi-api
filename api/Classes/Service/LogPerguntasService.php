<?php

namespace Service;

use Repository\GeneralisRepository;
use Repository\LogPerguntasRepository;
use Util\ConstantesGenericasUtil;
use Util\ResponseBuilderUtil;

class LogPerguntasService
{
    private $dados;
    private $logPerguntasRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->logPerguntasRepository = new LogPerguntasRepository();
    }

    public function listarLogPerguntas()
    {
        $id = $this->dados['id'] ?? null;

        $resultado = GeneralisRepository::listarInstancias($id, "logPerguntas");

        if ($resultado !== null) {
            return $resultado;
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LISTAR_TABELA_VAZIA);
    }

    public function inserirLogPerguntasService()
    {
        $jogadas = $this->dados['jogadas'] ?? null;

        if (is_array($jogadas) && !empty($jogadas)) {
            // Pega a última jogada realizada
            $jogadaAInserir = end($jogadas);

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

            if (
                $id !== null &&
                $jogadorEmail !== null &&
                $dataHoraInicio !== null &&
                $idade !== null &&
                $avatar !== null &&
                $jogadaId !== null &&
                $noticiaId !== null &&
                $avaliacaoCorreta !== null &&
                $tempoResposta !== null &&
                $posicaoAvatar !== null
            ) {
                $resultado = $this->logPerguntasRepository->inserirLogPerguntasRepository(
                    $id,
                    $jogadorEmail,
                    $dataHoraInicio,
                    $idade,
                    $avatar,
                    $jogadaId,
                    $noticiaId,
                    $avaliacaoCorreta,
                    $tempoResposta,
                    $posicaoAvatar
                );

                if ((int)$resultado === -1) {
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARJOGADA_PERGUNTA_SEM_REGISTRO . " Id Pergunta: " . $noticiaId);
                }

                if ($resultado !== null && $resultado !== false) {
                    return ResponseBuilderUtil::montarRespostaGenerica($resultado);
                }

                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_GENERICO);
            }
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SALVARJOGADA_BODY);
    }
}