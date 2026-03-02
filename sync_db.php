<?php
require_once 'carregar_dados.php';

// Proteção: Este script só deve ser executado via linha de comando
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die("Acesso negado. Este script só pode ser executado via CLI.");
}

$json_content = file_get_contents('quiz_data.json');
$dados = json_decode($json_content, true);

if ($dados === null) {
    die("Erro ao ler quiz_data.json: " . json_last_error_msg());
}

if (salvarDadosQuiz($dados)) {
    echo "Sincronização concluída com sucesso!";
} else {
    echo "Erro ao sincronizar com o banco de dados.";
}
?>
