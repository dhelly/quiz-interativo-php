<?php
require_once 'db_config.php';

echo "<h2>Migração: Tabela de Progresso do Usuário</h2>";

try {
    $pdo = get_db_connection();
    
    // SQL para criar a tabela user_progress
    $sql = "CREATE TABLE IF NOT EXISTS user_progress (
        user_id INT PRIMARY KEY,
        quiz_id INT NOT NULL DEFAULT 1,
        current_question_id INT NOT NULL,
        acertos INT DEFAULT 0,
        questoes_erradas TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "<div style='color: green;'>✅ Tabela 'user_progress' verificada/criada com sucesso!</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erro na migração: " . $e->getMessage() . "</div>";
}
?>
