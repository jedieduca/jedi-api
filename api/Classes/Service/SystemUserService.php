<?php
namespace Service;

use InvalidArgumentException;
use Repository\SystemUserRepository;
use Util\ConstantesGenericasUtil;

class SystemUserService
{
    private $dados;
    private $SystemUserRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->SystemUserRepository = new SystemUserRepository();
    }

    /**
     * @return string
     */
    function servicePegarUser()
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

            return $resultado;
        }

        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_USER_BODY);
    }

    /**
     * @return mixed|void
     */
    function alterarSenhaService()
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

            if($senhaNova !== null){
                $senhaNova = md5($senhaNova);

                $resultado = $this->SystemUserRepository->alterarSenha($login, $senhaAntiga, $senhaNova);

                return $resultado;
            }
        }


    }
}