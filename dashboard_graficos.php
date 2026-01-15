<?php
require_once __DIR__ . '/config.php';
session_start();
require_once 'config/database.php';

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit;
}

// Debug - REMOVER DEPOIS
// echo "Perfil na sessão: " . ($_SESSION['usuario_perfil'] ?? 'NÃO DEFINIDO');
// exit;

// Verifica se é administrador
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'administrador') {
    $_SESSION['mensagem_erro'] = 'Acesso negado. Apenas administradores podem acessar esta página.';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit;
}

$pageTitle = 'Dashboard - Gráficos e Estatísticas';
$paginaAtual = 'dashboard-graficos';

// Conecta ao banco
$database = new Database();
$conn = $database->getConnection();

// Busca estatísticas gerais
$sqlGeral = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as total_agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as total_pendentes,
    SUM(CASE WHEN urgente = 1 THEN 1 ELSE 0 END) as total_urgentes
FROM fila_espera";
$stmtGeral = $conn->prepare($sqlGeral);
$stmtGeral->execute();
$estatisticas = $stmtGeral->fetch(PDO::FETCH_ASSOC);

// Agendados vs Pendentes por mês (últimos 6 meses)
$sqlMensal = "SELECT 
    DATE_FORMAT(data_solicitacao, '%Y-%m') as mes,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera
WHERE data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(data_solicitacao, '%Y-%m')
ORDER BY mes ASC";
$stmtMensal = $conn->prepare($sqlMensal);
$stmtMensal->execute();
$dadosMensais = $stmtMensal->fetchAll(PDO::FETCH_ASSOC);

// Por especialidade
$sqlEspecialidade = "SELECT 
    e.nome as especialidade,
    COUNT(*) as total,
    SUM(CASE WHEN f.agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN f.agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera f
LEFT JOIN especialidades e ON f.especialidade_id = e.id
GROUP BY f.especialidade_id, e.nome
ORDER BY total DESC
LIMIT 10";
$stmtEsp = $conn->prepare($sqlEspecialidade);
$stmtEsp->execute();
$dadosEspecialidade = $stmtEsp->fetchAll(PDO::FETCH_ASSOC);

// Por médico (Top 10)
$sqlMedico = "SELECT 
    m.nome as medico,
    COUNT(*) as total,
    SUM(CASE WHEN f.agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN f.agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera f
LEFT JOIN medicos m ON f.medico_id = m.id
GROUP BY f.medico_id, m.nome
ORDER BY total DESC
LIMIT 10";
$stmtMed = $conn->prepare($sqlMedico);
$stmtMed->execute();
$dadosMedicos = $stmtMed->fetchAll(PDO::FETCH_ASSOC);

// Por tipo de atendimento
$sqlTipo = "SELECT 
    tipo_atendimento,
    COUNT(*) as total,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera
GROUP BY tipo_atendimento
ORDER BY total DESC";
$stmtTipo = $conn->prepare($sqlTipo);
$stmtTipo->execute();
$dadosTipo = $stmtTipo->fetchAll(PDO::FETCH_ASSOC);

// Por convênio
$sqlConvenio = "SELECT 
    c.nome as convenio,
    COUNT(*) as total,
    SUM(CASE WHEN f.agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN f.agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera f
LEFT JOIN convenios c ON f.convenio_id = c.id
GROUP BY f.convenio_id, c.nome
ORDER BY total DESC
LIMIT 10";
$stmtConv = $conn->prepare($sqlConvenio);
$stmtConv->execute();
$dadosConvenio = $stmtConv->fetchAll(PDO::FETCH_ASSOC);

// Tempo médio de espera
$sqlTempoEspera = "SELECT 
    AVG(DATEDIFF(data_agendamento, data_solicitacao)) as media_dias
FROM fila_espera
WHERE agendado = 1 
AND data_agendamento IS NOT NULL 
AND data_solicitacao IS NOT NULL";
$stmtTempo = $conn->prepare($sqlTempoEspera);
$stmtTempo->execute();
$tempoEspera = $stmtTempo->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-chart-line mr-3"></i>Dashboard de Análises
                </h1>
                <p class="text-blue-100">Visualize estatísticas e tendências da fila de espera</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-100">Atualizado em</p>
                <p class="text-xl font-bold"><?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Resumidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total de Registros -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold uppercase">Total de Registros</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($estatisticas['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-list-alt text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Agendados -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold uppercase">Agendados</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($estatisticas['total_agendados']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php 
                        $percAgendados = $estatisticas['total'] > 0 ? ($estatisticas['total_agendados'] / $estatisticas['total']) * 100 : 0;
                        echo number_format($percAgendados, 1) . '%'; 
                        ?>
                    </p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Pendentes -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold uppercase">Pendentes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($estatisticas['total_pendentes']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php 
                        $percPendentes = $estatisticas['total'] > 0 ? ($estatisticas['total_pendentes'] / $estatisticas['total']) * 100 : 0;
                        echo number_format($percPendentes, 1) . '%'; 
                        ?>
                    </p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Urgentes -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold uppercase">Urgentes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($estatisticas['total_urgentes']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php 
                        $percUrgentes = $estatisticas['total'] > 0 ? ($estatisticas['total_urgentes'] / $estatisticas['total']) * 100 : 0;
                        echo number_format($percUrgentes, 1) . '%'; 
                        ?>
                    </p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tempo Médio de Espera -->
    <?php if ($tempoEspera['media_dias']): ?>
    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center">
            <div class="bg-white bg-opacity-20 rounded-full p-4 mr-4">
                <i class="fas fa-hourglass-half text-3xl"></i>
            </div>
            <div>
                <p class="text-lg font-semibold opacity-90">Tempo Médio de Espera</p>
                <p class="text-4xl font-bold"><?php echo round($tempoEspera['media_dias']); ?> dias</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Gráfico: Status Geral (Pizza) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                Status Geral
            </h3>
            <canvas id="graficoStatusGeral"></canvas>
        </div>

        <!-- Gráfico: Evolução Mensal (Linha) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-line text-green-600 mr-2"></i>
                Evolução Mensal (Últimos 6 Meses)
            </h3>
            <canvas id="graficoEvolucao"></canvas>
        </div>

        <!-- Gráfico: Por Especialidade (Barra Horizontal) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-stethoscope text-purple-600 mr-2"></i>
                Top 10 Especialidades
            </h3>
            <canvas id="graficoEspecialidade"></canvas>
        </div>

        <!-- Gráfico: Por Tipo de Atendimento (Rosca) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-md text-indigo-600 mr-2"></i>
                Tipo de Atendimento
            </h3>
            <canvas id="graficoTipo"></canvas>
        </div>

        <!-- Gráfico: Por Médico (Barra) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-doctor text-teal-600 mr-2"></i>
                Top 10 Médicos
            </h3>
            <canvas id="graficoMedicos"></canvas>
        </div>

        <!-- Gráfico: Por Convênio (Barra Horizontal) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-id-card text-orange-600 mr-2"></i>
                Top 10 Convênios
            </h3>
            <canvas id="graficoConvenio"></canvas>
        </div>

    </div>

    <!-- Botão de Voltar -->
    <div class="mb-6">
        <a href="/dashboard.php" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition">
            <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
        </a>
    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Configurações de cores
const cores = {
    agendado: '#10b981',
    pendente: '#f59e0b',
    urgente: '#ef4444',
    azul: '#3b82f6',
    roxo: '#8b5cf6',
    rosa: '#ec4899'
};

// 1. Gráfico de Status Geral (Pizza)
const ctxStatusGeral = document.getElementById('graficoStatusGeral').getContext('2d');
new Chart(ctxStatusGeral, {
    type: 'pie',
    data: {
        labels: ['Agendados', 'Pendentes', 'Urgentes'],
        datasets: [{
            data: [
                <?php echo $estatisticas['total_agendados']; ?>,
                <?php echo $estatisticas['total_pendentes']; ?>,
                <?php echo $estatisticas['total_urgentes']; ?>
            ],
            backgroundColor: [cores.agendado, cores.pendente, cores.urgente],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// 2. Gráfico de Evolução Mensal (Linha)
const ctxEvolucao = document.getElementById('graficoEvolucao').getContext('2d');
new Chart(ctxEvolucao, {
    type: 'line',
    data: {
        labels: [
            <?php 
            foreach ($dadosMensais as $mes) {
                $data = DateTime::createFromFormat('Y-m', $mes['mes']);
                echo "'" . $data->format('M/Y') . "',";
            }
            ?>
        ],
        datasets: [
            {
                label: 'Agendados',
                data: [<?php echo implode(',', array_column($dadosMensais, 'agendados')); ?>],
                borderColor: cores.agendado,
                backgroundColor: cores.agendado + '20',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Pendentes',
                data: [<?php echo implode(',', array_column($dadosMensais, 'pendentes')); ?>],
                borderColor: cores.pendente,
                backgroundColor: cores.pendente + '20',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// 3. Gráfico por Especialidade (Barra Horizontal)
const ctxEspecialidade = document.getElementById('graficoEspecialidade').getContext('2d');
new Chart(ctxEspecialidade, {
    type: 'bar',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($dadosEspecialidade, 'especialidade')) . "'"; ?>],
        datasets: [
            {
                label: 'Agendados',
                data: [<?php echo implode(',', array_column($dadosEspecialidade, 'agendados')); ?>],
                backgroundColor: cores.agendado
            },
            {
                label: 'Pendentes',
                data: [<?php echo implode(',', array_column($dadosEspecialidade, 'pendentes')); ?>],
                backgroundColor: cores.pendente
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                stacked: true
            },
            y: {
                stacked: true
            }
        }
    }
});

// 4. Gráfico por Tipo de Atendimento (Rosca)
const ctxTipo = document.getElementById('graficoTipo').getContext('2d');
new Chart(ctxTipo, {
    type: 'doughnut',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($dadosTipo, 'tipo_atendimento')) . "'"; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_column($dadosTipo, 'total')); ?>],
            backgroundColor: [cores.azul, cores.agendado, cores.roxo, cores.pendente, cores.rosa],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// 5. Gráfico por Médico (Barra)
const ctxMedicos = document.getElementById('graficoMedicos').getContext('2d');
new Chart(ctxMedicos, {
    type: 'bar',
    data: {
        labels: [<?php echo "'" . implode("','", array_map(function($m) { 
            return substr($m['medico'], 0, 20); 
        }, $dadosMedicos)) . "'"; ?>],
        datasets: [{
            label: 'Total de Atendimentos',
            data: [<?php echo implode(',', array_column($dadosMedicos, 'total')); ?>],
            backgroundColor: cores.azul
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// 6. Gráfico por Convênio (Barra Horizontal)
const ctxConvenio = document.getElementById('graficoConvenio').getContext('2d');
new Chart(ctxConvenio, {
    type: 'bar',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($dadosConvenio, 'convenio')) . "'"; ?>],
        datasets: [{
            label: 'Total',
            data: [<?php echo implode(',', array_column($dadosConvenio, 'total')); ?>],
            backgroundColor: cores.roxo
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
