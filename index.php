<?php
require_once 'carregar_dados.php';

$acao = $_GET['acao'] ?? 'quiz';
$questao_id = $_GET['id'] ?? null;

// Carrega os dados do quiz
$quiz_data = carregarDadosQuiz();

switch ($acao) {
    case 'quiz':
        exibirQuiz($quiz_data, $questao_id);
        break;
    case 'responder':
        processarResposta($quiz_data);
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

function exibirQuiz($quiz_data, $questao_id = null) {
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
    
    $dados = [
        'questao' => $questao_atual,
        'numero_questao' => $numero_sequencial,
        'total_perguntas' => count($quiz_data),
        'acertos_total' => 0,
        'feedback' => null
    ];
    
    include 'templates/quiz.php';
}

function processarResposta($quiz_data) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php');
        exit;
    }
    
    $questao_id = (int)$_POST['questao_id'];
    $resposta = $_POST['resposta'];
    $acertos_anteriores = (int)$_POST['acertos_anteriores'];
    
    // Encontra a questão atual
    $questao_atual = null;
    $proxima_questao = null;
    $encontrou_atual = false;
    
    foreach ($quiz_data as $questao) {
        if ($questao['id'] == $questao_id) {
            $questao_atual = $questao;
            $encontrou_atual = true;
            continue;
        }
        if ($encontrou_atual && !$proxima_questao) {
            $proxima_questao = $questao;
        }
    }
    
    if (!$questao_atual) {
        die("Questão não encontrada.");
    }
    
    // Verifica resposta
    $acertou = (strtoupper(trim($resposta)) === strtoupper(trim($questao_atual['resposta_correta'])));
    
    if ($acertou) {
        $acertos_anteriores++;
        $mensagem_feedback = "✅ Certo! Isso mesmo. A resposta correta era {$questao_atual['resposta_correta']}.";
    } else {
        $mensagem_feedback = "❌ Errado. A resposta correta é <strong>{$questao_atual['resposta_correta']}</strong>. Sua resposta foi <strong>{$resposta}</strong>.";
    }
    
    $feedback = [
        'mensagem' => $mensagem_feedback,
        'explicacao' => $questao_atual['explicacao_feedback']
    ];
    
    if ($proxima_questao) {
        // Próxima questão
        $numero_sequencial = 1;
        foreach ($quiz_data as $i => $q) {
            if ($q['id'] == $proxima_questao['id']) {
                $numero_sequencial = $i + 1;
                break;
            }
        }
        
        $dados = [
            'questao' => $proxima_questao,
            'numero_questao' => $numero_sequencial,
            'total_perguntas' => count($quiz_data),
            'acertos_total' => $acertos_anteriores,
            'feedback' => $feedback
        ];
        
        include 'templates/quiz.php';
    } else {
        // Fim do quiz
        $dados = [
            'acertos_total' => $acertos_anteriores,
            'total_perguntas' => count($quiz_data),
            'feedback' => $feedback
        ];
        
        include 'templates/fim_quiz.php';
    }
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