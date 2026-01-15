-- =====================================================
-- Sistema de Lista de Espera - Hospital
-- Schema do Banco de Dados
-- =====================================================

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS dema5738_lista_espera_hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dema5738_lista_espera_hospital;

-- =====================================================
-- Tabela de Usuários (para autenticação)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('administrador', 'recepcao', 'medico') NOT NULL DEFAULT 'recepcao',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_perfil (perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela de Especialidades
-- =====================================================
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    cor VARCHAR(50) DEFAULT 'bg-blue-200',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela de Médicos
-- =====================================================
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    crm_cpf VARCHAR(20) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    email VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_crm_cpf (crm_cpf),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela de Relacionamento Médico-Especialidade (N:N)
-- =====================================================
CREATE TABLE medico_especialidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    especialidade_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_medico_especialidade (medico_id, especialidade_id),
    INDEX idx_medico (medico_id),
    INDEX idx_especialidade (especialidade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela de Convênios
-- =====================================================
CREATE TABLE convenios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    codigo VARCHAR(50),
    cor VARCHAR(50) DEFAULT 'bg-green-200',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela Principal: Fila de Espera
-- =====================================================
CREATE TABLE fila_espera (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    especialidade_id INT NOT NULL,
    convenio_id INT,
    nome_paciente VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    data_nascimento DATE NOT NULL,
    data_solicitacao DATE NOT NULL,
    informacao VARCHAR(100),
    observacao TEXT,
    agendado BOOLEAN DEFAULT FALSE,
    data_agendamento DATE,
    telefone1 VARCHAR(20) NOT NULL,
    telefone2 VARCHAR(20),
    agendado_por VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE RESTRICT,
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE SET NULL,
    INDEX idx_medico (medico_id),
    INDEX idx_especialidade (especialidade_id),
    INDEX idx_convenio (convenio_id),
    INDEX idx_nome_paciente (nome_paciente),
    INDEX idx_cpf (cpf),
    INDEX idx_data_solicitacao (data_solicitacao),
    INDEX idx_agendado (agendado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DADOS DE EXEMPLO
-- =====================================================

-- Inserir usuários (senha: admin123 para todos)
-- Hash bcrypt gerado com password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
('Administrador do Sistema', 'admin@hospital.com', '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm', 'administrador'),
('Maria Recepção', 'recepcao@hospital.com', '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm', 'recepcao'),
('Dr. João Silva', 'medico@hospital.com', '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm', 'medico');

-- Inserir especialidades com cores
INSERT INTO especialidades (nome, cor) VALUES
('Ortopedia', 'bg-blue-200'),
('Psiquiatria', 'bg-purple-200'),
('Cirurgia Vascular', 'bg-red-200'),
('Cardiologia', 'bg-pink-200'),
('Pediatria', 'bg-yellow-200'),
('Neurologia', 'bg-indigo-200'),
('Dermatologia', 'bg-green-200');

-- Inserir convênios com cores
INSERT INTO convenios (nome, codigo, cor) VALUES
('GOCARE', 'GOC001', 'bg-emerald-200'),
('CAPEP', 'CAP001', 'bg-sky-200'),
('APAS Santos', 'APS001', 'bg-violet-200'),
('Marinha', 'MAR001', 'bg-cyan-200'),
('Particular', 'PART', 'bg-gray-200');

-- Inserir médicos
INSERT INTO medicos (nome, crm_cpf, telefone, email, ativo) VALUES
('Dr. Carlos Alberto Mendes', 'CRM-SP 123456', '(11) 98765-4321', 'carlos.mendes@hospital.com', TRUE),
('Dra. Ana Paula Rodrigues', 'CRM-SP 234567', '(11) 97654-3210', 'ana.rodrigues@hospital.com', TRUE),
('Dr. Roberto Santos', 'CRM-SP 345678', '(11) 96543-2109', 'roberto.santos@hospital.com', TRUE),
('Dra. Juliana Costa', 'CRM-SP 456789', '(11) 95432-1098', 'juliana.costa@hospital.com', TRUE),
('Dr. Pedro Oliveira', 'CRM-SP 567890', '(11) 94321-0987', 'pedro.oliveira@hospital.com', TRUE);

-- Associar médicos com especialidades
INSERT INTO medico_especialidade (medico_id, especialidade_id) VALUES
(1, 1), -- Dr. Carlos: Ortopedia
(1, 3), -- Dr. Carlos: Cirurgia Vascular
(2, 2), -- Dra. Ana: Psiquiatria
(3, 4), -- Dr. Roberto: Cardiologia
(3, 3), -- Dr. Roberto: Cirurgia Vascular
(4, 5), -- Dra. Juliana: Pediatria
(5, 6), -- Dr. Pedro: Neurologia
(5, 7); -- Dr. Pedro: Dermatologia

-- Inserir registros de exemplo na fila de espera
INSERT INTO fila_espera (
    medico_id, especialidade_id, convenio_id, nome_paciente, cpf, 
    data_nascimento, data_solicitacao, informacao, observacao, 
    agendado, data_agendamento, telefone1, telefone2, agendado_por
) VALUES
(1, 1, 1, 'José da Silva Santos', '123.456.789-01', '1980-05-15', '2024-12-01', 'Consulta', 'Dor no joelho direito', FALSE, NULL, '(11) 99876-5432', '(11) 3234-5678', NULL),
(2, 2, 2, 'Maria Aparecida Oliveira', '234.567.890-12', '1975-08-22', '2024-12-02', 'Retorno', 'Acompanhamento mensal', TRUE, '2024-12-15', '(11) 98765-4321', NULL, 'Maria Recepção'),
(3, 4, 3, 'Antonio Carlos Pereira', '345.678.901-23', '1965-03-10', '2024-12-01', 'Exame', 'Eletrocardiograma solicitado', FALSE, NULL, '(11) 97654-3210', '(11) 3345-6789', NULL),
(4, 5, 1, 'Ana Beatriz Lima', '456.789.012-34', '2015-11-30', '2024-12-03', 'Consulta', 'Primeira consulta - febre persistente', TRUE, '2024-12-10', '(11) 96543-2109', '(11) 98234-5678', 'Maria Recepção'),
(5, 6, 4, 'Roberto Alves Costa', '567.890.123-45', '1990-07-18', '2024-11-28', 'Exame', 'Ressonância magnética cerebral', FALSE, NULL, '(11) 95432-1098', NULL, NULL),
(1, 3, 2, 'Fernanda Souza Lima', '678.901.234-56', '1988-12-05', '2024-12-02', 'Consulta', 'Varizes - avaliação cirúrgica', FALSE, NULL, '(11) 94321-0987', '(11) 3456-7890', NULL),
(3, 4, 1, 'Carlos Eduardo Martins', '789.012.345-67', '1955-02-28', '2024-11-30', 'Retorno', 'Pós-operatório 30 dias', TRUE, '2024-12-12', '(11) 93210-9876', NULL, 'Maria Recepção'),
(5, 7, 5, 'Patrícia Gomes Silva', '890.123.456-78', '1992-09-14', '2024-12-03', 'Consulta', 'Manchas na pele', FALSE, NULL, '(11) 92109-8765', '(11) 3567-8901', NULL);

-- =====================================================
-- Fim do Schema
-- =====================================================
