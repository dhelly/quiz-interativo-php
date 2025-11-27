<?php
define('QUIZ_DATA_FILE', 'quiz_data.json');
define('QUIZZES_DIR', 'quizzes');

if (file_exists('sanitize.php')) {
    require_once 'sanitize.php';
}

$FALLBACK_QUIZ_JSON = '[
  {
    "id": 15,
    "pergunta": "A inconstitucionalidade por omissão ocorre quando o legislador permanece inerte diante de uma norma constitucional de eficácia limitada, obstando o exercício de um direito previsto na Constituição.",
    "resposta_correta": "Certo",
    "opcoes_disponiveis": ["Certo", "Errado"],
    "explicacao_feedback": "A inconstitucionalidade por omissão caracteriza-se pela falta de regulamentação de um dispositivo constitucional que depende de lei (norma de eficácia limitada), impedindo a fruição de um direito.",
    "topico": "Direito Constitucional",
    "nivel": "Intermediário"
  }
]';

function carregarDadosQuiz() {
    global $FALLBACK_QUIZ_JSON;
    
    $arquivo = 'quiz_data.json';
    
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        $dados = json_decode($conteudo, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($dados)) {
            return $dados;
        }
    }
    
    // Fallback para dados padrão
    $dados = json_decode($FALLBACK_QUIZ_JSON, true);
    return $dados;
}

function salvarDadosQuiz($dados) {
    // Prepara dados para salvar (mantém markdown)
    // Não converte para HTML aqui, pois queremos manter o markdown no arquivo
    $json_data = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents('quiz_data.json', $json_data) !== false) {
        return true;
    }
    
    return false;
}

// NOVAS FUNÇÕES PARA GERENCIAR MÚLTIPLOS QUIZZES

function salvarQuizComo($dados, $nome_arquivo, $disciplina = 'geral') {
    try {
        // Cria diretório da disciplina se não existir
        $dir_disciplina = QUIZZES_DIR . '/' . slugify($disciplina);
        if (!is_dir($dir_disciplina)) {
            mkdir($dir_disciplina, 0755, true);
        }
        
        $caminho_arquivo = $dir_disciplina . '/' . $nome_arquivo . '.json';
        $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($caminho_arquivo, $json) !== false;
    } catch (Exception $e) {
        error_log("Erro ao salvar quiz: " . $e->getMessage());
        return false;
    }
}

function carregarQuiz($caminho) {
    if (file_exists($caminho)) {
        $conteudo = file_get_contents($caminho);
        $dados = json_decode($conteudo, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($dados)) {
            return $dados;
        }
    }
    return null;
}

function listarQuizzes() {
    $quizzes = [];
    
    if (!is_dir(QUIZZES_DIR)) {
        mkdir(QUIZZES_DIR, 0755, true);
        return $quizzes;
    }
    
    $disciplinas = scandir(QUIZZES_DIR);
    
    foreach ($disciplinas as $disciplina) {
        if ($disciplina === '.' || $disciplina === '..') continue;
        
        $caminho_disciplina = QUIZZES_DIR . '/' . $disciplina;
        
        if (is_dir($caminho_disciplina)) {
            $arquivos = scandir($caminho_disciplina);
            
            foreach ($arquivos as $arquivo) {
                if (pathinfo($arquivo, PATHINFO_EXTENSION) === 'json') {
                    $caminho_completo = $caminho_disciplina . '/' . $arquivo;
                    $info_quiz = obterInfoQuiz($caminho_completo, $disciplina);
                    
                    if ($info_quiz) {
                        $quizzes[] = $info_quiz;
                    }
                }
            }
        }
    }
    
    // Ordena por disciplina e depois por nome
    usort($quizzes, function($a, $b) {
        if ($a['disciplina'] === $b['disciplina']) {
            return strcmp($a['nome'], $b['nome']);
        }
        return strcmp($a['disciplina'], $b['disciplina']);
    });
    
    return $quizzes;
}

function obterInfoQuiz($caminho_arquivo, $disciplina) {
    try {
        $dados = carregarQuiz($caminho_arquivo);
        if (!$dados) return null;
        
        $nome_arquivo = pathinfo($caminho_arquivo, PATHINFO_FILENAME);
        $estatisticas = calcularEstatisticasQuiz($dados);
        
        return [
            'caminho' => $caminho_arquivo,
            'nome' => $nome_arquivo,
            'disciplina' => $disciplina,
            'total_questoes' => count($dados),
            'data_modificacao' => filemtime($caminho_arquivo),
            'topicos' => $estatisticas['topicos'],
            'niveis' => $estatisticas['niveis'],
            'tamanho' => filesize($caminho_arquivo)
        ];
    } catch (Exception $e) {
        error_log("Erro ao obter info do quiz: " . $e->getMessage());
        return null;
    }
}

function calcularEstatisticasQuiz($dados) {
    $topicos = [];
    $niveis = [];
    
    foreach ($dados as $questao) {
        $topico = $questao['topico'] ?? 'Geral';
        $nivel = $questao['nivel'] ?? 'Não especificado';
        
        $topicos[$topico] = ($topicos[$topico] ?? 0) + 1;
        $niveis[$nivel] = ($niveis[$nivel] ?? 0) + 1;
    }
    
    return [
        'topicos' => $topicos,
        'niveis' => $niveis
    ];
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'geral';
    }
    
    return $text;
}

function excluirQuiz($caminho_arquivo) {
    try {
        if (file_exists($caminho_arquivo)) {
            return unlink($caminho_arquivo);
        }
        return false;
    } catch (Exception $e) {
        error_log("Erro ao excluir quiz: " . $e->getMessage());
        return false;
    }
}

function obterDisciplinas() {
    $disciplinas = [];
    
    if (!is_dir(QUIZZES_DIR)) {
        return $disciplinas;
    }
    
    $itens = scandir(QUIZZES_DIR);
    
    foreach ($itens as $item) {
        if ($item !== '.' && $item !== '..' && is_dir(QUIZZES_DIR . '/' . $item)) {
            $disciplinas[] = $item;
        }
    }
    
    sort($disciplinas);
    return $disciplinas;
}
?>