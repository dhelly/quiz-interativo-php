<?php
require_once 'carregar_dados.php';

$acao = $_GET['acao'] ?? 'quiz';
$questao_id = $_GET['id'] ?? null;
$acertos = $_GET['acertos'] ?? 0;

// Carrega os dados do quiz
$quiz_data = carregarDadosQuiz();

switch ($acao) {
    case 'quiz':
        exibirQuiz($quiz_data, $questao_id, $acertos);
        break;
    case 'admin':
        exibirAdmin($quiz_data);
        break;
    case 'reload':
        recarregarDados();
        break;
    default:
        exibirQuiz($quiz_data);
        break;
}

function exibirQuiz($quiz_data, $questao_id = null, $acertos = 0) {
    if (empty($quiz_data)) {
        die("Erro: Nenhuma questão encontrada. Verifique o arquivo de dados.");
    }
    
    // Encontra a questão atual
    if ($questao_id) {
        $questao_atual = null;
        foreach ($quiz_data as $q) {
            if ($q['id'] == $questao_id) {
                $questao_atual = $q;
                break;
            }
        }
        if (!$questao_atual) {
            $questao_atual = $quiz_data[0];
        }
    } else {
        $questao_atual = $quiz_data[0];
    }
    
    // Calcula número sequencial
    $numero_sequencial = 1;
    foreach ($quiz_data as $i => $q) {
        if ($q['id'] == $questao_atual['id']) {
            $numero_sequencial = $i + 1;
            break;
        }
    }
    
    // Encontra próxima questão
    $proxima_id = null;
    $encontrou_atual = false;
    foreach ($quiz_data as $questao) {
        if ($questao['id'] == $questao_atual['id']) {
            $encontrou_atual = true;
            continue;
        }
        if ($encontrou_atual && !$proxima_id) {
            $proxima_id = $questao['id'];
            break;
        }
    }
    
    $dados = [
        'questao' => $questao_atual,
        'numero_questao' => $numero_sequencial,
        'total_perguntas' => count($quiz_data),
        'acertos_total' => (int)$acertos,
        'feedback' => null,
        'proxima_id' => $proxima_id,
        'resposta_correta' => $questao_atual['resposta_correta'],
        'explicacao' => $questao_atual['explicacao_feedback']
    ];
    
    include 'templates/quiz.php';
}

function exibirAdmin($quiz_data) {
    $dados = [
        'total_questoes' => count($quiz_data),
        'arquivo_atual' => 'quiz_data.json',
        'questoes' => $quiz_data
    ];
    
    include 'templates/admin_panel.php';
}

function recarregarDados() {
    // Força recarregamento dos dados
    header('Location: index.php?acao=admin');
    exit;
}
?>