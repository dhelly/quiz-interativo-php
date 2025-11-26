<?php
session_start();

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
    'sem_erradas' => $sem_erradas
];

include 'templates/fim_quiz.php';
?>