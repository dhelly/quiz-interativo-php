<?php
require_once 'session_config.php';
require_once 'carregar_dados.php';
require_once 'sanitize.php';

$quizzes_disponiveis = listarQuizzes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validarTokenCSRF($csrf_token)) {
        die('Erro CSRF: Requisição inválida.');
    }
}

// Lógicas de Ação
if (isset($_GET['carregar_quiz'])) {
    $caminho_quiz = $_GET['carregar_quiz'];
    $_SESSION['current_quiz_id'] = $caminho_quiz; // Salva o quiz selecionado na sessão
    header('Location: index.php?acao=quiz');
    exit;
}

$acao = $_GET['acao'] ?? 'quiz';

// Gerenciamento de Usuário (Sessão)
if ((!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) && $acao != 'login') {
    $acao = 'welcome';
}

if ($acao == 'login' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Tenta autenticar
    $user = autenticarUsuario($username, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        header('Location: index.php');
        exit;
    } else {
        // Se não conseguiu autenticar, tenta registrar (se não for admin o username desejado)
        if ($username !== 'admin') {
            $registro = registrarUsuario($username, $password);
            if ($registro['success']) {
                $_SESSION['user_id'] = $registro['id'];
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = false;
                header('Location: index.php');
                exit;
            } else {
                $erro_login = $registro['message'];
                $acao = 'welcome';
            }
        } else {
            $erro_login = "Senha incorreta para o administrador.";
            $acao = 'welcome';
        }
    }
}

if ($acao == 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Se estiver logado e não tiver quiz selecionado, vai para home
if (isset($_SESSION['user_id']) && !isset($_SESSION['current_quiz_id']) && $acao == 'quiz') {
    $acao = 'home';
}

$questao_id = $_GET['id'] ?? null;
$acertos = $_GET['acertos'] ?? 0;
$modo_revisao = $_GET['modo_revisao'] ?? false;

// Inicializa sessão de questões erradas se não existir
if (!isset($_SESSION['questoes_erradas'])) {
    $_SESSION['questoes_erradas'] = [];
}

// Carrega os dados do quiz (ou quiz padrão se nenhum selecionado)
$quiz_id = $_SESSION['current_quiz_id'] ?? 1;
$quiz_data = carregarQuiz($quiz_id);
if (empty($quiz_data)) {
    $quiz_data = carregarDadosQuiz(); // Fallback
}

switch ($acao) {
    case 'welcome':
        include 'templates/welcome.php';
        break;
    case 'home':
        exibirHome($quizzes_disponiveis);
        break;
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
        if (isset($_SESSION['user_id'])) {
            exibirHome($quizzes_disponiveis);
        } else {
            include 'templates/welcome.php';
        }
        break;
}

function exibirHome($quizzes) {
    $dados = [
        'quizzes' => $quizzes,
        'username' => $_SESSION['username']
    ];
    include 'templates/home.php';
}


function exibirQuiz($quiz_data, $questao_id = null, $acertos = 0, $modo_revisao = false) {
    if (empty($quiz_data)) {
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            header('Location: admin.php?erro=sem_questoes');
            exit;
        }
        die("<div style='font-family: sans-serif; padding: 20px; text-align: center;'>
                <h2>🚧 Quiz em Manutenção</h2>
                <p>Nenhuma questão foi encontrada no banco de dados. Por favor, tente novamente mais tarde.</p>
                <a href='index.php?acao=logout'>Voltar ao Início</a>
             </div>");
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

    // Encontra próxima questão - LÓGICA CORRIGIDA
    $proxima_id = null;
    $indice_atual = null;

    // Encontra o índice atual
    foreach ($quiz_data as $indice => $questao) {
        if ($questao['id'] == $questao_atual['id']) {
            $indice_atual = $indice;
            break;
        }
    }

    // Se não é o último elemento, pega o próximo ID
    if ($indice_atual !== null && isset($quiz_data[$indice_atual + 1])) {
        $proxima_id = $quiz_data[$indice_atual + 1]['id'];
    }

    // Garante que na última questão o proxima_id seja null
    if ($numero_sequencial >= count($quiz_data)) {
        $proxima_id = null;
    }
    
    // Aplicar conversão markdown→HTML apenas para exibição no quiz (APÓS definir todas as variáveis)
    if (function_exists('prepararDadosParaQuiz')) {
        $quiz_data = prepararDadosParaQuiz($quiz_data);
        // Também precisa converter a questão atual
        $questao_atual = prepararDadosParaQuiz([$questao_atual])[0];
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