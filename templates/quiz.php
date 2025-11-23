<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $dados['modo_revisao'] ? '📚 Revisão de Erradas - ' : '🎓 Quiz Interativo - '; ?>Inútil.App
    </title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: var(--text-light);
            font-size: 1.5em;
            font-weight: 600;
            margin: 0;
        }
        
        .modo-revisao {
            background: var(--warning-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
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
            display: none;
        }
        
        .feedback.mostrar {
            display: block;
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
        
        .badge.errada {
            background: var(--error-color);
        }
        
        .opcoes-container {
            margin: 25px 0;
        }
        
        .opcao-label {
            display: block;
            background: var(--secondary-color);
            padding: 15px 20px 15px 45px;
            margin-bottom: 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .opcao-label:hover {
            background: #3a506b;
        }
        
        .opcao-label.selecionada {
            border-color: var(--accent-color);
            background: #3a506b;
        }
        
        .opcao-label.correta {
            background: rgba(39, 174, 96, 0.2);
            border-color: var(--success-color);
        }
        
        .opcao-label.incorreta {
            background: rgba(231, 76, 60, 0.2);
            border-color: var(--error-color);
        }
        
        .numero-opcao {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent-color);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
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
        
        .btn-avancar {
            background: var(--success-color);
        }
        
        .btn-avancar:hover {
            background: #219653;
        }
        
        .btn-revisao {
            background: var(--warning-color);
        }
        
        .btn-revisao:hover {
            background: #e67e22;
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
            flex-wrap: wrap;
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

        .estatistica-resposta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.9em;
            color: var(--text-muted);
        }

        .contador-acertos {
            background: var(--success-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .contador-erradas {
            background: var(--error-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .info-revisao {
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning-color);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>
                <?php if ($dados['modo_revisao']): ?>
                    📚 Revisão de Questões Erradas
                <?php else: ?>
                    🎓 Quiz Interativo - Inútil.App
                <?php endif; ?>
            </h1>
            <?php if ($dados['modo_revisao']): ?>
                <div class="modo-revisao">MODO REVISÃO</div>
            <?php endif; ?>
        </div>

        <div class="content">
            <?php if ($dados['modo_revisao']): ?>
                <div class="info-revisao">
                    <strong>📖 Modo Revisão:</strong> Você está revisando <?php echo $dados['total_erradas']; ?> questão(ões) que errou anteriormente.
                    <a href="index.php?acao=limpar_revisao" style="color: var(--warning-color); margin-left: 10px;">🔄 Limpar Histórico</a>
                </div>
            <?php endif; ?>
            
            <div class="progresso">
                <div class="progresso-info">
                    <span>Questão <?php echo $dados['numero_questao']; ?> de <?php echo $dados['total_perguntas']; ?></span>
                    <span>
                        Acertos: <span class="contador-acertos"><?php echo $dados['acertos_total']; ?></span>
                        <?php if (!$dados['modo_revisao']): ?>
                            / Erradas: <span class="contador-erradas"><?php echo $dados['total_erradas']; ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="progresso-bar">
                    <div class="progresso-fill" style="width: <?php echo ($dados['numero_questao'] / $dados['total_perguntas'] * 100); ?>%"></div>
                </div>
            </div>

            <!-- Feedback dinâmico -->
            <div class="feedback" id="feedback">
                <div id="feedbackMensagem"></div>
                <div class="explicacao" id="feedbackExplicacao"></div>
            </div>

            <div class="questao-header">
                <div class="questao-info">
                    <span class="badge">ID: <?php echo $dados['questao']['id']; ?></span>
                    <span class="badge topico"><?php echo $dados['questao']['topico']; ?></span>
                    <span class="badge nivel"><?php echo $dados['questao']['nivel']; ?></span>
                    <?php if (in_array($dados['questao']['id'], $_SESSION['questoes_erradas'])): ?>
                        <span class="badge errada">❌ Errada Anteriormente</span>
                    <?php endif; ?>
                </div>
                <div class="questao-numero">#<?php echo $dados['numero_questao']; ?></div>
            </div>

            <div class="pergunta"><?php echo $dados['questao']['pergunta']; ?></div>

            <div class="opcoes-container">
                <?php foreach ($dados['questao']['opcoes_disponiveis'] as $index => $opcao): ?>
                    <label class="opcao-label" data-value="<?php echo $opcao; ?>">
                        <div class="numero-opcao"><?php echo $index + 1; ?></div>
                        <?php echo $opcao; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Botão de avançar (inicialmente oculto) -->
            <button class="btn-avancar" id="btnAvancar" style="display: none;">
                <?php if ($dados['proxima_id']): ?>
                    ➡️ Avançar para Próxima Questão
                <?php else: ?>
                    🏁 <?php echo $dados['modo_revisao'] ? 'Finalizar Revisão' : 'Ver Resultado Final'; ?>
                <?php endif; ?>
            </button>

            <div class="admin-panel">
                <strong>🔧 Painel de Controle</strong>
                <div class="admin-links">
                    <a href="admin.php">⚙️ Gerenciar Dados</a>
                    <a href="index.php?acao=reload">🔄 Recarregar</a>
                    <?php if (!$dados['modo_revisao'] && $dados['total_erradas'] > 0): ?>
                        <a href="index.php?acao=revisar_erradas">📚 Revisar Erradas (<?php echo $dados['total_erradas']; ?>)</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Variáveis globais
        const respostaCorreta = "<?php echo $dados['resposta_correta']; ?>";
        const explicacao = `<?php echo $dados['explicacao']; ?>`;
        const questaoId = <?php echo $dados['questao']['id']; ?>;
        let acertosAtuais = <?php echo $dados['acertos_total']; ?>;
        let questaoRespondida = false;
        const modoRevisao = <?php echo $dados['modo_revisao'] ? 'true' : 'false'; ?>;

        // Elementos DOM
        const opcoes = document.querySelectorAll('.opcao-label');
        const feedback = document.getElementById('feedback');
        const feedbackMensagem = document.getElementById('feedbackMensagem');
        const feedbackExplicacao = document.getElementById('feedbackExplicacao');
        const btnAvancar = document.getElementById('btnAvancar');

        // Adiciona eventos de clique nas opções
        opcoes.forEach(opcao => {
            opcao.addEventListener('click', function() {
                if (questaoRespondida) return;
                
                const respostaSelecionada = this.dataset.value;
                questaoRespondida = true;
                
                // Desabilita todas as opções
                opcoes.forEach(op => {
                    op.style.cursor = 'default';
                    op.classList.remove('selecionada');
                });
                
                // Marca a opção selecionada
                this.classList.add('selecionada');
                
                // Verifica se acertou
                const acertou = respostaSelecionada === respostaCorreta;
                
                // Atualiza contador de acertos
                if (acertou) {
                    acertosAtuais++;
                    document.querySelector('.contador-acertos').textContent = acertosAtuais;
                    
                    // Remove da lista de erradas se estiver lá (em caso de revisão)
                    if (modoRevisao) {
                        // Envia requisição para remover das questões erradas
                        fetch('salvar_errada.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `questao_id=${questaoId}&action=remove`
                        });
                    }
                } else {
                    // Adiciona à lista de questões erradas
                    fetch('salvar_errada.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `questao_id=${questaoId}&action=add`
                    });
                }
                
                // Destaca as opções corretas/incorretas
                opcoes.forEach(op => {
                    if (op.dataset.value === respostaCorreta) {
                        op.classList.add('correta');
                    } else if (op.dataset.value === respostaSelecionada && !acertou) {
                        op.classList.add('incorreta');
                    }
                });
                
                // Prepara mensagem de feedback
                let mensagem = '';
                if (acertou) {
                    mensagem = `✅ <strong>Correto!</strong> Você acertou. A resposta "${respostaCorreta}" está certa.`;
                    feedback.classList.add('acerto');
                } else {
                    mensagem = `❌ <strong>Incorreto.</strong> A resposta correta é "${respostaCorreta}".`;
                    feedback.classList.add('erro');
                }
                
                // Exibe feedback
                feedbackMensagem.innerHTML = mensagem;
                feedbackExplicacao.innerHTML = `<strong>Explicação:</strong> ${explicacao}`;
                feedback.classList.add('mostrar');
                
                // Mostra botão de avançar
                btnAvancar.style.display = 'block';
                
                // Rola a tela para o feedback
                feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        // Configura botão de avançar
        btnAvancar.addEventListener('click', function() {
            <?php if ($dados['proxima_id']): ?>
                // Avança para próxima questão
                const url = `index.php?id=<?php echo $dados['proxima_id']; ?>&acertos=${acertosAtuais}<?php echo $dados['modo_revisao'] ? '&modo_revisao=1' : ''; ?>`;
                window.location.href = url;
            <?php else: ?>
                // Vai para tela de resultados
                const url = `fim_quiz.php?acertos=${acertosAtuais}&total=<?php echo $dados['total_perguntas']; ?><?php echo $dados['modo_revisao'] ? '&modo_revisao=1' : ''; ?>`;
                window.location.href = url;
            <?php endif; ?>
        });

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            if (questaoRespondida) {
                // Tecla Enter para avançar
                if (e.key === 'Enter' && btnAvancar.style.display !== 'none') {
                    btnAvancar.click();
                }
            } else {
                // Teclas 1-4 para selecionar opções
                if (e.key >= '1' && e.key <= '4') {
                    const index = parseInt(e.key) - 1;
                    if (opcoes[index]) {
                        opcoes[index].click();
                    }
                }
            }
        });

        // Efeitos visuais nas opções
        opcoes.forEach((opcao, index) => {
            // Efeito hover
            opcao.addEventListener('mouseenter', function() {
                if (!questaoRespondida) {
                    this.style.transform = 'translateX(5px)';
                }
            });
            
            opcao.addEventListener('mouseleave', function() {
                if (!questaoRespondida) {
                    this.style.transform = 'translateX(0)';
                }
            });
        });

        // Dica de atalhos
        setTimeout(() => {
            if (!localStorage.getItem('atalhosMostrados')) {
                alert('💡 Dica: Use as teclas 1-4 para selecionar respostas rapidamente!');
                localStorage.setItem('atalhosMostrados', 'true');
            }
        }, 1000);
    </script>
</body>
</html>