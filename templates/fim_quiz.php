<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Concluído - Inútil.App</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --text-light: #ecf0f1;
            --text-muted: #bdc3c7;
            --bg-dark: #1a252f;
            --bg-card: #2c3e50;
            --border-color: #34495e;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--bg-dark);
            color: var(--text-light); 
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container { 
            max-width: 600px; 
            background: var(--bg-card);
            padding: 40px; 
            border-radius: 8px; 
            border: 1px solid var(--border-color);
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        
        .resultado {
            font-size: 2em;
            margin-bottom: 20px;
            color: var(--text-light);
        }
        
        .pontuacao {
            font-size: 3em;
            font-weight: bold;
            margin: 20px 0;
            color: var(--accent-color);
        }
        
        .estatisticas {
            background: var(--secondary-color);
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border: 1px solid var(--border-color);
        }
        
        .barra-progresso {
            height: 20px;
            background: var(--bg-dark);
            border-radius: 10px;
            margin: 15px 0;
            overflow: hidden;
        }
        
        .progresso-preenchido {
            height: 100%;
            background: var(--accent-color);
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
        
        .info-revisao {
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning-color);
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            text-align: left;
        }
        
        .info-sucesso {
            background: rgba(39, 174, 96, 0.1);
            border-left: 4px solid var(--success-color);
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        
        button {
            background: var(--accent-color);
            color: var(--text-light);
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            margin: 10px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            font-weight: 500;
            display: inline-block;
            width: auto;
            min-width: 200px;
        }
        
        button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success-color);
        }
        
        .btn-success:hover {
            background: #219653;
        }
        
        .btn-warning {
            background: var(--warning-color);
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        .btn-error {
            background: var(--error-color);
        }
        
        .btn-error:hover {
            background: #c0392b;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 25px;
            gap: 10px;
        }
        
        .estatistica-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
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
            <?php echo $dados['acertos_total']; ?>/<?php echo $dados['total_perguntas']; ?>
        </div>
        
        <div class="estatisticas">
            <div style="font-size: 1.2em; margin-bottom: 10px; color: var(--text-muted);">
                <?php echo number_format($percentual, 1); ?>% de acertos
            </div>
            
            <div class="barra-progresso">
                <div class="progresso-preenchido" style="width: <?php echo $percentual; ?>%"></div>
            </div>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.9em; color: var(--text-muted);">
                <span>0%</span>
                <span>100%</span>
            </div>
            
            <?php if (!$dados['modo_revisao']): ?>
                <div class="estatistica-item">
                    <span>Questões Erradas:</span>
                    <strong style="color: var(--error-color);"><?php echo $dados['total_erradas']; ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($dados['sem_erradas']): ?>
            <div class="info-sucesso">
                🎉 Parabéns! Você não tem mais questões para revisar.
            </div>
        <?php elseif ($dados['total_erradas'] > 0 && !$dados['modo_revisao']): ?>
            <div class="info-revisao">
                <strong>📖 Dica de Estudo:</strong> Você errou <?php echo $dados['total_erradas']; ?> questão(ões). 
                Reveja essas questões para consolidar seu aprendizado!
            </div>
        <?php elseif ($dados['modo_revisao'] && $dados['total_erradas'] > 0): ?>
            <div class="info-revisao">
                <strong>📖 Continue Revisando:</strong> Você ainda tem <?php echo $dados['total_erradas']; ?> questão(ões) para revisar.
            </div>
        <?php elseif ($dados['total_erradas'] === 0 && !$dados['modo_revisao']): ?>
            <div class="info-sucesso">
                🎉 Excelente! Você acertou todas as questões!
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <button onclick="location.href='index.php'">
                <?php echo $dados['modo_revisao'] ? '🔄 Novo Quiz' : '🔄 Reiniciar Quiz'; ?>
            </button>
            
            <?php if ($dados['total_erradas'] > 0 && !$dados['modo_revisao']): ?>
                <button class="btn-warning" onclick="location.href='index.php?acao=revisar_erradas'">
                    📚 Revisar Erradas (<?php echo $dados['total_erradas']; ?>)
                </button>
            <?php elseif ($dados['total_erradas'] > 0 && $dados['modo_revisao']): ?>
                <button class="btn-warning" onclick="location.href='index.php?acao=revisar_erradas'">
                    🔄 Continuar Revisão (<?php echo $dados['total_erradas']; ?>)
                </button>
            <?php endif; ?>
            
            <?php if ($dados['total_erradas'] > 0): ?>
                <button class="btn-error" onclick="location.href='index.php?acao=limpar_revisao'">
                    🗑️ Limpar Histórico
                </button>
            <?php endif; ?>
            
            <button class="btn-success" onclick="location.href='admin.php'">⚙️ Painel Admin</button>
        </div>
    </div>

    <script>
        // Anima a barra de progresso
        setTimeout(() => {
            document.querySelector('.progresso-preenchido').style.width = '<?php echo $percentual; ?>%';
        }, 500);
    </script>
</body>
</html>