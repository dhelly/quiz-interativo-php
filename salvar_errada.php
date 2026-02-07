<?php
require_once 'session_config.php';
require_once 'carregar_dados.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validarTokenCSRF($csrf_token)) {
        error_log("CSRF Failure in salvar_errada.php. Token: $csrf_token");
        http_response_code(403);
        die('Erro CSRF: Requisição inválida.');
    }
    $questao_id = intval($_POST['questao_id']);
    $action = $_POST['action'] ?? 'add';
    
    error_log("salvar_errada.php called. Action: $action, ID: $questao_id");
    
    // Variáveis de sessão já inicializadas em session_config.php
    
    if ($action === 'add') {
        // Adiciona a questão às erradas (se ainda não estiver)
        if (!in_array($questao_id, $_SESSION['questoes_erradas'])) {
            $_SESSION['questoes_erradas'][] = $questao_id;
        }
    } elseif ($action === 'remove') {
        // Remove a questão das erradas
        $_SESSION['questoes_erradas'] = array_filter($_SESSION['questoes_erradas'], function($id) use ($questao_id) {
            return $id !== $questao_id;
        });
    }
    
    echo 'OK';
} else {
    http_response_code(405);
    echo 'Método não permitido';
}
?>