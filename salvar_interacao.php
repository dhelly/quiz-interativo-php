<?php
session_start();
require_once 'carregar_dados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validarTokenCSRF($csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Erro CSRF: Requisição inválida.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $question_id = $_POST['question_id'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $is_flagged = isset($_POST['is_flagged']) && $_POST['is_flagged'] === '1';

    if ($question_id > 0) {
        if (salvarInteracaoQuestao($user_id, $question_id, $comment, $is_flagged)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar interação']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID da questão inválido']);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
}
exit;
