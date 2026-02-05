<?php
require_once 'db_config.php';

// Funções de Persistência de Progresso
function salvarProgresso($user_id, $quiz_id, $question_id, $acertos, $erradas) {
    try {
        $pdo = get_db_connection();
        $erradas_json = json_encode($erradas);
        
        $sql = "INSERT INTO user_progress (user_id, quiz_id, current_question_id, acertos, questoes_erradas) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                quiz_id = VALUES(quiz_id), 
                current_question_id = VALUES(current_question_id), 
                acertos = VALUES(acertos), 
                questoes_erradas = VALUES(questoes_erradas)";
                
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$user_id, $quiz_id, $question_id, $acertos, $erradas_json]);
    } catch (Exception $e) {
        error_log("Erro ao salvar progresso: " . $e->getMessage());
        return false;
    }
}

function obterProgresso($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $progresso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($progresso) {
            $progresso['questoes_erradas'] = json_decode($progresso['questoes_erradas'], true) ?? [];
        }
        
        return $progresso;
    } catch (Exception $e) {
        return false;
    }
}

function limparProgresso($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM user_progress WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (Exception $e) {
        return false;
    }
}

$FALLBACK_QUIZ_JSON = '[
  {
    "id": 1,
    "pergunta": "O Quiz Interativo foi migrado com sucesso para MySQL?",
    "resposta_correta": "Sim",
    "opcoes_disponiveis": ["Sim", "Não"],
    "explicacao_feedback": "A migração foi concluída com sucesso e o sistema está utilizando MariaDB/MySQL.",
    "topico": "Sistema",
    "nivel": "Básico"
  }
]';


function carregarDadosQuiz($somente_visiveis = true) {
    try {
        $pdo = get_db_connection();
        
        // Pega o ID do quiz inicial
        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE name = 'Quiz Inicial' LIMIT 1");
        $stmt->execute();
        $quiz = $stmt->fetch();
        $quiz_id = $quiz ? $quiz['id'] : 1;

        $where = $somente_visiveis ? "AND is_visible = 1" : "";
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? $where ORDER BY id ASC");
        $stmt->execute([$quiz_id]);
        $questoes = $stmt->fetchAll();
        
        foreach ($questoes as &$q) {
            $stmt_opt = $pdo->prepare("SELECT option_text FROM options WHERE question_id = ?");
            $stmt_opt->execute([$q['id']]);
            $q['opcoes_disponiveis'] = $stmt_opt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        return $questoes;
    } catch (Exception $e) {
        error_log("Erro ao carregar dados do MySQL: " . $e->getMessage());
        return [];
    }
}

function carregarDadosQuizDoJson($arquivo = 'quiz_data.json') {
    global $FALLBACK_QUIZ_JSON;
    
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Tenta detectar se é JSONL (pela extensão ou pelo conteúdo)
        $is_jsonl = str_ends_with($arquivo, '.jsonl') || (strpos($conteudo, "\n") !== false && $conteudo[0] === '{');
        
        if ($is_jsonl) {
            $dados = decodeJsonL($conteudo);
        } else {
            $dados = json_decode($conteudo, true);
        }

        if (json_last_error() === JSON_ERROR_NONE && is_array($dados)) {
            return $dados;
        }
    }
    return json_decode($FALLBACK_QUIZ_JSON, true);
}

function decodeJsonL($content) {
    $lines = explode("\n", trim($content));
    $data = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $decoded = json_decode($line, true);
        if ($decoded !== null) {
            $data[] = $decoded;
        }
    }
    return $data;
}

function encodeJsonL($data) {
    $lines = [];
    foreach ($data as $item) {
        $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
    }
    return implode("\n", $lines);
}

function salvarDadosQuiz($dados) {
    // Sincroniza com MySQL
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();
        
        // Pega o ID do quiz inicial
        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE name = 'Quiz Inicial' LIMIT 1");
        $stmt->execute();
        $quiz = $stmt->fetch();
        $quiz_id = $quiz ? $quiz['id'] : 1;
        
        // Limpa questões e opções atuais do Quiz Inicial
        // Ao deletar questions, as options são deletadas via ON DELETE CASCADE
        $pdo->exec("DELETE FROM questions WHERE quiz_id = $quiz_id");
        
        $stmt_question = $pdo->prepare("INSERT INTO questions (quiz_id, external_id, pergunta, resposta_correta, explicacao_feedback, topico, nivel) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_option = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
        
        foreach ($dados as $q) {
            $stmt_question->execute([
                $quiz_id,
                $q['id'] ?? null,
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

        // Salva no JSON apenas como backup secundário
        $json_data = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents('quiz_data.json', $json_data);

        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Erro ao salvar no MySQL: " . $e->getMessage());
        return false;
    }
}

// Funções de Autenticação
function autenticarUsuario($username, $password) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Retorna dados do usuário (incluindo is_admin)
        }
        return false;
    } catch (Exception $e) {
        error_log("Erro na autenticação: " . $e->getMessage());
        return false;
    }
}

function registrarUsuario($username, $password) {
    try {
        $pdo = get_db_connection();
        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Este apelido já está sendo usado.'];
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, 0]);
        
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro ao criar conta.'];
    }
}

// Funções para Ranking
function salvarScore($user_id, $quiz_id, $score, $total) {
    try {
        $pdo = get_db_connection();
        $percentage = ($score / $total) * 100;
        
        $stmt = $pdo->prepare("INSERT INTO scores (user_id, quiz_id, score, total, percentage) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $quiz_id, $score, $total, $percentage]);
    } catch (Exception $e) {
        error_log("Erro ao salvar score: " . $e->getMessage());
        return false;
    }
}

// Interações de Questões
function salvarInteracaoQuestao($user_id, $question_id, $comment, $is_flagged) {
    try {
        $pdo = get_db_connection();
        
        // Verifica se já existe uma interação desse usuário para essa questão
        $stmt = $pdo->prepare("SELECT id FROM question_interactions WHERE user_id = ? AND question_id = ?");
        $stmt->execute([$user_id, $question_id]);
        $interaction = $stmt->fetch();
        
        if ($interaction) {
            $stmt = $pdo->prepare("UPDATE question_interactions SET comment = ?, is_flagged = ? WHERE id = ?");
            return $stmt->execute([$comment, $is_flagged ? 1 : 0, $interaction['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO question_interactions (user_id, question_id, comment, is_flagged) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$user_id, $question_id, $comment, $is_flagged ? 1 : 0]);
        }
    } catch (Exception $e) {
        error_log("Erro ao salvar interação: " . $e->getMessage());
        return false;
    }
}

function obterInteracoes() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("
            SELECT i.*, u.username, q.pergunta, q.topico 
            FROM question_interactions i
            JOIN users u ON i.user_id = u.id
            JOIN questions q ON i.question_id = q.id
            ORDER BY i.is_flagged DESC, i.created_at DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao obter interações: " . $e->getMessage());
        return [];
    }
}

function obterComentariosPublicos($question_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT i.*, u.username, 
                   (SELECT COUNT(*) FROM comment_votes WHERE interaction_id = i.id) as total_votes
            FROM question_interactions i
            JOIN users u ON i.user_id = u.id
            WHERE i.question_id = ? AND i.comment IS NOT NULL AND i.comment != ''
            ORDER BY total_votes DESC, i.created_at DESC
        ");
        $stmt->execute([$question_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao obter comentários públicos: " . $e->getMessage());
        return [];
    }
}

function votarNoComentario($user_id, $interaction_id) {
    try {
        $pdo = get_db_connection();
        // Verifica se já votou
        $stmt = $pdo->prepare("SELECT id FROM comment_votes WHERE user_id = ? AND interaction_id = ?");
        $stmt->execute([$user_id, $interaction_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Você já votou neste comentário.'];
        }

        $stmt = $pdo->prepare("INSERT INTO comment_votes (user_id, interaction_id) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $interaction_id])) {
            // Retorna o novo total de votos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_votes WHERE interaction_id = ?");
            $stmt->execute([$interaction_id]);
            $count = $stmt->fetchColumn();
            return ['success' => true, 'votes' => $count];
        }
        return ['success' => false, 'message' => 'Erro ao registrar voto.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()];
    }
}

function obterRanking($quiz_id = 1, $limit = 10) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT u.username, s.score, s.total, s.percentage, s.completed_at 
            FROM scores s
            JOIN users u ON s.user_id = u.id
            WHERE s.quiz_id = ?
            ORDER BY s.percentage DESC, s.completed_at ASC
            LIMIT ?
        ");
        $stmt->execute([$quiz_id, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function toggleVisibilidadeQuestao($question_id, $visible) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE questions SET is_visible = ? WHERE id = ?");
        return $stmt->execute([$visible ? 1 : 0, $question_id]);
    } catch (Exception $e) {
        return false;
    }
}

function excluirInteracao($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM question_interactions WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Erro ao excluir interação: " . $e->getMessage());
        return false;
    }
}

function resolverFlag($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE question_interactions SET is_flagged = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Erro ao resolver flag: " . $e->getMessage());
        return false;
    }
}

// Funções de Gerenciamento de Quizzes (Mantidas para compatibilidade)
function listarQuizzes() {
    try {
        $pdo = get_db_connection();
        // Busca quizzes com contagem de questões
        $stmt = $pdo->query("
            SELECT q.*, 
                   (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as total_questoes 
            FROM quizzes q 
            ORDER BY q.discipline, q.name
        ");
        $quizzes = $stmt->fetchAll();
        
        foreach ($quizzes as &$q) {
            // Compatibilidade com o template antigo que usava nomes em português
            $q['nome'] = $q['name'];
            $q['disciplina'] = $q['discipline'];
            $q['caminho'] = $q['id']; // Agora o "caminho" é o ID do banco
            $q['data_modificacao'] = strtotime($q['created_at']);
            $q['tamanho'] = 0; 
            
            // Busca estatísticas de tópicos
            $stmt_stats = $pdo->prepare("SELECT topico, COUNT(*) as qty FROM questions WHERE quiz_id = ? GROUP BY topico");
            $stmt_stats->execute([$q['id']]);
            $q['topicos'] = $stmt_stats->fetchAll(PDO::FETCH_KEY_PAIR);

            // Busca estatísticas de níveis
            $stmt_stats = $pdo->prepare("SELECT nivel, COUNT(*) as qty FROM questions WHERE quiz_id = ? GROUP BY nivel");
            $stmt_stats->execute([$q['id']]);
            $q['niveis'] = $stmt_stats->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        
        return $quizzes;
    } catch (Exception $e) {
        error_log("Erro ao listar quizzes: " . $e->getMessage());
        return [];
    }
}

function carregarQuiz($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? AND is_visible = 1");
        $stmt->execute([$id]);
        $questoes = $stmt->fetchAll();
        foreach ($questoes as &$q) {
            $stmt_opt = $pdo->prepare("SELECT option_text FROM options WHERE question_id = ?");
            $stmt_opt->execute([$q['id']]);
            $q['opcoes_disponiveis'] = $stmt_opt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $questoes;
    } catch (Exception $e) {
        return [];
    }
}

function obterDisciplinas() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT DISTINCT discipline FROM quizzes ORDER BY discipline");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return ['geral'];
    }
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'geral' : $text;
}

function salvarQuizComo($dados, $nome, $disciplina) {
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO quizzes (name, discipline) VALUES (?, ?)");
        $stmt->execute([$nome, $disciplina]);
        $quiz_id = $pdo->lastInsertId();
        
        $stmt_question = $pdo->prepare("INSERT INTO questions (quiz_id, external_id, pergunta, resposta_correta, explicacao_feedback, topico, nivel) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_option = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
        
        foreach ($dados as $q) {
            $stmt_question->execute([
                $quiz_id,
                $q['id'] ?? null,
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
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        error_log("Erro ao salvar quiz como: " . $e->getMessage());
        return false;
    }
}

function excluirQuiz($id) {
    try {
        $pdo = get_db_connection();
        // Garante que não estamos excluindo o quiz padrão (ID 1)
        if ($id == 1) return false;
        
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Erro ao excluir quiz: " . $e->getMessage());
        return false;
    }
}

// Proteção CSRF
function gerarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function h_json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}
?>