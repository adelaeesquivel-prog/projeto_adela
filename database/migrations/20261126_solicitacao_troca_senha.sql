CREATE TABLE solicitacao_troca_senha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    senha_nova_hash VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolvido_em TIMESTAMP NULL DEFAULT NULL,
    administrador_id INT NULL,
    INDEX idx_solicitacao_troca_senha_status_criado (status, criado_em),
    CONSTRAINT fk_solicitacao_troca_senha_cliente
        FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE,
    CONSTRAINT fk_solicitacao_troca_senha_administrador
        FOREIGN KEY (administrador_id) REFERENCES administrador(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
