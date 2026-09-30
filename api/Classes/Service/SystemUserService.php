<?php

namespace Service;

use InvalidArgumentException;
use Repository\PasswordResetRepository;
use Repository\SystemUserRepository;
use Util\ConstantesGenericasUtil;
use Util\MailUtil;
use Util\ResponseBuilderUtil;

class SystemUserService
{
    private const RESET_VALIDADE_MINUTOS = 60;
    private const RESET_JANELA_MINUTOS = 60;
    private const RESET_LIMITE_POR_USUARIO = 3;
    private const RESET_LIMITE_POR_IP = 10;
    private const SENHA_TAMANHO_MINIMO = 8;

    private $dados;
    private $SystemUserRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->SystemUserRepository = new SystemUserRepository();
    }

    /**
     * @return array
     */
    public function servicePegarUser()
    {
        $login = $this->dados['login'] ?? null;
        $password = $this->dados['password'] ?? null;

        if ($login !== null || $password !== null) {
            $password = md5($password);

            $resultado = $this->SystemUserRepository->repositoryPegarUser($login, $password);

            if ($resultado === null || $resultado === false) {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_NAO_REGISTRADO);
            }

            if (isset($resultado['active']) && $resultado['active'] === 'N') {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_NAO_ATIVO);
            }

            return ResponseBuilderUtil::montarAutenticar($resultado);
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_BODY);
    }

    /**
     * @return mixed|void
     */
    public function alterarSenhaService()
    {
        $login = $this->dados['login'] ?? null;
        $senhaAntiga = $this->dados['senhaAntiga'] ?? null;
        $senhaNova = $this->dados['senhaNova'] ?? null;

        if ($login !== null || $senhaAntiga !== null) {
            $senhaAntiga = md5($senhaAntiga);

            $resultado = $this->SystemUserRepository->repositoryPegarUser($login, $senhaAntiga);

            if ($resultado === null || $resultado === false) {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_NAO_REGISTRADO);
            }

            if (isset($resultado['active']) && $resultado['active'] === 'N') {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_NAO_ATIVO);
            }

            if ($senhaNova !== null) {
                $senhaNova = md5($senhaNova);

                $resultado = $this->SystemUserRepository->alterarSenha($login, $senhaAntiga, $senhaNova);

                return $resultado;
            }
        }
    }

    public function cadastrarUsuarioService()
    {
        $nome  = $this->dados['nome'] ?? null;
        $senha = $this->dados['senha'] ?? null;
        $login = $this->dados['login'] ?? null;
        $email = $this->dados['email'] ?? null;

        if ($email !== null && $nome !== null && $senha !== null && $login !== null) {
            $resultado = $this->SystemUserRepository->repositoryCadastrarUsurario($login, md5($senha), $email, $nome);

            return ResponseBuilderUtil::montarRespostaGenerica($resultado);
        }
    }

    /**
     * Envia um link de redefinição para o e-mail informado.
     * A resposta é sempre a mesma para não revelar se o e-mail está cadastrado.
     */
    public function recuperarSenhaService(): array
    {
        $email = trim((string) ($this->dados['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["resposta" => 0];
        }

        try {
            $frontendUrl = rtrim((string) (getenv('FRONTEND_URL') ?: ''), '/');
            if ($frontendUrl === '') {
                throw new \RuntimeException('FRONTEND_URL não definida no .env');
            }

            $usuario = $this->SystemUserRepository->repositoryPegarUserPorEmail($email);
            if (!$usuario || ($usuario['active'] ?? 'Y') === 'N') {
                return ["resposta" => 1];
            }

            $idUsuario = (int) $usuario['id'];
            $ip = $this->obterIpCliente();
            $resetRepository = new PasswordResetRepository();

            // Limite de pedidos: ignora silenciosamente para não revelar nada ao solicitante
            if ($resetRepository->contarPedidosRecentesUsuario($idUsuario, self::RESET_JANELA_MINUTOS) >= self::RESET_LIMITE_POR_USUARIO
                || ($ip !== null && $resetRepository->contarPedidosRecentesIp($ip, self::RESET_JANELA_MINUTOS) >= self::RESET_LIMITE_POR_IP)) {
                return ["resposta" => 1];
            }

            // Somente o hash do token é gravado; o token puro segue apenas no e-mail
            $token = bin2hex(random_bytes(32));
            $resetRepository->criarToken($idUsuario, hash('sha256', $token), self::RESET_VALIDADE_MINUTOS, $ip);

            $link = $frontendUrl . '/?resetToken=' . $token;
            $nome = (string) ($usuario['name'] ?? '');

            MailUtil::enviarHtml(
                $usuario['email'],
                $nome,
                'Redefinição de senha - JEDi Educa',
                $this->montarEmailRecuperacao($nome, $link),
                "Olá, {$nome}.\n\nPara criar uma nova senha no JEDi Educa, acesse: {$link}\n\n"
                . "O link é válido por " . self::RESET_VALIDADE_MINUTOS . " minutos e pode ser usado uma única vez.\n"
                . "Se você não fez essa solicitação, ignore este e-mail. Sua senha atual continua valendo."
            );
        } catch (\Throwable $e) {
            $causa = $e->getPrevious() ? ' | ' . $e->getPrevious()->getMessage() : '';
            error_log('[recuperarSenha] ' . $e->getMessage() . $causa);
        }

        return ["resposta" => 1];
    }

    /**
     * Grava a nova senha a partir de um token de redefinição válido
     */
    public function redefinirSenhaService(): array
    {
        $token = trim((string) ($this->dados['token'] ?? ''));
        $senha = (string) ($this->dados['senha'] ?? '');

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RESET_LINK_INVALIDO);
        }
        if (mb_strlen($senha) < self::SENHA_TAMANHO_MINIMO) {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RESET_SENHA_CURTA);
        }

        $resetRepository = new PasswordResetRepository();
        $registro = $resetRepository->buscarTokenValido(hash('sha256', $token));
        if (!$registro || $registro['active'] === 'N') {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RESET_LINK_INVALIDO);
        }

        try {
            // md5 mantido por compatibilidade com o login do adm (Adianti 7.6) e da API
            $trocou = $resetRepository->consumirTokenETrocarSenha(
                (int) $registro['id'],
                (int) $registro['system_user_id'],
                md5($senha)
            );
        } catch (\RuntimeException $e) {
            $causa = $e->getPrevious() ? ' | ' . $e->getPrevious()->getMessage() : '';
            error_log('[redefinirSenha] ' . $e->getMessage() . $causa);
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RESET_FALHA);
        }

        if (!$trocou) {
            throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RESET_LINK_INVALIDO);
        }

        return ["resposta" => 1];
    }

    /**
     * IP do solicitante. Atrás do proxy reverso (rede Docker), o IP real vem no X-Forwarded-For.
     */
    private function obterIpCliente(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $conexaoViaProxy = $ip !== null
            && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if ($conexaoViaProxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            $ultimoIp = end($ips);
            if (filter_var($ultimoIp, FILTER_VALIDATE_IP)) {
                return $ultimoIp;
            }
        }

        return $ip;
    }

    private function montarEmailRecuperacao(string $nome, string $link): string
    {
        $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $validade = self::RESET_VALIDADE_MINUTOS;

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redefinição de senha - JEDi Educa</title>
</head>
<body style="margin: 0; padding: 24px; background: #ffffff;">
<div style="font-family: Arial, sans-serif; max-width: 520px; margin: auto; color: #222;">
  <h2 style="color: #5b21b6;">Redefinição de senha</h2>
  <p>Olá, {$nome}.</p>
  <p>Recebemos uma solicitação para criar uma nova senha no <strong>JEDi Educa</strong>.</p>
  <p style="text-align: center; margin: 28px 0;">
    <a href="{$link}" style="background: #5b21b6; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none;">Criar nova senha</a>
  </p>
  <p>O link é válido por {$validade} minutos e pode ser usado uma única vez.</p>
  <p>Se você não fez essa solicitação, ignore este e-mail. Sua senha atual continua valendo.</p>
  <p style="font-size: 12px; color: #777;">Se o botão não funcionar, copie e cole no navegador:<br>{$link}</p>
</div>
</body>
</html>
HTML;
    }
}