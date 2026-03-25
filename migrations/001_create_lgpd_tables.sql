CREATE TABLE lgpd_audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT,
    perfil VARCHAR(50),
    campo VARCHAR(50),
    tipo_dado VARCHAR(30),
    hash_valor CHAR(64),
    valor_mascarado VARCHAR(255),
    origem VARCHAR(100),
    assinatura CHAR(128),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
