<?php

namespace Service;

use Repository\CategoriaRepository;
use Repository\GeneralisRepository;

class CategoriaService
{
    private $dados;
    private $Pergunta2Repository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->Pergunta2Repository = new CategoriaRepository();
    }

    public function listarCategorias(){
        $id = $this->dados['id'] ?? null;

        $resultado = GeneralisRepository::listarInstancias($id, "categoria");

        if($resultado !== null){
            return $resultado;
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LISTAR_TABELA_VAZIA);
    }
}