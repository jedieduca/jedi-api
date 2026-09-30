<?php

namespace Repository;

use DB\MySQL;
use PDO;
use PDOException;

class PasswordResetRepository
{
    /**
     * @var \DB\MySQL
     */
    private $MySQL;
    public const TABELA = 'system_user_password_reset';

    public function __construct() {
        $this->MySQL = new MySQL();
    }

    /**
     * Quantidade de pedidos feitos para o usuário nos últimos $minutos
     */
    public function contarPedidosRecentesUsuario(int $idUsuario, int $minutos): int
    {
        $sql = "SELECT COUNT(*) FROM " . self::TABELA . "
                 WHERE system_user_id = :idUsuario AND created_at > DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";
        $stmt = $this->MySQL->getDb()->prepare($sql);
        $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Quantidade de pedidos feitos a partir do IP nos últimos $minutos
     */
    public function contarPedidosRecentesIp(string $ip, int $minutos): int
    {
        $sql = "SELECT COUNT(*) FROM " . self::TABELA . "
                 WHERE request_ip = :ip AND created_at > DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";
        $stmt = $this->MySQL->getDb()->prepare($sql);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Invalida os tokens em aberto do usuário e registra um novo (somente o hash é gravado)
     */
    public function criarToken(int $idUsuario, string $tokenHash, int $validadeMinutos, ?string $ip): void
    {
        $db = $this->MySQL->getDb();
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE " . self::TABELA . " SET used_at = NOW()
                                   WHERE system_user_id = :idUsuario AND used_at IS NULL");
            $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $db->prepare("INSERT INTO " . self::TABELA . " (system_user_id, token_hash, expires_at, request_ip)
                                  VALUES (:idUsuario, :tokenHash, DATE_ADD(NOW(), INTERVAL :validade MINUTE), :ip)");
            $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':tokenHash', $tokenHash);
            $stmt->bindValue(':validade', $validadeMinutos, PDO::PARAM_INT);
            $stmt->bindValue(':ip', $ip);
            $stmt->execute();

            $db->commit();
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new \RuntimeException('Erro SQL ao registrar token de redefinição de senha', 0, $e);
        }
    }

    /**
     * Retorna o token se ele existir, não tiver sido usado e não estiver expirado
     * @return array|false [id, system_user_id, active]
     */
    public function buscarTokenValido(string $tokenHash)
    {
        $sql = "SELECT r.id, r.system_user_id, u.active
                  FROM " . self::TABELA . " r
                  JOIN " . SystemUserRepository::TABELA . " u ON u.id = r.system_user_id
                 WHERE r.token_hash = :tokenHash AND r.used_at IS NULL AND r.expires_at > NOW()";
        $stmt = $this->MySQL->getDb()->prepare($sql);
        $stmt->bindValue(':tokenHash', $tokenHash);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Consome o token e grava a nova senha na mesma transação.
     * Retorna false se o token já tiver sido consumido por outra requisição.
     */
    public function consumirTokenETrocarSenha(int $idToken, int $idUsuario, string $senhaHash): bool
    {
        $db = $this->MySQL->getDb();
        try {
            $db->beginTransaction();

            // Marca o token como usado; se outra requisição chegou antes, rowCount = 0
            $stmt = $db->prepare("UPDATE " . self::TABELA . " SET used_at = NOW() WHERE id = :id AND used_at IS NULL");
            $stmt->bindValue(':id', $idToken, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() !== 1) {
                $db->rollBack();
                return false;
            }

            $stmt = $db->prepare("UPDATE " . SystemUserRepository::TABELA . " SET password = :senha WHERE id = :idUsuario");
            $stmt->bindValue(':senha', $senhaHash);
            $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            // Invalida qualquer outro token em aberto do usuário
            $stmt = $db->prepare("UPDATE " . self::TABELA . " SET used_at = NOW()
                                   WHERE system_user_id = :idUsuario AND used_at IS NULL");
            $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new \RuntimeException('Erro SQL ao redefinir senha', 0, $e);
        }
    }
}
