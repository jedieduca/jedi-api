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
    public const GRUPO_DISCENTE = 4;          // system_group 'Discente'
    public const ESCOLA_AVULSOS = 106;        // escola 'Escola JEDi Educa'
    public const TURMA_AVULSOS = 46;          // turma padrão dos alunos avulsos

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
            // 1. Verifica se o e-mail ou o login já existem
            $consulta = 'SELECT email, login FROM ' . self::TABELA . ' WHERE email = :email OR login = :login';

            $stmt = $this->MySQL->getDb()->prepare($consulta);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            // 0 = email já cadastrado | 2 = login já cadastrado
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                if (strcasecmp((string) $item['email'], $email) === 0) {
                    return 0;
                }
                if (strcasecmp((string) $item['login'], $login) === 0) {
                    return 2;
                }
            }

            // Inicia a transação para garantir atomicidade de todas as inserções
            $db = $this->MySQL->getDb();
            $db->beginTransaction();

            // 2. Insere o novo usuário na tabela system_user
            $consulta = "INSERT INTO " . self::TABELA . " (name, login, password, email, frontpage_id, active, custom_code, otp_secret)
                    VALUES (:nome, :login, :password, :email, 41, 'Y', '', '')";
            $stmt = $db->prepare($consulta);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $senha);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // 3. Captura o ID do aluno recém-criado
            $idUsuarioCriado = (int) $db->lastInsertId();

            // 4. Grupo Discente (system_user_group.id não é AUTO_INCREMENT)
            $stmt = $db->prepare("INSERT INTO system_user_group (id, system_user_id, system_group_id)
                                  SELECT COALESCE(MAX(id), 0) + 1, :idUsuario, :idGrupo FROM system_user_group");
            $stmt->bindValue(':idUsuario', $idUsuarioCriado, PDO::PARAM_INT);
            $stmt->bindValue(':idGrupo', self::GRUPO_DISCENTE, PDO::PARAM_INT);
            $stmt->execute();

            // 5. Escola padrão dos alunos avulsos
            $stmt = $db->prepare("INSERT INTO usuario_escola (id_usuario, id_escola) VALUES (:idUsuario, :idEscola)");
            $stmt->bindValue(':idUsuario', $idUsuarioCriado, PDO::PARAM_INT);
            $stmt->bindValue(':idEscola', self::ESCOLA_AVULSOS, PDO::PARAM_INT);
            $stmt->execute();

            // 6. Vincula o aluno à turma padrão dos avulsos
            $stmt = $db->prepare("INSERT INTO turma_aluno (id_turma, id_aluno) VALUES (:idTurma, :idAluno)");
            $stmt->bindValue(':idTurma', self::TURMA_AVULSOS, PDO::PARAM_INT);
            $stmt->bindValue(':idAluno', $idUsuarioCriado, PDO::PARAM_INT);
            $stmt->execute();

            // Confirma todas as inserções no banco
            $db->commit();

            return 1; // Sucesso

        } catch (PDOException $e) {
            // Desfaz qualquer inserção pendente caso ocorra um erro de SQL
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
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