<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Concluído - Inútil App</title>
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
        
        .feedback {
            background: var(--secondary-color);
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: left;
            border: 1px solid var(--border-color);
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
        
        .action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="resultado">
            <?php
            $percentual = ($dados['acertos_total'] / $dados['total_perguntas']) * 100;
            if ($percentual >= 80) {
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
        
        <div style="font-size: 1.2em; margin-bottom: 20px; color: var(--text-muted);">
            <?php echo number_format($percentual, 1); ?>% de acertos
        </div>

        <?php if ($dados['feedback']): ?>
            <div class="feedback">
                <strong>Última questão:</strong><br>
                <?php echo $dados['feedback']['mensagem']; ?><br>
                <?php if ($dados['feedback']['explicacao']): ?>
                    <div style="margin-top: 10px; padding-left: 10px; border-left: 3px solid var(--accent-color); color: var(--text-muted);">
                        <?php echo $dados['feedback']['explicacao']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <button onclick="location.href='index.php'">🔄 Reiniciar Quiz</button>
            <button class="btn-success" onclick="location.href='admin.php'">⚙️ Painel Admin</button>
        </div>
    </div>
</body>
</html>