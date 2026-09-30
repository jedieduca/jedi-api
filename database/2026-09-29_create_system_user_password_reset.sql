-- Tokens de redefinição de senha (fluxo "Esqueci minha senha" do jogo)
-- Banco: jedi-educa-v2
CREATE TABLE IF NOT EXISTS system_user_password_reset (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  system_user_id INT          NOT NULL,
  token_hash     CHAR(64)     NOT NULL,
  expires_at     DATETIME     NOT NULL,
  used_at        DATETIME     NULL,
  request_ip     VARCHAR(45)  NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_token_hash (token_hash),
  KEY idx_user_created (system_user_id, created_at),
  KEY idx_ip_created (request_ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
