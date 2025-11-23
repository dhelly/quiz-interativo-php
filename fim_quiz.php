<?php
$acertos_total = $_GET['acertos'] ?? 0;
$total_perguntas = $_GET['total'] ?? 0;

$dados = [
    'acertos_total' => (int)$acertos_total,
    'total_perguntas' => (int)$total_perguntas
];

include 'templates/fim_quiz.php';
?>