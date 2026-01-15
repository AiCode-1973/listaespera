<?php
/**
 * Configuração de Conexão com Banco de Dados
 * Utiliza PDO para maior segurança (prepared statements)
 */

class Database {
    private $host = '186.209.113.107';
    private $db_name = 'dema5738_lista_espera_hospital';
    private $username = 'dema5738_lista_espera_hospital';
    private $password = 'Dema@1973';
    private $conn;

    /**
     * Obtém conexão com o banco de dados
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
            die();
        }

        return $this->conn;
    }
}
