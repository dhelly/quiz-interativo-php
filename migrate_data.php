<?php
/**
 * Script de Migração de Dados
 * Migra questões de quiz_data.json para o banco de dados MySQL.
 */

require_once 'db_config.php';

// Proteção: Este script só deve ser executado via linha de comando
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die("Acesso negado. Este script só pode ser executado via CLI.");
}

// Carrega o JSON original
$json_file = 'quiz_data.json';
if (!file_exists($json_file)) {
    die("❌ Arquivo '$json_file' não encontrado.\n");
}

$json_content = file_get_contents($json_file);
$questoes = json_decode($json_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Erro ao decodificar JSON: " . json_last_error_msg() . "\n");
}

try {
    $pdo = get_db_connection();
    echo "✅ Conectado ao banco de dados.\n";
    
    // Pega o ID do quiz padrão (ou cria um)
    $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE name = 'Quiz Inicial' LIMIT 1");
    $stmt->execute();
    $quiz = $stmt->fetch();
    
    if (!$quiz) {
        $pdo->exec("INSERT INTO quizzes (name, discipline) VALUES ('Quiz Inicial', 'geral')");
        $quiz_id = $pdo->lastInsertId();
    } else {
        $quiz_id = $quiz['id'];
    }
    
    echo "📦 Iniciando migração de " . count($questoes) . " questões...\n";
    
    $pdo->beginTransaction();
    
    $stmt_question = $pdo->prepare("INSERT INTO questions (quiz_id, external_id, pergunta, resposta_correta, explicacao_feedback, topico, nivel) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt_option = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
    
    foreach ($questoes as $q) {
        $stmt_question->execute([
            $quiz_id,
            $q['id'],
            $q['pergunta'],
            $q['resposta_correta'],
            $q['explicacao_feedback'],
            $q['topico'],
            $q['nivel']
        ]);
        
        $question_id = $pdo->lastInsertId();
        
        foreach ($q['opcoes_disponiveis'] as $opcao) {
            $stmt_option->execute([$question_id, $opcao]);
        }
    }
    
    $pdo->commit();
    echo "✨ Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Erro na migração: " . $e->getMessage() . "\n");
}
