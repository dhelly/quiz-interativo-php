<?php
define('QUIZ_DATA_FILE', 'quiz_data.json');

$FALLBACK_QUIZ_JSON = '[
  {
    "id": 15,
    "pergunta": "A inconstitucionalidade por omissão ocorre quando o legislador permanece inerte diante de uma norma constitucional de eficácia limitada, obstando o exercício de um direito previsto na Constituição.",
    "resposta_correta": "Certo",
    "opcoes_disponiveis": ["Certo", "Errado"],
    "explicacao_feedback": "A inconstitucionalidade por omissão caracteriza-se pela falta de regulamentação de um dispositivo constitucional que depende de lei (norma de eficácia limitada), impedindo a fruição de um direito.",
    "topico": "Direito Constitucional",
    "nivel": "Intermediário"
  },
  {
    "id": 16,
    "pergunta": "O princípio da reserva do possível é ilimitado e pode ser invocado para justificar a inação do Estado em prover direitos sociais essenciais.",
    "resposta_correta": "Errado",
    "opcoes_disponiveis": ["Certo", "Errado"],
    "explicacao_feedback": "O princípio da reserva do possível não é absoluto. Sua invocação deve ser devidamente justificada e não pode levar à inércia total do Estado, especialmente no que tange aos direitos fundamentais e ao mínimo existencial.",
    "topico": "Direito Constitucional",
    "nivel": "Avançado"
  },
  {
    "id": 17,
    "pergunta": "O Habeas Corpus é o remédio constitucional cabível para proteger direito líquido e certo não amparado por Habeas Data ou Habeas Corpus.",
    "resposta_correta": "Errado",
    "opcoes_disponiveis": ["Certo", "Errado"],
    "explicacao_feedback": "A descrição corresponde ao **Mandado de Segurança**. O Habeas Corpus protege a liberdade de locomoção.",
    "topico": "Direito Processual Constitucional",
    "nivel": "Básico"
  }
]';

function carregarDadosQuiz() {
    global $FALLBACK_QUIZ_JSON;
    
    if (file_exists(QUIZ_DATA_FILE)) {
        try {
            $content = file_get_contents(QUIZ_DATA_FILE);
            if (!empty(trim($content))) {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao ler arquivo: " . $e->getMessage());
        }
    }
    
    // Fallback
    return json_decode($FALLBACK_QUIZ_JSON, true);
}

function salvarDadosQuiz($dados) {
    try {
        $json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(QUIZ_DATA_FILE, $json);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao salvar arquivo: " . $e->getMessage());
        return false;
    }
}
?>