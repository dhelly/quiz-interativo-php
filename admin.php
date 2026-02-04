<?php
// Configuração de Sessão Segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();
require_once 'carregar_dados.php';
require_once 'validar_quiz.php';

// Acesso restrito a administradores
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

$acao = $_GET['acao'] ?? 'panel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validarTokenCSRF($csrf_token)) {
        die('Erro CSRF: Requisição inválida.');
    }
}

$quiz_data = carregarDadosQuiz();

switch ($acao) {
    case 'panel':
        exibirPainelAdmin($quiz_data);
        break;
    case 'salvar-json':
        salvarJson();
        break;
    case 'upload-json':
        uploadJson();
        break;
    case 'download-json':
        downloadJson($quiz_data);
        break;
    case 'download-jsonl':
        downloadJsonL($quiz_data);
        break;
    case 'reset-padrao':
        resetParaPadrao();
        break;
    case 'salvar-como':
        salvarQuizComoAdmin();
        break;
    case 'carregar-quiz':
        carregarQuizAdmin();
        break;
    case 'excluir-quiz':
        excluirQuizAdmin();
        break;
    case 'download-template':
            downloadTemplate();
            break;
    case 'download-quiz':
        downloadQuiz();
        break;
    case 'toggle-visibility':
        toggleVisibilityAdmin();
        break;
    default:
        exibirPainelAdmin($quiz_data);
        break;
}

function exibirPainelAdmin($quiz_data) {
    $quizzes_salvos = listarQuizzes();
    $disciplinas = obterDisciplinas();

    // Converter dados para markdown para o editor
    $dados_para_editor = $quiz_data; // Manter originais (já em markdown)
    
    $dados = [
        'total_questoes' => count($quiz_data),
        'arquivo_atual' => 'quiz_data.json',
        'questoes' => $quiz_data,
        'json_atual' => json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        'quizzes_salvos' => $quizzes_salvos,
        'disciplinas' => $disciplinas,
        'interacoes' => obterInteracoes()
    ];
    
    // Template HTML
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Painel Admin - Inútil App</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⚙️ Painel de Administração</h1>
                <p>Inútil.App - Gerenciamento de Quiz Interativo</p>
            </div>

            <!-- Alertas fixos para mensagens do PHP -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-fixed" style="display: block;">
                    <?php echo h($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-error alert-fixed" style="display: block;">
                    <?php echo h($_GET['erro']); ?>
                </div>
            <?php endif; ?>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dados['total_questoes']; ?></div>
                    <div>Questões Atuais</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($dados['quizzes_salvos']); ?></div>
                    <div>Quizzes Salvos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($dados['disciplinas']); ?></div>
                    <div>Disciplinas</div>
                </div>
            </div>

            <div class="card">
                <div class="tabs">
                    <div class="tab active" onclick="showTab('editor')">📝 Editor JSON</div>
                    <div class="tab" onclick="showTab('upload')">📁 Upload</div>
                    <div class="tab" onclick="showTab('quizzes')">📚 Quizzes</div>
                    <div class="tab" onclick="showTab('questoes')">📊 Questões</div>
                    <div class="tab" onclick="showTab('moderacao')">👮 Moderação</div>
                </div>

                <!-- Alertas dinâmicos para JavaScript -->
                <div id="alertSuccess" class="alert alert-success" style="display: none;"></div>
                <div id="alertError" class="alert alert-error" style="display: none;"></div>

                <!-- Tab 1: Editor JSON -->
                <div id="editor" class="tab-content active">
                    <h3>Editor de JSON</h3>
                    <p>Edite diretamente o JSON das questões abaixo:</p>
                    
                    <div class="two-columns">
                        <div class="column">
                            <textarea id="jsonEditor" placeholder="Cole seu JSON aqui..."><?php echo h($dados['json_atual']); ?></textarea>
                        </div>
                        <div class="column">
                            <div class="action-panel">
                                <div class="action-title">📋 Ações do Editor</div>
                                <div class="btn-group">
                                    <button class="btn btn-success" onclick="saveJson()">
                                        💾 Salvar JSON
                                    </button>
                                    <input type="hidden" id="admin_csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                                    <button class="btn" onclick="loadCurrentJson()">
                                        🔄 Carregar JSON Atual
                                    </button>
                                    <button class="btn btn-warning" onclick="formatJson()">
                                        ✨ Format JSON
                                    </button>
                                </div>
                                
                                <div class="action-title" style="margin-top: 25px;">⚡ Ações Rápidas</div>
                                <div class="btn-group">
                                    <div class="btn-row">
                                        <button class="btn btn-secondary" onclick="downloadCurrentJson()">
                                            📥 Download JSON
                                        </button>
                                        <button class="btn btn-secondary" onclick="downloadCurrentJsonL()">
                                            📥 Download JSONL
                                        </button>
                                    </div>
                                    <div class="btn-row" style="margin-top: 5px;">
                                        <button class="btn btn-error" onclick="resetToDefault()">
                                            🔄 Restaurar
                                        </button>
                                        <a href="index.php" class="btn">
                                            🎮 Voltar ao Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Upload de Arquivo -->
                <div id="upload" class="tab-content">
                    <h3>Upload de Arquivo JSON</h3>
                    <p>Faça upload de um arquivo JSON com as questões:</p>
                    
                    <div class="two-columns">
                        <div class="column">
                            <div class="upload-area" id="uploadArea">
                                <p>📁 Arraste e solte um arquivo JSON aqui ou</p>
                                <input type="file" id="fileInput" accept=".json" style="display: none;">
                                <button type="button" class="btn" onclick="document.getElementById('fileInput').click()">
                                    Selecione um Arquivo
                                </button>
                            </div>

                            <div id="fileInfo" style="display: none; margin-top: 15px; padding: 15px; background: var(--secondary-color); border-radius: 6px; border: 1px solid var(--border-color);">
                                <strong>Arquivo selecionado:</strong> <span id="fileName"></span>
                            </div>

                            <button type="button" class="btn btn-success" onclick="uploadFile()" style="margin-top: 15px;">
                                ⬆️ Fazer Upload
                            </button>
                            <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                        </div>
                        
                        <div class="column">
                            <div class="action-panel">
                                <div class="action-title">💡 Informações</div>
                                <p><strong>Formatos esperados:</strong></p>
                                <p><strong>1. JSON (Array):</strong></p>
                                <pre style="font-size: 0.8em; max-height: 150px; overflow: auto;">
[
  {
    "id": 1,
    "pergunta": "Texto...",
    "resposta_correta": "Opção...",
    "opcoes_disponiveis": ["A", "B"],
    "explicacao_feedback": "...",
    "topico": "...",
    "nivel": "..."
  }
]</pre>
                                <p><strong>2. JSONL (JSON Lines):</strong></p>
                                <pre style="font-size: 0.8em; max-height: 150px; overflow: auto;">
{"id": 1, "pergunta": "...", "resposta_correta": "...", ...}
{"id": 2, "pergunta": "...", "resposta_correta": "...", ...}</pre>
                                <div class="btn-group" style="margin-top: 15px;">
                                    <a href="admin.php?acao=download-template" class="btn btn-secondary" target="_blank">
                                        📥 Baixar Modelo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOVA TAB: Quizzes Salvos -->
                <div id="quizzes" class="tab-content">
                    <h3>📚 Quizzes Salvos</h3>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <p>Gerencie seus quizzes salvos por disciplina</p>
                        <button class="btn btn-success" onclick="abrirModalSalvarComo()">
                            💾 Salvar Quiz Atual
                        </button>
                    </div>

                    <?php if (empty($dados['quizzes_salvos'])): ?>
                        <div class="empty-state">
                            <h4>📭 Nenhum quiz salvo</h4>
                            <p>Salve seu primeiro quiz usando o botão acima!</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $quizzes_por_disciplina = [];
                        foreach ($dados['quizzes_salvos'] as $quiz) {
                            $disciplina = $quiz['disciplina'];
                            if (!isset($quizzes_por_disciplina[$disciplina])) {
                                $quizzes_por_disciplina[$disciplina] = [];
                            }
                            $quizzes_por_disciplina[$disciplina][] = $quiz;
                        }
                        ?>
                        
                        <?php foreach ($quizzes_por_disciplina as $disciplina => $quizzes): ?>
                            <div class="disciplina-section">
                                <div class="disciplina-header">
                                    📁 <?php echo ucfirst($disciplina); ?> (<?php echo count($quizzes); ?> quizzes)
                                </div>
                                <div class="grid-quizzes">
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <div class="quiz-card">
                                            <div class="quiz-header">
                                                <h4 class="quiz-title"><?php echo $quiz['nome']; ?></h4>
                                                <span class="quiz-disciplina"><?php echo $quiz['disciplina']; ?></span>
                                            </div>
                                            
                                            <div class="quiz-info">
                                                <div><strong>Questões:</strong> <?php echo $quiz['total_questoes']; ?></div>
                                                <div><strong>Modificado:</strong> <?php echo date('d/m/Y H:i', $quiz['data_modificacao']); ?></div>
                                                <div><strong>Tamanho:</strong> <?php echo round($quiz['tamanho'] / 1024, 2); ?> KB</div>
                                            </div>
                                            
                                            <div class="quiz-stats">
                                                <?php foreach ($quiz['topicos'] as $topico => $quantidade): ?>
                                                    <span class="stat-badge"><?php echo $topico . ': ' . $quantidade; ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="quiz-stats">
                                                <?php foreach ($quiz['niveis'] as $nivel => $quantidade): ?>
                                                    <span class="stat-badge"><?php echo $nivel . ': ' . $quantidade; ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <div class="quiz-actions">
                                                <button class="btn btn-success btn-small" 
                                                        onclick="carregarQuiz('<?php echo urlencode($quiz['caminho']); ?>')">
                                                    🎯 Carregar
                                                </button>
                                                <button class="btn btn-small" 
                                                        onclick="downloadQuiz('<?php echo urlencode($quiz['caminho']); ?>', '<?php echo $quiz['nome']; ?>')">
                                                    📥 Download
                                                </button>
                                                <button class="btn btn-error btn-small" 
                                                        onclick="excluirQuiz('<?php echo urlencode($quiz['caminho']); ?>')">
                                                    🗑️ Excluir
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Tab 4: Visualizar Questões -->
                <div id="questoes" class="tab-content">
                    <h3>Questões Carregadas (<?php echo $dados['total_questoes']; ?>)</h3>
                    
                    <div class="two-columns">
                        <div class="column">
                            <div class="questoes-list">
                                <?php foreach ($dados['questoes'] as $questao): ?>
                                <div class="questao-item">
                                    <strong>#<?php echo $questao['id']; ?> - <?php echo $questao['topico']; ?></strong>
                                    <p style="margin: 8px 0; font-size: 0.9em;"><?php echo substr($questao['pergunta'], 0, 100); ?>...</p>
                                    <div style="font-size: 0.8em; color: var(--text-muted);">
                                        <span class="badge" style="background: var(--success-color);">
                                            <?php echo $questao['nivel']; ?>
                                        </span>
                                        Resposta: <?php echo $questao['resposta_correta']; ?>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px;">
                                        <div class="admin-opcoes-wrapper">
                                            <?php 
                                            if (isset($questao['opcoes_disponiveis']) && is_array($questao['opcoes_disponiveis'])) {
                                                foreach ($questao['opcoes_disponiveis'] as $opcao) {
                                                    $is_correta = ($opcao === $questao['resposta_correta']);
                                                    echo '<span class="admin-opcao-badge' . ($is_correta ? ' correta' : '') . '">' . htmlspecialchars($opcao) . '</span>';
                                                }
                                            } else {
                                                echo '<span class="admin-opcao-badge">N/A</span>';
                                            }
                                            ?>
                                        </div>
                                        <button class="btn btn-small <?php echo $questao['is_visible'] ? 'btn-secondary' : 'btn-success'; ?>" 
                                                onclick="toggleVisibility(<?php echo $questao['id']; ?>, <?php echo $questao['is_visible'] ? 0 : 1; ?>)">
                                            <?php echo $questao['is_visible'] ? '👁️ Ocultar' : '👁️‍🗨️ Mostrar'; ?>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="column">
                            <div class="action-panel">
                                <div class="action-title">📊 Estatísticas</div>
                                <div style="margin-bottom: 15px;">
                                    <strong>Total de Questões:</strong> <?php echo $dados['total_questoes']; ?>
                                </div>
                                
                                <div class="action-title" style="margin-top: 25px;">🔧 Ferramentas</div>
                                <div class="btn-group">
                                    <button class="btn" onclick="showTab('editor')">
                                        📝 Editar JSON
                                    </button>
                                    <button class="btn btn-success" onclick="loadCurrentJson()">
                                        🔄 Recarregar Dados
                                    </button>
                                    <a href="index.php" class="btn">
                                        🎮 Testar Quiz
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Salvar Como -->
        <div id="modalSalvarComo" class="modal">
            <div class="modal-content">
                <h3>💾 Salvar Quiz</h3>
                <form id="formSalvarComo">
                    <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                    <div class="form-group">
                        <label for="nome_quiz">Nome do Quiz:</label>
                        <input type="text" id="nome_quiz" name="nome_quiz" required 
                               placeholder="Ex: Simulado OAB 2024">
                    </div>
                    
                    <div class="form-group">
                        <label for="disciplina">Disciplina:</label>
                        <select id="disciplina" name="disciplina">
                            <option value="geral">Geral</option>
                            <?php foreach ($dados['disciplinas'] as $disciplina): ?>
                                <option value="<?php echo $disciplina; ?>"><?php echo ucfirst($disciplina); ?></option>
                            <?php endforeach; ?>
                            <option value="nova">Nova Disciplina...</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="novaDisciplinaGroup" style="display: none;">
                        <label for="nova_disciplina">Nome da Nova Disciplina:</label>
                        <input type="text" id="nova_disciplina" name="nova_disciplina" 
                               placeholder="Ex: direito-constitucional">
                    </div>
                    
                    <div class="form-group">
                        <label>Resumo do Quiz:</label>
                        <div style="background: var(--secondary-color); padding: 10px; border-radius: 4px;">
                            <strong>Questões:</strong> <?php echo $dados['total_questoes']; ?><br>
                            <strong>Disciplinas:</strong> 
                            <?php
                            $topicos = [];
                            foreach ($dados['questoes'] as $questao) {
                                $topico = $questao['topico'];
                                $topicos[$topico] = ($topicos[$topico] ?? 0) + 1;
                            }
                            echo implode(', ', array_keys($topicos));
                            ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success">💾 Salvar</button>
                        <button type="button" class="btn btn-secondary" onclick="fecharModalSalvarComo()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // ========== FUNÇÕES PRINCIPAIS ==========
            
            // Sistema de tabs - DEFINIDA PRIMEIRO
            function showTab(tabName) {
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                if (event) {
                    event.currentTarget.classList.add('active');
                }
                document.getElementById(tabName).classList.add('active');
            }

            function toggleVisibility(id, visible) {
                if (confirm('Deseja ' + (visible ? 'mostrar' : 'ocultar') + ' esta questão?')) {
                    window.location.href = 'admin.php?acao=toggle-visibility&id=' + id + '&visible=' + visible;
                }
            }

            // Alertas
            function showAlert(message, type) {
                const alertDiv = type === 'success' ? 
                    document.getElementById('alertSuccess') : 
                    document.getElementById('alertError');
                
                alertDiv.textContent = message;
                alertDiv.style.display = 'block';
                
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }

            // ========== EDITOR JSON ==========
            function loadCurrentJson() {
                // Recarrega a página para obter os dados mais recentes
                location.reload();
            }

            async function saveJson() {
                // Validação no lado do cliente primeiro
                if (!validarJsonAntesEnvio()) {
                    return;
                }
                
                const jsonData = document.getElementById('jsonEditor').value;
                
                try {
                    // Parse e validação adicional
                    const dados = JSON.parse(jsonData);
                    
                    // Validação mais detalhada no cliente
                    const erros = [];
                    
                    if (!Array.isArray(dados)) {
                        erros.push('O JSON deve ser um array de questões');
                    } else if (dados.length === 0) {
                        erros.push('O array de questões não pode estar vazio');
                    } else {
                        // Valida cada questão individualmente
                        dados.forEach((questao, index) => {
                            const numeroQuestao = index + 1;
                            
                            // Verifica campos obrigatórios
                            const camposObrigatorios = ['id', 'pergunta', 'resposta_correta', 'opcoes_disponiveis', 'explicacao_feedback', 'topico', 'nivel'];
                            camposObrigatorios.forEach(campo => {
                                if (!questao.hasOwnProperty(campo)) {
                                    erros.push(`Questão ${numeroQuestao}: Campo obrigatório '${campo}' não encontrado`);
                                }
                            });
                            
                            // Valida tipos
                            if (questao.id && typeof questao.id !== 'number') {
                                erros.push(`Questão ${numeroQuestao}: ID deve ser um número`);
                            }
                            
                            if (questao.pergunta && (typeof questao.pergunta !== 'string' || questao.pergunta.trim().length < 10)) {
                                erros.push(`Questão ${numeroQuestao}: Pergunta deve ter pelo menos 10 caracteres`);
                            }
                            
                            if (questao.resposta_correta && typeof questao.resposta_correta !== 'string') {
                                erros.push(`Questão ${numeroQuestao}: resposta_correta deve ser uma string`);
                            }
                            
                            if (questao.opcoes_disponiveis) {
                                if (!Array.isArray(questao.opcoes_disponiveis)) {
                                    erros.push(`Questão ${numeroQuestao}: opcoes_disponiveis deve ser um array`);
                                } else if (questao.opcoes_disponiveis.length < 2) {
                                    erros.push(`Questão ${numeroQuestao}: opcoes_disponiveis deve ter pelo menos 2 opções`);
                                } else {
                                    // Verifica se todas as opções são strings
                                    questao.opcoes_disponiveis.forEach((opcao, opcaoIndex) => {
                                        if (typeof opcao !== 'string') {
                                            erros.push(`Questão ${numeroQuestao}: Opção ${opcaoIndex + 1} deve ser uma string`);
                                        }
                                    });
                                    
                                    // Verifica se a resposta_correta está nas opções
                                    if (questao.resposta_correta && !questao.opcoes_disponiveis.includes(questao.resposta_correta)) {
                                        erros.push(`Questão ${numeroQuestao}: resposta_correta "${questao.resposta_correta}" não está presente nas opcoes_disponiveis`);
                                    }
                                }
                            }
                            
                            if (questao.explicacao_feedback && (typeof questao.explicacao_feedback !== 'string' || questao.explicacao_feedback.trim().length < 10)) {
                                erros.push(`Questão ${numeroQuestao}: explicacao_feedback deve ter pelo menos 10 caracteres`);
                            }
                            
                            if (questao.topico && typeof questao.topico !== 'string') {
                                erros.push(`Questão ${numeroQuestao}: topico deve ser uma string`);
                            }
                            
                            if (questao.nivel && typeof questao.nivel !== 'string') {
                                erros.push(`Questão ${numeroQuestao}: nivel deve ser uma string`);
                            }
                        });
                        
                        // Verifica IDs duplicados
                        const ids = [];
                        dados.forEach(questao => {
                            if (questao.id) {
                                if (ids.includes(questao.id)) {
                                    erros.push(`ID duplicado: ${questao.id}`);
                                }
                                ids.push(questao.id);
                            }
                        });
                    }
                    
                    // Se há erros, não envia para o servidor
                    if (erros.length > 0) {
                        showAlert('Erros de validação encontrados:\n' + erros.join('\n'), 'error');
                        return;
                    }
                    
                    // Se passou na validação, envia para o servidor
                    const response = await fetch('admin.php?acao=salvar-json', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'json_data=' + encodeURIComponent(jsonData) + '&csrf_token=' + encodeURIComponent(document.getElementById('admin_csrf_token').value)
                    });
                    
                    const result = await response.text();
                    
                    // Verifica se a resposta do servidor indica sucesso ou erro
                    if (response.ok) {
                        showAlert(result, 'success');
                        // Atualiza a página após 2 segundos
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        // Se o servidor retornou um erro (status 4xx, 5xx)
                        showAlert('Erro no servidor: ' + result, 'error');
                    }
                    
                } catch (error) {
                    // Captura erros de JSON.parse ou outros erros de rede
                    if (error instanceof SyntaxError) {
                        showAlert('Erro de sintaxe JSON: ' + error.message, 'error');
                    } else {
                        showAlert('Erro ao salvar JSON: ' + error.message, 'error');
                    }
                }
            }

            function resetToDefault() {
                if (!confirm('Tem certeza que deseja restaurar os dados padrão? Isso sobrescreverá o arquivo atual.')) {
                    return;
                }
                window.location.href = 'admin.php?acao=reset-padrao';
            }

            function downloadCurrentJson() {
                window.open('admin.php?acao=download-json', '_blank');
            }

            function downloadCurrentJsonL() {
                window.open('admin.php?acao=download-jsonl', '_blank');
            }

            // ========== UPLOAD DE ARQUIVOS ==========
            let currentFile = null;

            function setupUploadArea() {
                const uploadArea = document.getElementById('uploadArea');
                const fileInput = document.getElementById('fileInput');

                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('dragover');
                });

                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.classList.remove('dragover');
                });

                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        handleFileSelect(files[0]);
                    }
                });

                fileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        handleFileSelect(e.target.files[0]);
                    }
                });
            }

            function handleFileSelect(file) {
                if (!file.name.endsWith('.json') && !file.name.endsWith('.jsonl')) {
                    showAlert('Por favor, selecione apenas arquivos JSON ou JSONL.', 'error');
                    return;
                }

                currentFile = file;
                document.getElementById('fileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
                document.getElementById('fileInfo').style.display = 'block';
            }

            async function uploadFile() {
                if (!currentFile) {
                    showAlert('Por favor, selecione um arquivo primeiro.', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('json_file', currentFile);
                formData.append('csrf_token', document.getElementById('admin_csrf_token').value);

                try {
                    const response = await fetch('admin.php?acao=upload-json', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.text();
                    
                    if (response.ok) {
                        showAlert(result, 'success');
                        // Atualiza a página após 2 segundos
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showAlert('Erro no upload: ' + result, 'error');
                    }
                } catch (error) {
                    showAlert('Erro no upload: ' + error, 'error');
                }
            }

            // ========== GERENCIAMENTO DE QUIZZES ==========
            function abrirModalSalvarComo() {
                document.getElementById('modalSalvarComo').style.display = 'block';
            }

            function fecharModalSalvarComo() {
                document.getElementById('modalSalvarComo').style.display = 'none';
            }

            // Mostrar/ocultar campo de nova disciplina
            document.getElementById('disciplina').addEventListener('change', function() {
                const novaDisciplinaGroup = document.getElementById('novaDisciplinaGroup');
                novaDisciplinaGroup.style.display = this.value === 'nova' ? 'block' : 'none';
            });

            // Form salvar como
            document.getElementById('formSalvarComo').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                let disciplina = formData.get('disciplina');
                
                if (disciplina === 'nova') {
                    disciplina = document.getElementById('nova_disciplina').value;
                    if (!disciplina) {
                        alert('Por favor, informe o nome da nova disciplina');
                        return;
                    }
                }
                
                const dados = {
                    nome_quiz: formData.get('nome_quiz'),
                    disciplina: disciplina
                };
                
                try {
                    const response = await fetch('admin.php?acao=salvar-como', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(dados)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showAlert(result.message, 'success');
                        fecharModalSalvarComo();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(result.message, 'error');
                    }
                } catch (error) {
                    showAlert('Erro ao salvar quiz: ' + error, 'error');
                }
            });

            function carregarQuiz(caminho) {
                if (confirm('Deseja carregar este quiz? O quiz atual será substituído.')) {
                    window.location.href = 'admin.php?acao=carregar-quiz&caminho=' + caminho;
                }
            }

            function downloadQuiz(caminho, nome) {
                window.open('admin.php?acao=download-quiz&caminho=' + caminho + '&nome=' + encodeURIComponent(nome), '_blank');
            }

            function excluirQuiz(caminho) {
                const exclusaoAtiva = false;

                if (!exclusaoAtiva) {
                    confirm('A função de exclusão está desativada no momento.');
                    return;
                }
                if (confirm('Tem certeza que deseja excluir este quiz? Esta ação não pode ser desfeita.')) {
                    window.location.href = 'admin.php?acao=excluir-quiz&caminho=' + caminho;
                }
            }

            // ========== INICIALIZAÇÃO ==========
            document.addEventListener('DOMContentLoaded', function() {
                setupUploadArea();
                
                // Esconder alertas fixos após 5 segundos
                setTimeout(() => {
                    document.querySelectorAll('.alert-fixed').forEach(alert => {
                        alert.style.display = 'none';
                    });
                }, 5000);
            });

            // Fechar modal ao clicar fora
            window.onclick = function(event) {
                const modal = document.getElementById('modalSalvarComo');
                if (event.target === modal) {
                    fecharModalSalvarComo();
                }
            }

            // Validação no lado do cliente para melhor UX
            function validarJsonAntesEnvio() {
                const jsonEditor = document.getElementById('jsonEditor');
                const jsonData = jsonEditor.value;
                
                try {
                    const dados = JSON.parse(jsonData);
                    
                    // Validações básicas no cliente
                    if (!Array.isArray(dados)) {
                        throw new Error('O JSON deve ser um array de questões');
                    }
                    
                    if (dados.length === 0) {
                        throw new Error('O array não pode estar vazio');
                    }
                    
                    for (let i = 0; i < dados.length; i++) {
                        const questao = dados[i];
                        const numeroQuestao = i + 1;
                        
                        // Campos obrigatórios
                        const camposObrigatorios = ['id', 'pergunta', 'resposta_correta', 'opcoes_disponiveis', 'explicacao_feedback', 'topico', 'nivel'];
                        for (const campo of camposObrigatorios) {
                            if (!questao.hasOwnProperty(campo)) {
                                throw new Error(`Questão ${numeroQuestao}: Campo '${campo}' não encontrado`);
                            }
                        }
                        
                        if (typeof questao.id !== 'number') {
                            throw new Error(`Questão ${numeroQuestao}: ID deve ser um número`);
                        }
                        
                        if (typeof questao.pergunta !== 'string' || questao.pergunta.trim().length === 0) {
                            throw new Error(`Questão ${numeroQuestao}: Pergunta não pode ser vazia`);
                        }
                        
                        if (!Array.isArray(questao.opcoes_disponiveis) || questao.opcoes_disponiveis.length < 2) {
                            throw new Error(`Questão ${numeroQuestao}: Deve ter pelo menos 2 opções disponíveis`);
                        }
                        
                        // Verifica se resposta_correta está nas opções
                        if (!questao.opcoes_disponiveis.includes(questao.resposta_correta)) {
                            throw new Error(`Questão ${numeroQuestao}: A resposta correta não está nas opções disponíveis`);
                        }
                    }
                    
                    return true;
                } catch (error) {
                    showAlert('Erro de validação: ' + error.message, 'error');
                    return false;
                }
            }

            // Função para formatar JSON e converter markdown para HTML
            function formatJson() {
                try {
                    const jsonEditor = document.getElementById('jsonEditor');
                    const jsonData = JSON.parse(jsonEditor.value);
                    jsonEditor.value = JSON.stringify(jsonData, null, 2);
                    showAlert('JSON formatado com sucesso!', 'success');
                } catch (error) {
                    showAlert('Erro ao formatar JSON: ' + error.message, 'error');
                }
            }

            // Função auxiliar para sanitizar markdown no JSON (lado do cliente)
            function sanitizarMarkdownJson(dados) {
                return dados.map(questao => {
                    return {
                        ...questao,
                        pergunta: questao.pergunta.replace(/`([^`]+)`/g, '<code>$1</code>'),
                        explicacao_feedback: questao.explicacao_feedback.replace(/`([^`]+)`/g, '<code>$1</code>'),
                        opcoes_disponiveis: questao.opcoes_disponiveis ? 
                            questao.opcoes_disponiveis.map(opcao => 
                                opcao.replace(/`([^`]+)`/g, '<code>$1</code>')
                            ) : []
                    };
                });
            }
        </script>
    </body>
    </html>
    <?php
}

// ========== FUNÇÕES PHP ==========

function salvarJson() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_data = $_POST['json_data'] ?? '';
        
        if (empty($json_data)) {
            http_response_code(400);
            echo "Erro: Dados JSON vazios.";
            exit;
        }
        
        try {
            $dados = json_decode($json_data, true);
                       
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo "Erro de sintaxe JSON: " . json_last_error_msg();
                exit;
            }
            
            // Valida a estrutura do quiz (mantém markdown)
            $erros_validacao = validarEstruturaQuiz($dados);
            
            if (!empty($erros_validacao)) {
                http_response_code(400);
                echo "Erros de validação encontrados:\n" . implode("\n", $erros_validacao);
                exit;
            }
            
            // Corrige IDs sequenciais se necessário (mantém markdown)
            list($dados, $ids_corrigidos) = corrigirIDsSequenciais($dados);
            
            // if ($ids_corrigidos) {
            //     $mensagem = "IDs corrigidos para sequência numérica. ";
            // } else {
            //     $mensagem = "";
            // }
            
            // Salvar dados ORIGINAIS (com markdown)
            if (salvarDadosQuiz($dados)) {
                echo ($ids_corrigidos ? "IDs corrigidos para sequência numérica. " : "") . "Dados salvos com sucesso!";
            } else {
                http_response_code(500);
                echo "Erro ao salvar dados no arquivo.";
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "Erro: " . $e->getMessage();
        }
    }
    exit;
}

function uploadJson() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
        $file = $_FILES['json_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo "Erro no upload do arquivo: " . $file['error'];
            exit;
        }
        
        // Verifica o tipo do arquivo
        $file_type = $file['type'];
        $is_json = $file_type === 'application/json' || str_ends_with($file['name'], '.json');
        $is_jsonl = str_ends_with($file['name'], '.jsonl');
        
        if (!$is_json && !$is_jsonl) {
            http_response_code(400);
            echo "Por favor, envie apenas arquivos JSON ou JSONL.";
            exit;
        }
        
        // Verifica o tamanho do arquivo (máximo 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            http_response_code(400);
            echo "Arquivo muito grande. Tamanho máximo: 10MB";
            exit;
        }
        
        $content = file_get_contents($file['tmp_name']);
        
        if ($is_jsonl) {
            $dados = decodeJsonL($content);
        } else {
            $dados = json_decode($content, true);
        }
        
        if ($dados === null || !is_array($dados)) {
            http_response_code(400);
            echo "Arquivo inválido ou erro na leitura.";
            exit;
        }
        
        // Valida a estrutura do quiz
        $erros_validacao = validarEstruturaQuiz($dados);
        
        if (!empty($erros_validacao)) {
            http_response_code(400);
            echo "Erros de validação encontrados:\n" . implode("\n", $erros_validacao);
            exit;
        }
        
        // Corrige IDs sequenciais se necessário
        list($dados, $ids_corrigidos) = corrigirIDsSequenciais($dados);
        
        if ($ids_corrigidos) {
            $mensagem = "IDs corrigidos para sequência numérica. ";
        } else {
            $mensagem = "";
        }
        
        if (salvarDadosQuiz($dados)) {
            echo $mensagem . "Arquivo carregado e validado com sucesso!";
        } else {
            http_response_code(500);
            echo "Erro ao salvar arquivo.";
        }
    } else {
        http_response_code(400);
        echo "Nenhum arquivo enviado.";
    }
    exit;
}

function downloadJson($quiz_data) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="quiz_data.json"');
    echo json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function downloadJsonL($quiz_data) {
    header('Content-Type: application/x-jsonlines');
    header('Content-Disposition: attachment; filename="quiz_data.jsonl"');
    echo encodeJsonL($quiz_data);
    exit;
}

function resetParaPadrao() {
    global $FALLBACK_QUIZ_JSON;
    $dados = json_decode($FALLBACK_QUIZ_JSON, true);
    
    if (salvarDadosQuiz($dados)) {
        header('Location: admin.php?success=Dados restaurados para o padrão!');
        exit;
    } else {
        header('Location: admin.php?erro=Erro ao restaurar dados padrão.');
        exit;
    }
}

function salvarQuizComoAdmin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $nome_quiz = $input['nome_quiz'] ?? '';
        $disciplina = $input['disciplina'] ?? 'geral';
        
        if (empty($nome_quiz)) {
            echo json_encode(['success' => false, 'message' => 'Nome do quiz é obrigatório']);
            exit;
        }
        
        $quiz_data = carregarDadosQuiz();
        
        if (salvarQuizComo($quiz_data, $nome_quiz, $disciplina)) {
            echo json_encode(['success' => true, 'message' => 'Quiz salvo com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar quiz']);
        }
    }
    exit;
}

function carregarQuizAdmin() {
    $caminho = $_GET['caminho'] ?? '';
    
    if (empty($caminho)) {
        header('Location: admin.php?erro=Caminho não especificado');
        exit;
    }
    
    $quiz_data = carregarQuiz($caminho);
    
    if ($quiz_data && salvarDadosQuiz($quiz_data)) {
        header('Location: admin.php?success=Quiz carregado com sucesso');
    } else {
        header('Location: admin.php?erro=Erro ao carregar quiz');
    }
    exit;
}

function excluirQuizAdmin() {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        header('Location: admin.php?erro=ID do quiz não especificado');
        exit;
    }
    
    if (excluirQuiz($id)) {
        header('Location: admin.php?success=Quiz excluído com sucesso');
    } else {
        header('Location: admin.php?erro=Erro ao excluir quiz. O quiz inicial não pode ser excluído.');
    }
    exit;
}

function downloadTemplate() {
    $template = [
        [
            "id" => 1,
            "pergunta" => "Exemplo de pergunta bem formulada com pelo menos 10 caracteres?",
            "resposta_correta" => "Opção Correta",
            "opcoes_disponiveis" => [
                "Opção Incorreta 1",
                "Opção Correta",
                "Opção Incorreta 2", 
                "Opção Incorreta 3"
            ],
            "explicacao_feedback" => "Explicação detalhada sobre por que esta é a resposta correta, com pelo menos 10 caracteres.",
            "topico" => "Direito Civil",
            "nivel" => "Básico"
        ]
    ];
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="template_quiz_valido.json"');
    echo json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function downloadQuiz() {
    $caminho = $_GET['caminho'] ?? '';
    $nome_personalizado = $_GET['nome'] ?? 'quiz';
    
    if (empty($caminho) || !file_exists($caminho)) {
        header('Location: admin.php?erro=Arquivo não encontrado');
        exit;
    }
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $nome_personalizado . '.json"');
    header('Content-Length: ' . filesize($caminho));
    
    readfile($caminho);
    exit;
}

function toggleVisibilityAdmin() {
    $id = $_GET['id'] ?? 0;
    $visible = $_GET['visible'] ?? 1;
    
    if ($id > 0) {
        toggleVisibilidadeQuestao($id, $visible);
        header('Location: admin.php?success=Visibilidade atualizada!&acao=questoes');
    } else {
        header('Location: admin.php?erro=ID inválido');
    }
    exit;
}
?>
