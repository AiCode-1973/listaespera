<?php
/**
 * Endpoint para buscar médicos e exibir detalhes
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Medico.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

header('Content-Type: application/json');

$medicoModel = new Medico();

// Se tem parâmetro 'id', retorna dados completos de um médico específico
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Busca médico
        $medico = $medicoModel->buscarPorId($id);
        
        if ($medico) {
            // Busca especialidades do médico
            $especialidades = $medicoModel->buscarEspecialidades($id);
            $medico['especialidades'] = !empty($especialidades) ? implode(', ', $especialidades) : null;
            
            echo json_encode([
                'sucesso' => true,
                'medico' => $medico
            ]);
        } else {
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Médico não encontrado'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Erro ao buscar médico: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Se tem parâmetro 'busca', retorna lista de médicos
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = trim($_GET['busca']);
    
    if (strlen($busca) < 2) {
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Digite pelo menos 2 caracteres'
        ]);
        exit();
    }
    
    try {
        // Busca médicos
        $medicos = $medicoModel->listar(['busca' => $busca]);
        
        echo json_encode([
            'sucesso' => true,
            'medicos' => $medicos
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Erro ao buscar médicos: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Se não tem parâmetros
echo json_encode([
    'sucesso' => false,
    'erro' => 'Parâmetros inválidos'
]);
