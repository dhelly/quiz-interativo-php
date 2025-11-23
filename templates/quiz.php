<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Interativo - Inútil App</title>
    <style>
        /* CSS idêntico ao template quiz.html anterior */
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: var(--bg-dark);
            color: var(--text-light); 
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .container { 
            max-width: 900px; 
            margin: 20px auto;
            background: var(--bg-card);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        
        .header {
            background: var(--secondary-color);
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h1 {
            color: var(--text-light);
            font-size: 1.5em;
            font-weight: 600;
            margin: 0;
        }
        
        .content {
            padding: 30px;
        }
        
        .progresso {
            background: var(--secondary-color);
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        
        .progresso-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9em;
            color: var(--text-muted);
        }
        
        .progresso-bar {
            height: 6px;
            background: var(--bg-dark);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progresso-fill {
            height: 100%;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .pergunta { 
            font-size: 1.2em; 
            margin-bottom: 25px; 
            line-height: 1.6;
            background: var(--secondary-color);
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid var(--accent-color);
        }
        
        .feedback { 
            padding: 20px; 
            border-radius: 6px; 
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .acerto { 
            background: rgba(39, 174, 96, 0.1); 
            border-left: 4px solid var(--success-color);
        }
        
        .erro { 
            background: rgba(231, 76, 60, 0.1); 
            border-left: 4px solid var(--error-color);
        }
        
        .explicacao { 
            margin-top: 15px; 
            font-size: 0.95em;
            background: rgba(52, 73, 94, 0.5);
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid var(--accent-color);
        }
        
        .questao-info {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .badge {
            background: var(--accent-color);
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
            color: var(--text-light);
        }
        
        .badge.topico {
            background: var(--secondary-color);
        }
        
        .badge.nivel {
            background: var(--success-color);
        }
        
        .opcoes-container {
            margin: 25px 0;
        }
        
        .opcao-label {
            display: block;
            background: var(--secondary-color);
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .opcao-label:hover {
            background: #3a506b;
            transform: translateX(5px);
        }
        
        .opcao-label.selected {
            border-color: var(--accent-color);
            background: #3a506b;
        }
        
        input[type="radio"] { 
            margin-right: 15px; 
            transform: scale(1.1);
        }
        
        button { 
            background: var(--accent-color);
            color: var(--text-light); 
            border: none; 
            padding: 15px 30px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            border: 1px solid var(--border-color);
        }
        
        button:hover { 
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        button:disabled {
            background: var(--secondary-color);
            cursor: not-allowed;
            transform: none;
            opacity: 0.6;
        }
        
        .admin-panel {
            background: var(--secondary-color);
            padding: 15px 20px;
            border-radius: 6px;
            margin-top: 25px;
            border: 1px solid var(--border-color);
            font-size: 0.9em;
        }
        
        .admin-links {
            display: flex;
            gap: 15px;
            margin-top: 8px;
        }
        
        .admin-links a {
            color: var(--accent-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .admin-links a:hover {
            color: var(--text-light);
        }
        
        .questao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .questao-numero {
            background: var(--accent-color);
            color: var(--text-light);
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>🎓 Quiz Jurídico - Inútil App</h1>
        </div>

        <div class="content">
            <div class="progresso">
                <div class="progresso-info">
                    <span>Questão <?php echo $dados['numero_questao']; ?> de <?php echo $dados['total_perguntas']; ?></span>
                    <span>Acertos: <?php echo $dados['acertos_total']; ?> / <?php echo $dados['total_perguntas']; ?></span>
                </div>
                <div class="progresso-bar">
                    <div class="progresso-fill" style="width: <?php echo ($dados['numero_questao'] / $dados['total_perguntas'] * 100); ?>%"></div>
                </div>
            </div>

            <?php if ($dados['feedback']): ?>
                <div class="feedback <?php echo (strpos($dados['feedback']['mensagem'], '✅') !== false) ? 'acerto' : 'erro'; ?>">
                    <strong><?php echo $dados['feedback']['mensagem']; ?></strong>
                    <?php if ($dados['feedback']['explicacao']): ?>
                        <div class="explicacao">
                            📚 <strong>Explicação:</strong> <?php echo $dados['feedback']['explicacao']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="questao-header">
                <div class="questao-info">
                    <span class="badge">ID: <?php echo $dados['questao']['id']; ?></span>
                    <span class="badge topico"><?php echo $dados['questao']['topico']; ?></span>
                    <span class="badge nivel"><?php echo $dados['questao']['nivel']; ?></span>
                </div>
                <div class="questao-numero">#<?php echo $dados['numero_questao']; ?></div>
            </div>

            <div class="pergunta"><?php echo $dados['questao']['pergunta']; ?></div>

            <form method="POST" action="index.php?acao=responder" id="quizForm">
                
                <div class="opcoes-container">
                    <?php foreach ($dados['questao']['opcoes_disponiveis'] as $opcao): ?>
                        <label class="opcao-label" onclick="selectOption(this)">
                            <input type="radio" name="resposta" value="<?php echo $opcao; ?>" required>
                            <?php echo $opcao; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <input type="hidden" name="questao_id" value="<?php echo $dados['questao']['id']; ?>">
                <input type="hidden" name="acertos_anteriores" value="<?php echo $dados['acertos_total']; ?>">
                
                <button type="submit" id="submitBtn" disabled>
                    Responder e Avançar →
                </button>
            </form>

            <div class="admin-panel">
                <strong>🔧 Painel de Controle</strong>
                <div class="admin-links">
                    <a href="admin.php">⚙️ Gerenciar Dados</a>
                    <a href="index.php?acao=reload">🔄 Recarregar</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        function selectOption(label) {
            document.querySelectorAll('.opcao-label').forEach(l => {
                l.classList.remove('selected');
            });
            
            label.classList.add('selected');
            document.getElementById('submitBtn').disabled = false;
        }

        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="resposta"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Por favor, selecione uma resposta antes de continuar.');
            }
        });

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('submitBtn').disabled = false;
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key >= '1' && e.key <= '4') {
                const index = parseInt(e.key) - 1;
                const options = document.querySelectorAll('input[type="radio"]');
                if (options[index]) {
                    options[index].checked = true;
                    selectOption(options[index].closest('.opcao-label'));
                }
            }
        });
    </script>
</body>
</html>