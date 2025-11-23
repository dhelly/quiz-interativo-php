<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questao_id = intval($_POST['questao_id']);
    $action = $_POST['action'] ?? 'add';
    
    // Inicializa o array se não existir
    if (!isset($_SESSION['questoes_erradas'])) {
        $_SESSION['questoes_erradas'] = [];
    }
    
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