<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
            if ($dados['modo_revisao']) echo '📚 Revisão de Erradas - '; 
            elseif (isset($dados['modo_reforco']) && $dados['modo_reforco']) echo '🎯 Reforço de Aprendizado - ';
            else echo '🎓 Quiz Interativo - '; 
        ?>Inútil.App
    </title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-quiz">
    <div id="toast-container" class="toast-container"></div>
    <div class="container-quiz">
        
        <div class="header-quiz" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="text-align: left;">
                <h1 style="margin-bottom: 5px; text-align: left;">
                    <?php if ($dados['modo_revisao']): ?>
                        📚 Revisão
                    <?php elseif (isset($dados['modo_reforco']) && $dados['modo_reforco']): ?>
                        🎯 Reforço
                    <?php else: ?>
                        🎓 Quiz
                    <?php endif; ?>
                </h1>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Logado como <strong><?php echo h($_SESSION['username']); ?></strong></p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="salvarProgressoManual()" class="btn btn-primary btn-small" id="btnSalvarState" <?php echo (isset($dados['modo_reforco']) && $dados['modo_reforco']) ? 'style="display:none;"' : ''; ?>>
                    💾 Salvar Estado
                </button>
                <a href="index.php?acao=home" class="btn btn-secondary btn-small">🏠 Início</a>
                <a href="index.php?acao=logout" class="btn btn-secondary btn-small">🚪 Sair</a>
            </div>
        </div>
        <?php if ($dados['modo_revisao']): ?>
            <div class="modo-revisao" style="margin-top: 10px;">MODO REVISÃO</div>
        <?php elseif (isset($dados['modo_reforco']) && $dados['modo_reforco']): ?>
            <div class="modo-revisao" style="margin-top: 10px; background-color: var(--primary);">MODO REFORÇO</div>
        <?php endif; ?>

        <div class="content-quiz">
            <?php if ($dados['modo_revisao']): ?>
                <div class="info-revisao-quiz">
                    <strong>📖 Modo Revisão:</strong> Você está revisando <?php echo h($dados['total_erradas']); ?> questão(ões) que errou anteriormente.
                    <a href="index.php?acao=limpar_revisao" style="color: var(--warning-color); margin-left: 10px;">🔄 Limpar Histórico</a>
                </div>
            <?php endif; ?>
            
            <div class="progresso-quiz">
                <div class="progresso-info">
                    <span>Questão <?php echo h($dados['numero_questao']); ?> de <?php echo h($dados['total_perguntas']); ?></span>
                    <span>
                        Acertos: <span class="contador-acertos"><?php echo h($dados['acertos_total']); ?></span>
                        <?php if (!$dados['modo_revisao']): ?>
                            / Erradas: <span class="contador-erradas"><?php echo h($dados['total_erradas']); ?></span>
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
                
                <!-- Nova Área de Interação -->
                <div class="interacao-container" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border);">
                    <div style="font-size: 0.9rem; font-weight: 600; margin-bottom: 10px; color: var(--text-main);">
                        💬 Dúvida ou erro nessa questão?
                    </div>
                    <textarea id="comentarioQuestao" placeholder="Deixe um comentário ou reporte um erro..." 
                              style="width: 100%; height: 60px; padding: 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem;"></textarea>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer; color: var(--danger);">
                            <input type="checkbox" id="sinalizarErro"> 🚩 Sinalizar Erro
                        </label>
                        <button class="btn btn-small" onclick="enviarInteracao()" id="btnEnviarInteracao">
                            Enviar Feedback
                        </button>
                    </div>
                    <div id="interacaoAviso" style="font-size: 0.75rem; margin-top: 5px; display: none;"></div>
                </div>

                <!-- Nova Área da Comunidade -->
                <div class="comunidade-wrapper">
                    <div class="comunidade-header">
                        <div class="comunidade-title">
                            🤝 Notas da Comunidade
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Ver notas de outros</span>
                            <label class="switch">
                                <input type="checkbox" id="toggleComunidade" onchange="toggleComunidade()">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="listaComentarios" class="comentarios-lista" style="display: none;">
                        <div class="empty-comments">Carregando comentários...</div>
                    </div>
                </div>
            </div>

            <div class="questao-header">
                <div class="questao-info">
                    <span class="badge-quiz">ID: <?php echo $dados['questao']['id']; ?></span>
                    <span class="badge-quiz topico"><?php echo h($dados['questao']['topico']); ?></span>
                    <span class="badge-quiz nivel"><?php echo h($dados['questao']['nivel']); ?></span>
                    <?php if (in_array($dados['questao']['id'], $_SESSION['questoes_erradas'])): ?>
                        <span class="badge-quiz errada">❌ Errada Anteriormente</span>
                    <?php endif; ?>
                </div>
                <div class="questao-numero">#<?php echo h($dados['numero_questao']); ?></div>
            </div>

            <div class="pergunta"><?php echo $dados['questao']['pergunta']; ?></div>

            <div class="opcoes-container">
            <?php foreach ($dados['questao']['opcoes_disponiveis'] as $index => $opcao): ?>
                <div class="opcao-label" data-value="<?php echo h($opcao); ?>">
                    <div class="numero-opcao"><?php echo $index + 1; ?></div>
                    <?php echo $opcao; // Opção já convertida para HTML ?>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Botão de avançar (inicialmente oculto) -->
            <button class="btn-quiz proxima-pergunta-btn" id="btnAvancar" style="display: none;">
                <?php if ($dados['proxima_id']): ?>
                    Próxima Questão ➡️
                <?php else: ?>
                    🏁 <?php 
                        if ($dados['modo_revisao']) echo 'Finalizar Revisão';
                        elseif (isset($dados['modo_reforco']) && $dados['modo_reforco']) echo 'Finalizar Reforço';
                        else echo 'Ver Resultado Final';
                    ?>
                <?php endif; ?>
            </button>

            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <div class="admin-panel">
                <strong>🔧 Painel de Controle</strong>
                <div class="admin-links">
                    <a href="admin.php">⚙️ Gerenciar Dados</a>
                    <a href="javascript:void(0)" onclick="recarregarPagina()">🔄 Recarregar</a>
                    <a href="javascript:void(0)" onclick="copiarQuestao()">📋 Copiar Questão</a>
                    <?php if (!$dados['modo_revisao'] && $dados['total_erradas'] > 0): ?>
                        <a href="index.php?acao=revisar_erradas">📚 Revisar Erradas (<?php echo $dados['total_erradas']; ?>)</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Variáveis globais
        const respostaCorreta = <?php echo json_encode($dados['resposta_correta']); ?>;
        const explicacao = <?php echo json_encode($dados['explicacao'] ?? ''); ?>;
        const questaoId = <?php echo json_encode($dados['questao']['id']); ?>;
        const csrfToken = "<?php echo gerarTokenCSRF(); ?>";
        let acertosAtuais = <?php echo intval($dados['acertos_total'] ?? 0); ?>;
        let questaoRespondida = false;
        const modoRevisao = <?php echo $dados['modo_revisao'] ? 'true' : 'false'; ?>;
        const modoReforco = <?php echo (isset($dados['modo_reforco']) && $dados['modo_reforco']) ? 'true' : 'false'; ?>;

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
                    
                    // Remove da lista de erradas se estiver lá (em caso de revisão ou reforço)
                    if (modoRevisao || modoReforco) {
                        // Envia requisição para remover das questões erradas
                        fetch('salvar_errada.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `questao_id=${questaoId}&action=remove&csrf_token=${csrfToken}`
                        });
                    }
                } else {
                    // Adiciona à lista de questões erradas
                    fetch('salvar_errada.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `questao_id=${questaoId}&action=add&csrf_token=${csrfToken}`
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
                const url = `index.php?id=<?php echo $dados['proxima_id']; ?>&acertos=${acertosAtuais}${modoRevisao ? '&modo_revisao=1' : ''}${modoReforco ? '&acao=quiz_erros' : ''}`;
                window.location.href = url;
            } else {
                // Vai para tela de resultados
                const url = `fim_quiz.php?acertos=${acertosAtuais}&total=${totalQuestoes}${modoRevisao ? '&modo_revisao=1' : ''}${modoReforco ? '&modo_reforco=1' : ''}`;
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
                showToast('💡 Dica: Use as teclas 1-4 para selecionar respostas rapidamente!', 'info');
                localStorage.setItem('atalhosMostrados', 'true');
            }
        }, 1000);

        function recarregarPagina() {
            // Mantém todos os parâmetros atuais da URL
            const urlParams = new URLSearchParams(window.location.search);
            window.location.href = 'index.php?' + urlParams.toString();
        }

        function enviarInteracao() {
            const comment = document.getElementById('comentarioQuestao').value;
            const is_flagged = document.getElementById('sinalizarErro').checked ? 1 : 0;
            const aviso = document.getElementById('interacaoAviso');
            const btn = document.getElementById('btnEnviarInteracao');

            if (!comment && !is_flagged) return;

            btn.disabled = true;
            btn.textContent = 'Enviando...';

            const formData = new FormData();
            formData.append('question_id', questaoId);
            formData.append('comment', comment);
            formData.append('is_flagged', is_flagged);
            formData.append('csrf_token', csrfToken);

            fetch('salvar_interacao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    aviso.style.display = 'block';
                    aviso.style.color = 'var(--success)';
                    aviso.textContent = '✅ Feedback enviado com sucesso!';
                    btn.textContent = 'Enviado';
                } else {
                    aviso.style.display = 'block';
                    aviso.style.color = 'var(--danger)';
                    aviso.textContent = '❌ Erro: ' + data.message;
                    btn.disabled = false;
                    btn.textContent = 'Enviar Feedback';
                }
            })
            .catch(error => {
                aviso.style.display = 'block';
                aviso.style.color = 'var(--danger)';
                aviso.textContent = '❌ Erro de conexão.';
                btn.disabled = false;
                btn.textContent = 'Enviar Feedback';
            });
        }

        function toggleComunidade() {
            const toggle = document.getElementById('toggleComunidade');
            const lista = document.getElementById('listaComentarios');
            
            if (toggle.checked) {
                lista.style.display = 'flex';
                carregarComentarios();
                localStorage.setItem('showCommunityComments', 'true');
            } else {
                lista.style.display = 'none';
                localStorage.setItem('showCommunityComments', 'false');
            }
        }

        function carregarComentarios() {
            const lista = document.getElementById('listaComentarios');
            
            fetch(`api.php?action=get_comments&question_id=${questaoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderizarComentarios(data.comments);
                    } else {
                        lista.innerHTML = `<div class="empty-comments">❌ Erro ao carregar: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    lista.innerHTML = '<div class="empty-comments">❌ Erro de conexão ao buscar notas.</div>';
                });
        }

        function renderizarComentarios(comments) {
            const lista = document.getElementById('listaComentarios');
            if (comments.length === 0) {
                lista.innerHTML = '<div class="empty-comments">Nenhuma nota compartilhada para esta questão ainda.</div>';
                return;
            }

            lista.innerHTML = '';
            comments.forEach(c => {
                const item = document.createElement('div');
                item.className = 'comentario-item';
                
                const dataFormatada = new Date(c.created_at).toLocaleDateString('pt-BR', {
                    day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'
                });

                item.innerHTML = `
                    <div class="comentario-votos">
                        <button class="btn-vote" onclick="votarComentario(${c.id}, this)" title="Útil!">
                            ▲
                        </button>
                        <span class="votos-count">${c.total_votes}</span>
                    </div>
                    <div class="comentario-corpo">
                        <div class="comentario-meta">
                            <span class="comentario-user">@${c.username}</span>
                            <span>${dataFormatada}</span>
                        </div>
                        <div class="comentario-texto">${c.comment}</div>
                    </div>
                `;
                lista.appendChild(item);
            });
        }

        function votarComentario(commentId, btn) {
            if (btn.classList.contains('voted')) return;

            const formData = new FormData();
            formData.append('comment_id', commentId);
            formData.append('csrf_token', csrfToken);

            fetch('api.php?action=vote_comment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.classList.add('voted');
                    const countSpan = btn.nextElementSibling;
                    countSpan.textContent = data.votes;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao votar:', error);
            });
        }

        // Recupera preferência da comunidade
        const pref = localStorage.getItem('showCommunityComments');
        if (pref === 'true') {
            document.getElementById('toggleComunidade').checked = true;
            toggleComunidade();
        }

        function salvarProgressoManual() {
            const btn = document.getElementById('btnSalvarState');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '💾 Salvando...';
            
            const formData = new FormData();
            formData.append('quiz_id', "<?php echo $dados['questao']['quiz_id'] ?? 1; ?>");
            formData.append('question_id', questaoId);
            formData.append('acertos', acertosAtuais);
            formData.append('erradas', JSON.stringify(<?php echo json_encode($_SESSION['questoes_erradas'] ?? []); ?>)); // Usa da sessão PHP pois JS não rastreia totalmente
            formData.append('csrf_token', csrfToken);
            
            fetch('api.php?action=save_progress', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '✅ Salvo!';
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-primary');
                    }, 2000);
                } else {
                    showToast('Erro ao salvar: ' + data.message, 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                showToast('Erro de conexão.', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function copiarQuestao() {
            const q = <?php echo json_encode($dados['questao']); ?>;
            const n = <?php echo json_encode($dados['numero_questao']); ?>;
            const resp = <?php echo json_encode($dados['resposta_correta']); ?>;
            const expl = <?php echo json_encode($dados['explicacao'] ?? ''); ?>;
            
            // Função simples para remover tags HTML
            const stripHtml = (html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                return doc.body.textContent || "";
            };

            let texto = `📌 Questão #${n} (ID: ${q.id})\n`;
            // texto += `📂 Tópico: ${q.topico} | Nível: ${q.nivel}\n\n`;
            texto += `❓ PERGUNTA:\n${stripHtml(q.pergunta)}\n\n`;
            
            texto += `📝 OPÇÕES:\n`;
            q.opcoes_disponiveis.forEach((opt, i) => {
                texto += `${i + 1}. ${stripHtml(opt)}\n`;
            });
            
            // texto += `\n✅ RESPOSTA CORRETA: ${stripHtml(resp)}\n`;
            // texto += `\n📖 EXPLICAÇÃO:\n${stripHtml(expl)}`;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(texto).then(() => {
                    showToast('✅ Questão copiada para a área de transferência!', 'success');
                }).catch(err => {
                    console.error('Erro ao copiar:', err);
                    showToast('❌ Falha ao copiar a questão.', 'error');
                });
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = texto;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast('✅ Questão copiada para a área de transferência!', 'success');
                } catch (err) {
                    console.error('Erro ao copiar (fallback):', err);
                    showToast('❌ Falha ao copiar a questão.', 'error');
                }
                document.body.removeChild(textArea);
            }
        }

        // Função para exibir Toast Notifications
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            
            // Criar elemento toast
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Ícone baseado no tipo
            let icon = 'ℹ️';
            if (type === 'success') icon = '✅';
            if (type === 'error') icon = '❌';
            if (type === 'warning') icon = '⚠️';
            
            toast.innerHTML = `
                <span class="toast-icon">${icon}</span>
                <span class="toast-message">${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Mostrar toast (pequeno delay para permitir transição)
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });
            
            // Remover após 3 segundos
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300); // Tempo da transição CSS
            }, 3000);
        }
    </script>
</body>
</html>