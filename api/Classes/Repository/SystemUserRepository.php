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

    public function __construct() {
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
            // 1. Verifica se o e-mail já existe
            $consulta = 'SELECT id FROM ' . self::TABELA . ' WHERE email = :email';

            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item !== false) {
                return 0; // Usuário já cadastrado
            }

            // Inicia a transação para garantir atomicidade das duas inserções
            $this->MySQL->getDb()->beginTransaction();

            // 2. Insere o novo usuário na tabela system_user
            $consulta = "INSERT INTO " . self::TABELA . " (name, login, password, email, frontpage_id, active)
                    VALUES (:nome, :login, :password, :email, 41, 'Y')";
            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $senha);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // 3. Captura o ID do aluno recém-criado
            $idAlunoCriado = (int) $this->MySQL->getDb()->lastInsertId();

            echo $idAlunoCriado;

            if ($idAlunoCriado > 0) {
                // 4. Insere o vínculo na tabela turma_aluno com id_turma = 46
                $sqlTurmaAluno = "INSERT INTO turma_aluno (id_turma, id_aluno) VALUES (46, :id_aluno)";
                $stmtTurma = $this->MySQL->getDb()->prepare($sqlTurmaAluno);
                $stmtTurma->bindParam(':id_aluno', $idAlunoCriado, PDO::PARAM_INT);
                $stmtTurma->execute();

                // Confirma todas as inserções no banco
                $this->MySQL->getDb()->commit();

                return 1; // Sucesso
            }

            // Se por algum motivo o id não foi retornado, cancela a operação
            $this->MySQL->getDb()->rollBack();
            return 0;

        } catch (PDOException $e) {
            // Desfaz qualquer inserção pendente caso ocorra um erro de SQL
            if ($this->MySQL->getDb()->inTransaction()) {
                $this->MySQL->getDb()->rollBack();
            }
            throw new \InvalidArgumentException("Erro SQL ao cadastrar usuário: " . $e->getMessage());
        }
    }

    public function recuperarSenha($email)
    {
        try {
            // 1. Busca o usuário pelo e-mail
            $usuario = $this->repositoryPegarUserPorEmail($email);

            if ($usuario === false || empty($usuario)) {
                return 0;
            }

            // 2. Gera a nova senha em texto puro e aplica o MD5 (padrão do sistema)
            $novaSenhaPura = $this->gerarSenhaAleatoria(10);
            $senhaMd5 = md5($novaSenhaPura);

            // 3. Atualiza no banco
            $sucesso = $this->repositoryAtualizarSenha($usuario['id'], $senhaMd5);

            // 4. Retorna a senha em texto puro se atualizou com sucesso, ou 0 se falhou
            return $sucesso ? $novaSenhaPura : 0;

        } catch (\Exception $e) {
            return 0;
        }
    }

    private function gerarSenhaAleatoria(int $tamanho = 10): string
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $maxIndex = strlen($caracteres) - 1;
        $senha = '';

        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $caracteres[random_int(0, $maxIndex)];
        }

        return $senha;
    }

    public function repositoryAtualizarSenha($idUsuario, $senhaHash): bool
    {
        try {
            $sql = "UPDATE " . self::TABELA . " SET password = :senha WHERE id = :id";
            $stmt = $this->MySQL->getDb()->prepare($sql);
            $stmt->bindParam(':senha', $senhaHash);
            $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}