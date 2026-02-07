<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Concluído - Inútil.App</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-resultado">
    <div class="container-resultado">
        <div class="resultado">
            <?php
            $percentual = ($dados['acertos_total'] / $dados['total_perguntas']) * 100;
            if ($dados['modo_revisao']) {
                echo '📚 Revisão Concluída!';
            } elseif ($percentual >= 80) {
                echo '🎉 Excelente!';
            } elseif ($percentual >= 60) {
                echo '👍 Bom Trabalho!';
            } else {
                echo '💪 Continue Estudando!';
            }
            ?>
        </div>
        
        <div class="pontuacao">
            <?php echo h($dados['acertos_total']); ?>/<?php echo h($dados['total_perguntas']); ?>
        </div>
        
        <div class="estatisticas">
            <div style="font-size: 1.2em; margin-bottom: 10px; color: var(--text-muted);">
                <?php echo h(number_format($percentual, 1)); ?>% de acertos
            </div>
            
            <div class="barra-progresso">
                <div class="progresso-preenchido" style="width: 0%"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.9em; color: var(--text-muted);">
                <span>0%</span>
                <span>100%</span>
            </div>
            
            <?php if (!$dados['modo_revisao']): ?>
                <div class="estatistica-item">
                    <span>Questões Erradas:</span>
                    <strong style="color: var(--error-color);"><?php echo h($dados['total_erradas']); ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($dados['sem_erradas']): ?>
            <div class="info-sucesso">
                🎉 Parabéns! Você não tem mais questões para revisar.
            </div>
        <?php elseif ($dados['total_erradas'] > 0 && !$dados['modo_revisao']): ?>
            <div class="info-revisao">
                <strong>📖 Dica de Estudo:</strong> Você errou <?php echo h($dados['total_erradas']); ?> questão(ões). 
                Reveja essas questões para consolidar seu aprendizado!
            </div>
        <?php elseif ($dados['modo_revisao'] && $dados['total_erradas'] > 0): ?>
            <div class="info-revisao">
                <strong>📖 Continue Revisando:</strong> Você ainda tem <?php echo h($dados['total_erradas']); ?> questão(ões) para revisar.
            </div>
        <?php elseif ($dados['total_erradas'] === 0 && !$dados['modo_revisao']): ?>
            <div class="info-sucesso">
                🎉 Excelente! Você acertou todas as questões!
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <button class="btn" onclick="location.href='index.php'">
                <?php echo $dados['modo_revisao'] ? '🔄 Novo Quiz' : '🔄 Reiniciar Quiz'; ?>
            </button>
            
            <?php if ($dados['total_erradas'] > 0 && !$dados['modo_revisao']): ?>
                <button class="btn btn-warning" onclick="location.href='index.php?acao=revisar_erradas'">
                    📚 Revisar Erradas (<?php echo h($dados['total_erradas']); ?>)
                </button>
            <?php elseif ($dados['total_erradas'] > 0 && $dados['modo_revisao']): ?>
                <button class="btn btn-warning" onclick="location.href='index.php?acao=revisar_erradas'">
                    🔄 Continuar Revisão (<?php echo h($dados['total_erradas']); ?>)
                </button>
            <?php endif; ?>
            
            <?php if ($dados['total_erradas'] > 0): ?>
                <button id="btnLimparHistorico" class="btn btn-error" onclick="if(confirm('Tem certeza que deseja limpar seu histórico de questões erradas?')) location.href='index.php?acao=limpar_revisao'">
                    🗑️ Limpar Histórico
                </button>
            <?php endif; ?>
            
            <button class="btn btn-success" onclick="location.href='admin.php'">⚙️ Painel Admin</button>
        </div>
    </div>

    <script>
        // Anima a barra de progresso
        setTimeout(() => {
            document.querySelector('.progresso-preenchido').style.width = '<?php echo h($percentual); ?>%';
        }, 500);
    </script>
</body>
</html>