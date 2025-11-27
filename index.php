<?php
session_start();
require_once 'carregar_dados.php';

$quizzes_disponiveis = listarQuizzes();

if (isset($_GET['carregar_quiz'])) {
    $caminho_quiz = $_GET['carregar_quiz'];
    $quiz_data = carregarQuiz($caminho_quiz);
    if ($quiz_data) {
        salvarDadosQuiz($quiz_data);
        header('Location: index.php');
        exit;
    }
}

$acao = $_GET['acao'] ?? 'quiz';
$questao_id = $_GET['id'] ?? null;
$acertos = $_GET['acertos'] ?? 0;
$modo_revisao = $_GET['modo_revisao'] ?? false;

// Inicializa sessão de questões erradas se não existir
if (!isset($_SESSION['questoes_erradas'])) {
    $_SESSION['questoes_erradas'] = [];
}

// Carrega os dados do quiz
$quiz_data = carregarDadosQuiz();

switch ($acao) {
    case 'quiz':
        exibirQuiz($quiz_data, $questao_id, $acertos, $modo_revisao);
        break;
    case 'admin':
        exibirAdmin($quiz_data);
        break;
    case 'reload':
        recarregarDados();
        break;
    case 'revisar_erradas':
        revisarQuestoesErradas($quiz_data);
        break;
    case 'limpar_revisao':
        limparRevisao();
        break;
    default:
        exibirQuiz($quiz_data);
        break;
}

function exibirQuiz($quiz_data, $questao_id = null, $acertos = 0, $modo_revisao = false) {
    if (empty($quiz_data)) {
        die("Erro: Nenhuma questão encontrada. Verifique o arquivo de dados.");
    }

    // Aplicar conversão markdown→HTML apenas para exibição no quiz
    if (function_exists('prepararDadosParaQuiz')) {
        $quiz_data = prepararDadosParaQuiz($quiz_data);
    }
    
    // Se for modo revisão, usa apenas as questões erradas
    if ($modo_revisao) {
        $questoes_revisao = [];
        foreach ($quiz_data as $questao) {
            if (in_array($questao['id'], $_SESSION['questoes_erradas'])) {
                $questoes_revisao[] = $questao;
            }
        }
        
        if (empty($questoes_revisao)) {
            header('Location: fim_quiz.php?acertos=' . $acertos . '&total=' . count($quiz_data) . '&modo_revisao=1&sem_erradas=1');
            exit;
        }
        
        $quiz_data = $questoes_revisao;
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
        'explicacao' => $questao_atual['explicacao_feedback'],
        'modo_revisao' => $modo_revisao,
        'total_erradas' => count($_SESSION['questoes_erradas'])
    ];
    
    include 'templates/quiz.php';
}

function revisarQuestoesErradas($quiz_data) {
    if (empty($_SESSION['questoes_erradas'])) {
        header('Location: fim_quiz.php?sem_erradas=1');
        exit;
    }
    
    header('Location: index.php?modo_revisao=1');
    exit;
}

function limparRevisao() {
    $_SESSION['questoes_erradas'] = [];
    header('Location: index.php');
    exit;
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