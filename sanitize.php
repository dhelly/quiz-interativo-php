<?php
// Funções para converter entre markdown e HTML

function markdownParaHtml($texto) {
    if (!is_string($texto)) {
        return $texto;
    }
    
    // Preservar quebras de linha
    $texto = nl2br($texto);
    
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