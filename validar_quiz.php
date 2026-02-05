<?php
// Função de validação robusta do JSON do quiz
function validarEstruturaQuiz($dados) {
    $erros = [];
    
    // Verifica se é um array
    if (!is_array($dados)) {
        return ["O JSON deve ser um array de questões"];
    }
    
    // Verifica se há pelo menos uma questão
    if (count($dados) === 0) {
        return ["O array de questões não pode estar vazio"];
    }
    
    foreach ($dados as $index => $questao) {
        $numero_questao = $index + 1;
        
        // Verifica se é um objeto/array associativo
        if (!is_array($questao) && !is_object($questao)) {
            $erros[] = "Questão {$numero_questao}: Deve ser um objeto";
            continue;
        }
        
        // Converte para array para facilitar a validação
        $questao = (array)$questao;
        
        // Campos obrigatórios
        $campos_obrigatorios = [
            'id' => 'inteiro',
            'pergunta' => 'string', 
            'resposta_correta' => 'string',
            'opcoes_disponiveis' => 'array',
            'explicacao_feedback' => 'string',
            'topico' => 'string',
            'nivel' => 'string'
        ];
        
        foreach ($campos_obrigatorios as $campo => $tipo) {
            if (!array_key_exists($campo, $questao)) {
                $erros[] = "Questão {$numero_questao}: Campo obrigatório '{$campo}' não encontrado";
                continue;
            }
            
            $valor = $questao[$campo];
            
            // Validação por tipo
            switch ($tipo) {
                case 'inteiro':
                    if (!is_int($valor)) {
                        $erros[] = "Questão {$numero_questao}: Campo '{$campo}' deve ser um número inteiro";
                    }
                    break;
                    
                case 'string':
                    if (!is_string($valor) || trim($valor) === '') {
                        $erros[] = "Questão {$numero_questao}: Campo '{$campo}' deve ser uma string não vazia";
                    }
                    break;
                    
                case 'array':
                    if (!is_array($valor)) {
                        $erros[] = "Questão {$numero_questao}: Campo '{$campo}' deve ser um array";
                    } elseif (count($valor) < 2) {
                        $erros[] = "Questão {$numero_questao}: Campo '{$campo}' deve ter pelo menos 2 opções";
                    } elseif (!array_filter($valor, 'is_string')) {
                        $erros[] = "Questão {$numero_questao}: Campo '{$campo}' deve conter apenas strings";
                    }
                    break;
            }
        }
        
        // Validações específicas adicionais
        if (isset($questao['id']) && $questao['id'] <= 0) {
            $erros[] = "Questão {$numero_questao}: ID deve ser maior que zero";
        }
        
        if (isset($questao['opcoes_disponiveis']) && is_array($questao['opcoes_disponiveis'])) {
            // Verifica se a resposta_correta está nas opções_disponiveis
            if (isset($questao['resposta_correta']) && !in_array($questao['resposta_correta'], $questao['opcoes_disponiveis'])) {
                $erros[] = "Questão {$numero_questao}: A resposta_correta '{$questao['resposta_correta']}' não está presente nas opcoes_disponiveis";
            }
            
            // Verifica se há opções duplicadas
            $opcoes_unicas = array_unique($questao['opcoes_disponiveis']);
            if (count($opcoes_unicas) !== count($questao['opcoes_disponiveis'])) {
                $erros[] = "Questão {$numero_questao}: Existem opções duplicadas em opcoes_disponiveis";
            }
            
            // Verifica se todas as opções são strings não vazias
            foreach ($questao['opcoes_disponiveis'] as $i => $opcao) {
                if (!is_string($opcao) || trim($opcao) === '') {
                    $erros[] = "Questão {$numero_questao}: Opção " . ($i + 1) . " em opcoes_disponiveis deve ser uma string não vazia";
                }
            }
        }
        
        // Valida comprimento mínimo dos textos (Removido para permitir flexibilidade)
    }
    
    // Verifica IDs duplicados
    $ids = [];
    foreach ($dados as $questao) {
        $questao = (array)$questao;
        if (isset($questao['id'])) {
            $id = $questao['id'];
            if (in_array($id, $ids)) {
                $erros[] = "ID duplicado encontrado: {$id}";
            }
            $ids[] = $id;
        }
    }
    
    return $erros;
}

// Função para validar e corrigir IDs sequenciais se necessário
function corrigirIDsSequenciais($dados) {
    $ids_corrigidos = false;
    
    foreach ($dados as $index => &$questao) {
        $questao = (array)$questao;
        $novo_id = $index + 1;
        
        if ($questao['id'] !== $novo_id) {
            $questao['id'] = $novo_id;
            $ids_corrigidos = true;
        }
    }
    
    return [$dados, $ids_corrigidos];
}
