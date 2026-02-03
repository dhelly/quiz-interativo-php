<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $dados['modo_revisao'] ? '📚 Revisão de Erradas - ' : '🎓 Quiz Interativo - '; ?>Inútil.App
    </title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-quiz">
    <div class="container-quiz">
        
        <div class="header-quiz">
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

        <div class="content-quiz">
            <?php if ($dados['modo_revisao']): ?>
                <div class="info-revisao-quiz">
                    <strong>📖 Modo Revisão:</strong> Você está revisando <?php echo $dados['total_erradas']; ?> questão(ões) que errou anteriormente.
                    <a href="index.php?acao=limpar_revisao" style="color: var(--warning-color); margin-left: 10px;">🔄 Limpar Histórico</a>
                </div>
            <?php endif; ?>
            
            <div class="progresso-quiz">
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
                    <span class="badge-quiz">ID: <?php echo $dados['questao']['id']; ?></span>
                    <span class="badge-quiz topico"><?php echo $dados['questao']['topico']; ?></span>
                    <span class="badge-quiz nivel"><?php echo $dados['questao']['nivel']; ?></span>
                    <?php if (in_array($dados['questao']['id'], $_SESSION['questoes_erradas'])): ?>
                        <span class="badge-quiz errada">❌ Errada Anteriormente</span>
                    <?php endif; ?>
                </div>
                <div class="questao-numero">#<?php echo $dados['numero_questao']; ?></div>
            </div>

            <div class="pergunta"><?php echo $dados['questao']['pergunta']; ?></div>

            <div class="opcoes-container">
            <?php foreach ($dados['questao']['opcoes_disponiveis'] as $index => $opcao): ?>
                <label class="opcao-label" data-value="<?php echo htmlspecialchars($opcao); ?>">
                    <div class="numero-opcao"><?php echo $index + 1; ?></div>
                    <?php echo $opcao; ?>
                </label>
            <?php endforeach; ?>
            </div>

            <!-- Botão de avançar (inicialmente oculto) -->
            <button class="btn-quiz proxima-pergunta-btn" id="btnAvancar" style="display: none;">
                <?php if ($dados['proxima_id']): ?>
                    Próxima Questão ➡️
                <?php else: ?>
                    🏁 <?php echo $dados['modo_revisao'] ? 'Finalizar Revisão' : 'Ver Resultado Final'; ?>
                <?php endif; ?>
            </button>

            <div class="admin-panel">
                <strong>🔧 Painel de Controle</strong>
                <div class="admin-links">
                    <a href="admin.php">⚙️ Gerenciar Dados</a>
                    <a href="javascript:void(0)" onclick="recarregarPagina()">🔄 Recarregar</a>
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

        // Configura botão de avançar - VERSÃO CORRIGIDA
        btnAvancar.addEventListener('click', function() {
            const totalQuestoes = <?php echo $dados['total_perguntas']; ?>;
            const questaoAtual = <?php echo $dados['numero_questao']; ?>;
            const isUltimaQuestao = questaoAtual >= totalQuestoes;
            
            if (!isUltimaQuestao && <?php echo $dados['proxima_id'] ? 'true' : 'false'; ?>) {
                // Avança para próxima questão
                const url = `index.php?id=<?php echo $dados['proxima_id']; ?>&acertos=${acertosAtuais}<?php echo $dados['modo_revisao'] ? '&modo_revisao=1' : ''; ?>`;
                window.location.href = url;
            } else {
                // Vai para tela de resultados
                const url = `fim_quiz.php?acertos=${acertosAtuais}&total=${totalQuestoes}<?php echo $dados['modo_revisao'] ? '&modo_revisao=1' : ''; ?>`;
                window.location.href = url;
            }
        });

        // Atalhos de teclado - CORREÇÃO DEFINITIVA
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

        function recarregarPagina() {
            // Mantém todos os parâmetros atuais da URL
            const urlParams = new URLSearchParams(window.location.search);
            window.location.href = 'index.php?' + urlParams.toString();
        }
    </script>
</body>
</html>