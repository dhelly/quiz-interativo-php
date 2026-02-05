<?php
require_once 'session_config.php';
require_once 'carregar_dados.php';

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

header('Content-Type: application/json');

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// Validação CSRF para ações que alteram dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validarTokenCSRF($csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Erro CSRF: Requisição inválida.']);
        exit;
    }
}

switch ($action) {
    case 'get_comments':
        $question_id = $_GET['question_id'] ?? 0;
        if ($question_id) {
            $comments = obterComentariosPublicos($question_id);
            echo json_encode(['success' => true, 'comments' => $comments]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID da questão não fornecido.']);
        }
        break;

    case 'vote_comment':
        $comment_id = $_POST['comment_id'] ?? 0;
        if ($comment_id) {
            $result = votarNoComentario($user_id, $comment_id);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID do comentário não fornecido.']);
        }
        break;

    case 'save_progress':
        $quiz_id = $_POST['quiz_id'] ?? 0;
        $question_id = $_POST['question_id'] ?? 0;
        $acertos = $_POST['acertos'] ?? 0;
        $erradas = json_decode($_POST['erradas'] ?? '[]', true);
        
        if ($quiz_id && $question_id) {
            $result = salvarProgresso($user_id, $quiz_id, $question_id, $acertos, $erradas);
            if ($result) {
               echo json_encode(['success' => true, 'message' => 'Progresso salvo com sucesso!']); 
            } else {
               echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        break;
}
