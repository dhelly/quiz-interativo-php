<?php
/**
 * Script de Configuração Inicial do Banco de Dados
 * Cria as tabelas necessárias para o Quiz Interativo.
 */

require_once 'db_config.php';

try {
    // Primeiro tentamos conectar sem o banco de dados para criá-lo
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cria o banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados '" . DB_NAME . "' criado ou já existente.\n";
    
    // Conecta ao banco de dados recém-criado
    $pdo->exec("USE " . DB_NAME);
    
    // Resetar banco de dados (Cuidado: Remove todos os dados)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS comment_votes");
    $pdo->exec("DROP TABLE IF EXISTS question_interactions");
    $pdo->exec("DROP TABLE IF EXISTS scores");
    $pdo->exec("DROP TABLE IF EXISTS options");
    $pdo->exec("DROP TABLE IF EXISTS questions");
    $pdo->exec("DROP TABLE IF EXISTS quizzes");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "♻️ Banco de dados zerado para nova configuração.\n";

    // Tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'users' pronta.\n";
    
    // Tabela de Quizzes
    $pdo->exec("CREATE TABLE IF NOT EXISTS quizzes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        discipline VARCHAR(50) DEFAULT 'geral',
        json_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'quizzes' pronta.\n";
    
    // Tabela de Questões
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT DEFAULT 1,
        external_id INT, -- ID que vinha do JSON
        pergunta TEXT NOT NULL,
        resposta_correta VARCHAR(255) NOT NULL,
        explicacao_feedback TEXT,
        topico VARCHAR(100),
        nivel VARCHAR(50),
        is_visible BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'questions' pronta.\n";
    
    // Tabela de Opções
    $pdo->exec("CREATE TABLE IF NOT EXISTS options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        option_text VARCHAR(255) NOT NULL,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'options' pronta.\n";
    
    // Tabela de Scores (Ranking)
    $pdo->exec("CREATE TABLE IF NOT EXISTS scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        quiz_id INT NOT NULL,
        score INT NOT NULL,
        total INT NOT NULL,
        percentage DECIMAL(5,2),
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'scores' pronta.\n";

    // Tabela de Interações (Sinalizações e Comentários)
    $pdo->exec("CREATE TABLE IF NOT EXISTS question_interactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        question_id INT NOT NULL,
        comment TEXT,
        is_flagged BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'question_interactions' pronta.\n";

    // Tabela de Votos em Comentários
    $pdo->exec("CREATE TABLE IF NOT EXISTS comment_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        interaction_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_interaction_vote (user_id, interaction_id),
        FOREIGN KEY (interaction_id) REFERENCES question_interactions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "✅ Tabela 'comment_votes' pronta.\n";
    
    // Inserir usuários iniciais
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)")
        ->execute(['admin', $adminPassword, 1]);
    echo "✅ Usuário administrador criado (admin / admin123).\n";

    // Inserir quiz padrão
    $pdo->exec("INSERT INTO quizzes (name, discipline) VALUES ('Quiz Inicial', 'geral')");
    echo "✅ Quiz Inicial inserido.\n";

    echo "\n🚀 Configuração concluída com sucesso!";

} catch (PDOException $e) {
    die("❌ Erro ao configurar banco de dados: " . $e->getMessage());
}
