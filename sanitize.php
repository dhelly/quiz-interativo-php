<?php
// Funções para converter entre markdown e HTML

function markdownParaHtml($texto) {
    if (!is_string($texto)) {
        return $texto;
    }
    
    $lines = explode("\n", $texto);
    $parsedLines = [];
    $tableBuffer = [];
    $inTable = false;
    
    foreach ($lines as $line) {
        $trimLine = trim($line);
        
        // Detectar tabelas
        if (strpos($trimLine, '|') === 0 && substr($trimLine, -1) === '|') {
            $inTable = true;
            $tableBuffer[] = $trimLine;
            continue;
        } else {
            if ($inTable) {
                $parsedLines[] = processarTabelaMarkdown($tableBuffer);
                $tableBuffer = [];
                $inTable = false;
            }
        }
        
        // Headers
        if (preg_match('/^(#{1,6})\s+(.+)$/', $trimLine, $matches)) {
            $level = strlen($matches[1]);
            $content = $matches[2];
            $parsedLines[] = "<h{$level}>{$content}</h{$level}>";
            continue;
        }
        
        $parsedLines[] = $line;
    }
    
    // Processar buffer de tabela restante
    if ($inTable) {
        $parsedLines[] = processarTabelaMarkdown($tableBuffer);
    }
    
    $texto = implode("\n", $parsedLines);
    
    // Preservar quebras de linha (mas removemos as quebras extras depois de blocos HTML)
    $texto = nl2br($texto);
    
    // Limpar <br> após fechamento de blocos para evitar espaços extras
    $texto = preg_replace('/(<\/h[1-6]>)\s*<br\s*\/?>/i', '$1', $texto);
    $texto = preg_replace('/(<\/table>)\s*<br\s*\/?>/i', '$1', $texto);
    
    // Converter código inline
    $texto = preg_replace('/`([^`]+)`/', '<code>$1</code>', $texto);
    
    // Converter blocos de código
    $texto = preg_replace('/```(\w+)?\s*([^`]+)```/s', '<pre><code class="$1">$2</code></pre>', $texto);
    
    // Converter negrito
    $texto = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $texto);
    
    // Converter itálico
    $texto = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $texto);
    
    // Corrigir tags escapadas que podem vir do JSON (ex: <\/b>)
    $texto = str_replace(['<\\/', '<\\'], ['</', '<'], $texto);
    
    return $texto;
}

function processarTabelaMarkdown($lines) {
    if (empty($lines)) return '';
    
    $html = '<div class="table-responsive"><table class="markdown-table">';
    $isHeader = true;
    
    foreach ($lines as $index => $line) {
        // Ignora linha de separação (|---|)
        if (preg_match('/^\|\s*:?-+:?\s*\|/', $line)) {
            continue;
        }
        
        $cells = array_values(array_filter(explode('|', $line), function($v) { 
            return trim($v) !== ''; 
        }));
        
        // Se for a primeira linha e a próxima parecer um separador, é header
        if ($isHeader && isset($lines[$index + 1]) && preg_match('/^\|\s*:?-+:?\s*\|/', $lines[$index+1])) {
            $html .= '<thead><tr>';
            foreach ($cells as $cell) {
                $html .= '<th>' . trim($cell) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            $isHeader = false;
        } else {
            if ($index === 0 && $isHeader) {
                 // Caso especial: tabela sem header explícito ou falha na detecção
                 $html .= '<tbody>';
                 $isHeader = false;
            }
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . trim($cell) . '</td>';
            }
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody></table></div>';
    return $html;
}

function htmlParaMarkdown($texto) {
    if (!is_string($texto)) {
        return $texto;
    }
    
    // Remover <br> e converter para quebras de linha
    $texto = preg_replace('/<br\s*\/?>/i', "\n", $texto);
    
    // Converter <code> para crases
    $texto = preg_replace('/<code>([^<]*)<\/code>/', '`$1`', $texto);
    
    // Converter <pre><code> para blocos
    $texto = preg_replace('/<pre><code(?:\s+class="([^"]*)")?>([^<]*)<\/code><\/pre>/s', '```$1$2```', $texto);
    
    // Converter <strong> para **
    $texto = preg_replace('/<strong>([^<]*)<\/strong>/', '**$1**', $texto);
    
    // Converter <em> para *
    $texto = preg_replace('/<em>([^<]*)<\/em>/', '*$1*', $texto);
    
    return $texto;
}

function prepararDadosParaEditor($dados) {
    foreach ($dados as &$questao) {
        $questao['pergunta'] = htmlParaMarkdown($questao['pergunta']);
        $questao['explicacao_feedback'] = htmlParaMarkdown($questao['explicacao_feedback']);
        $questao['resposta_correta'] = htmlParaMarkdown($questao['resposta_correta']);
        
        if (isset($questao['opcoes_disponiveis']) && is_array($questao['opcoes_disponiveis'])) {
            foreach ($questao['opcoes_disponiveis'] as &$opcao) {
                $opcao = htmlParaMarkdown($opcao);
            }
        }
    }
    
    return $dados;
}

/**
 * Atalho para htmlspecialchars para evitar ataques XSS
 */
function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function sanitizarDadosQuiz($dados) {
    return prepararDadosParaQuiz($dados);
}

function prepararDadosParaQuiz($dados) {
    foreach ($dados as &$questao) {
        $questao['pergunta'] = markdownParaHtml($questao['pergunta']);
        $questao['explicacao_feedback'] = markdownParaHtml($questao['explicacao_feedback']);
        $questao['resposta_correta'] = markdownParaHtml($questao['resposta_correta']);
        
        if (isset($questao['opcoes_disponiveis']) && is_array($questao['opcoes_disponiveis'])) {
            foreach ($questao['opcoes_disponiveis'] as &$opcao) {
                $opcao = markdownParaHtml($opcao);
            }
        }
    }
    
    return $dados;
}
?>