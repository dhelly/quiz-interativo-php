<?php
/**
 * Script de Teste de Telas - Quiz Interativo
 * Verifica se as telas principais estão respondendo com HTTP 200 e contém elementos esperados.
 */

// Proteção: Este script só deve ser executado via linha de comando
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die("Acesso negado. Este script só pode ser executado via CLI.");
}

$baseUrl = "http://localhost:8000";

$tests = [
    [
        "name" => "Welcome Screen",
        "url" => "/",
        "expected" => ["Quiz Interativo", "Qual é o seu apelido?", "Iniciar Desafio"]
    ],
    [
        "name" => "Quiz Page (Redirect if no session)",
        "url" => "/?acao=quiz",
        "expected" => ["Quiz Interativo", "Qual é o seu apelido?"] // Deve redirecionar ou mostrar welcome se não logado
    ],
    [
        "name" => "Admin Panel",
        "url" => "/admin.php",
        "expected" => ["Painel de Administração", "Editor de JSON", "Quizzes Salvos"]
    ],
    [
        "name" => "Ranking Page",
        "url" => "/ranking.php",
        "expected" => ["Ranking de Líderes", "Posição", "Usuário"]
    ],
    [
        "name" => "Fim do Quiz",
        "url" => "/fim_quiz.php?acertos=0&total=10",
        "expected" => ["Quiz Concluído", "0/10", "Reiniciar Quiz"]
    ]
];

echo "=== Iniciando Teste de Telas ===\n\n";

$allPassed = true;

foreach ($tests as $test) {
    echo "Testando: {$test['name']}... ";
    
    $ch = curl_init($baseUrl . $test['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "\033[31mFALHA\033[0m (HTTP $httpCode)\n";
        $allPassed = false;
        continue;
    }

    $missing = [];
    foreach ($test['expected'] as $string) {
        if (strpos($response, $string) === false) {
            $missing[] = $string;
        }
    }

    if (empty($missing)) {
        echo "\033[32mOK\033[0m\n";
    } else {
        echo "\033[31mFALHA\033[0m (Strings não encontradas: " . implode(", ", $missing) . ")\n";
        $allPassed = false;
    }
}

echo "\n===============================\n";
if ($allPassed) {
    echo "RESULTADO FINAL: \033[32mTODOS OS TESTES PASSARAM!\033[0m\n";
    exit(0);
} else {
    echo "RESULTADO FINAL: \033[31mHOUVE FALHAS NOS TESTES.\033[0m\n";
    exit(1);
}
