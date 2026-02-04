<?php
require_once 'session_config.php';
require_once 'carregar_dados.php';
require_once 'sanitize.php';

$acertos_total = $_GET['acertos'] ?? 0;
$total_perguntas = $_GET['total'] ?? 0;
$modo_revisao = isset($_GET['modo_revisao']);
$sem_erradas = isset($_GET['sem_erradas']);

$questoes_erradas = $_SESSION['questoes_erradas'] ?? [];
$total_erradas = count($questoes_erradas);

$dados = [
    'acertos_total' => (int)$acertos_total,
    'total_perguntas' => (int)$total_perguntas,
    'questoes_erradas' => $questoes_erradas,
    'total_erradas' => $total_erradas,
    'modo_revisao' => $modo_revisao,
    'sem_erradas' => $sem_erradas,
    'username' => $_SESSION['username'] ?? 'Anônimo'
];

// Salva no banco de dados se não for modo revisão
if (!$modo_revisao && isset($_SESSION['user_id'])) {
    $quiz_id = $_GET['quiz_id'] ?? $_SESSION['current_quiz_id'] ?? 1;
    salvarScore($_SESSION['user_id'], $quiz_id, $acertos_total, $total_perguntas);
}


include 'templates/fim_quiz.php';
?>