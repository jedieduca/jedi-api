<?php
// arquivo para constantes genéricas do sistema (alterar conforme a necessidade)
namespace Util;

abstract class ConstantesGenericasUtil
{
    /* REQUESTS */
    public const TIPO_REQUEST = ['GET', 'POST', 'DELETE', 'PUT'];
    public const TIPO_GET = ['PARTIDASPERGUNTAS', 'PERGUNTA2', 'LOGPERGUNTAS', 'CATEGORIA']; //Aqui eu adiciono as tabelas que aceitam requisições do tipo GET
    public const TIPO_POST = ['SYSTEM_USER', 'PARTIDASPERGUNTAS', 'PERGUNTA2', 'LOGPERGUNTAS'];
    public const TIPO_DELETE = [''];
    public const TIPO_PUT = ['PARTIDASPERGUNTAS', 'LOGPERGUNTAS'];

    /* ERROS MÉTODO */
    public const MSG_ERRO_METODO = 'Método HTTP não permitida!';
    public const MSG_ERRO_METODO_SEM_ROTA = 'Método HTTP sem rota!';

    /* ERROS ROTA*/
    public const MSG_ERRO_TIPO_ROTA = 'A rota não tem permissão para fazer esse Método HTTP!';
    public const MSG_ERRO_ROTA_VAZIA = 'A requisição precisa de uma rota!';

    /* ERROS RECURSO */
    public const MSG_ERRO_RECURSO_INEXISTENTE = 'Recurso inexistente!';

    /* ERROS PADRÃO */
    public const MSG_ERRO_GENERICO = 'Algum erro ocorreu na requisição!';
    public const MSG_ERRO_SEM_RETORNO = 'Nenhum registro encontrado!';

    public const MSG_ERRO_NAO_AFETADO = 'Nenhum registro afetado!';
    public const MSG_ERRO_USER_BODY = 'Body inválido. Envie {"login": "login", "password": "senha"}.';
    public const MSG_ERRO_USER_NAO_ATIVO = 'Usuário não ativo!';
    public const MSG_ERRO_USER_NAO_REGISTRADO = 'Usuário não registrado!';
    public const MSG_ERRO_JSON_VAZIO = 'O Corpo da requisição não pode ser vazio!';

    /* SUCESSO */
    public const MSG_DELETADO_SUCESSO = 'Registro deletado com Sucesso!';
    public const MSG_ATUALIZADO_SUCESSO = 'Registro atualizado com Sucesso!';

    /* RECUSO SORTEAR PERGUNTAS*/
    public const MSG_ERRO_SORTEARPERGUNTAS_BODY = 'Body inválido. Envie {"quantidade": N}';
    public const MSG_ERRO_SORTEARPERGUNTAS_QUANTIDADE_REGISTROS = 'Quantidade de registros inferior a quantidade pedida!';
    public const MSG_ERRO_SORTEARPERGUNTAS_QUANTIDADE = 'A quantidade precisa ser maior que 0!';

    /* RECURSO USUARIOS */
    public const MSG_ERRO_LOGIN_EXISTENTE = 'Login já existente!';
    public const MSG_ERRO_LOGIN_SENHA_OBRIGATORIO = 'Login e Senha são obrigatórios!';

    /*RECURSO SALVAR PARTIDA*/
    public const MSG_ERRO_SALVARPARTIDA_BODY = 'Body inválido. Envie {"id": N, "jogadorEmail": "email", "dataHoraInicio": "Data Hora",
    "nome": "nome" (Opicional), "idade": N (Opicional), "autoAvaliacao": "Auto Avaliação", "avatar": "Nome do avatar", "tempoGasto": N,
    "jogadas": [{"jogadaId": N, "noticiaId": N, "avaliacaoCorreta": bool, "tempoResposta": N, "posicaoAvatar": N}]
    }';
    public const MSG_ERRO_SALVARPARTIDA_SEM_REGISTRO = 'Nenhum usuário encontrado!';

    /*RECURSO SALVAR JOGADA*/
    public const MSG_ERRO_SALVARJOGADA_BODY = 'Body inválido. Envie {"jogadaId": N, "noticiaId": N, "avaliacaoCorreta": bool, "tempoResposta": N, "posicaoAvatar": N}';
    public const MSG_ERRO_SALVARJOGADA_PERGUNTA_SEM_REGISTRO = 'Pergunta sem registro!';

    /* RECURSOS RANKING */
    public const MSG_ERRO_RANKING_SEM_REGISTRO = 'Partida sem registro!';

    public const MSG_ERRO_RANKING_BODY = 'Body inválido. Envie {"idPartida": N} ou {"idPartida": N, "jogador": "jogador"}';

    public const MSG_ERRO_ID_TEMA_OBRIGATORIO = 'O ID do tema é obrigatório!';

    /*Listar*/
    public const MSG_ERRO_LISTAR_TABELA_VAZIA = 'Nenhum registro encontrado!';
    public const MSG_ERRO_LISTAR_TABELA_ID = 'É necessário passar o id!';

    /* RETORNO JSON */
    const TIPO_SUCESSO = 'sucesso';
    const TIPO_ERRO = 'erro';

    /* OUTRAS */
    public const SIM = 'S';
    public const TIPO = 'tipo';
    public const RESPOSTA = 'resposta';
}