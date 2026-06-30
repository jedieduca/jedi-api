<?php

namespace Repository;

use DB\MySQL;

class SystemUserRepository
{
    //Classe responsável por executar as requisições ao banco de dados
    /**
     * @var \DB\MySQL
     */
    private $MySQL;
    public const TABELA = 'system_user';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    /**
     * @return MySQL|object
     */
    public function getMySQL()
    {
        return $this->MySQL;
    }

    /**
     * @param $email
     * @param $password
     * @return mixed|null
     */
    public function repositoryPegarUser($email, $password)
    {
        try{
            //Função que executa a validação do user
            $consulta = 'SELECT id, name, login, email, frontpage_id, active FROM ' . self::TABELA . ' WHERE login = :login AND password = :password';


            // Corrigido para usar a instância correta do banco de dados
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':login', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            $item = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                "id" => (String) $item['id'],
                "name" => (String) $item['name'],
                "login" => (String) $item['login'],
                "email" => (String) $item['email'],
                "frontpage_id" => (String) $item['frontpage_id'],
                "active" => (String) $item['active']
            ];
        }
        catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function alterarSenha($login ,$senhaAntiga, $senhaNova){
        try {
            $consulta = " UPDATE " . self::TABELA . " SET password = :senhaNova  WHERE login = :login AND password = :password ";
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':senhaNova', $senhaNova);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $senhaAntiga);
            $stmt->execute();
            $resultado = $stmt->rowCount();

            return $resultado;

        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }

    }
}