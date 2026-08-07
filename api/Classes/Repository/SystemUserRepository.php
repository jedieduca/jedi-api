<?php

namespace Repository;

use DB\MySQL;
use PDO;
use PDOException;

class SystemUserRepository
{
    /**
     * @var \DB\MySQL
     */
    private $MySQL;
    public const TABELA = 'system_user';

    public function __construct(){
        $this->MySQL = new MySQL();
    }

    /**
     * @return MySQL
     */
    public function getMySQL(): MySQL
    {
        return $this->MySQL;
    }

    /**
     * @param string $login
     * @param string $password
     * @return array|null
     */
    public function repositoryPegarUser($login, $password)
    {
        try {
            // Consulta buscando colunas da tabela system_user
            $consulta = 'SELECT id, name, login, email, frontpage_id, active FROM ' . self::TABELA . ' WHERE login = :login AND password = :password';

            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se o usuário não for encontrado ou as credenciais forem inválidas
            if (!$item) {
                return null;
            }

            return [
                "id"           => $item['id'],
                "name"         => $item['name'],
                "login"        => $item['login'],
                "email"        => $item['email'],
                "frontpage_id" => $item['frontpage_id'],
                "active"       => $item['active']
            ];
        }
        catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    /**
     * @param string $login
     * @param string $senhaAntiga
     * @param string $senhaNova
     * @return int
     */
    public function alterarSenha($login, $senhaAntiga, $senhaNova)
    {
        try {
            $consulta = "UPDATE " . self::TABELA . " SET password = :senhaNova WHERE login = :login AND password = :password";
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':senhaNova', $senhaNova);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $senhaAntiga);
            $stmt->execute();

            return $stmt->rowCount();

        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function repositoryPegarUserPorEmail($email)
    {
        try {
            $consulta = "SELECT * FROM " . self::TABELA . " WHERE email = :email";
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }

    public function repositoryCadastrarUsurario($login, $senha, $email, $nome)
    {
        try {
            $consulta = 'SELECT * FROM ' . self::TABELA . ' WHERE email = :email';

            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item !== false) {
                return 0;
            }

            $consulta = "INSERT INTO " . self::TABELA . " (name, login, password, email, frontpage_id, active)
                        VALUES (:nome, :login, :password, :email, 41, 'Y')";
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $senha);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $item = $this->MySQL->getDb()->lastInsertId();

            if($item !== false) {
                return 1;
            }
        }
        catch (PDOException $e) {
            throw new \InvalidArgumentException("Erro SQL: " . $e->getMessage());
        }
    }
}