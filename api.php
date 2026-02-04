<?php
session_start();
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

    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        break;
}
