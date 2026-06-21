<?php

namespace Service;

use http\Exception\InvalidArgumentException;
use Repository\GeneralisRepository;
use Repository\Pergunta2Repository;
use Util\ConstantesGenericasUtil;

class Pergunta2Service
{
    private $dados;
    private $Pergunta2Repository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->Pergunta2Repository = new Pergunta2Repository();
    }

    public function listarPergunta()
    {
        $id = $this->dados['id'] ?? null;

        $resultado = GeneralisRepository::listarInstancias($id, "pergunta2");

        if($resultado !== null){
            return $resultado;
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LISTAR_TABELA_VAZIA);
    }
    public function pegarPerguntasService()
    {
        $quantidade = $this->dados['quantidade'] ?? null;
        $categoria = $this->dados['categoria'] ?? null;

        if($quantidade !== null){
            if($quantidade > 0){
                $resultado = $this->Pergunta2Repository->sortearPerguntas($quantidade, $categoria);

                if(count($resultado) !== 0){
                    return $resultado;
                }
            }

            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SORTEARPERGUNTAS_QUANTIDADE . " Quantidade Passada: " . $quantidade);

        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_SORTEARPERGUNTAS_BODY);
    }
}