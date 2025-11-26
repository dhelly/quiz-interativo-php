<?php
session_start();
require_once 'carregar_dados.php';

$acao = $_GET['acao'] ?? 'panel';
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
    case 'download-quiz':
        downloadQuiz();
        break;
    default:
        exibirPainelAdmin($quiz_data);
        break;
}

function exibirPainelAdmin($quiz_data) {
    $quizzes_salvos = listarQuizzes();
    $disciplinas = obterDisciplinas();
    
    $dados = [
        'total_questoes' => count($quiz_data),
        'arquivo_atual' => 'quiz_data.json',
        'questoes' => $quiz_data,
        'json_atual' => json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        'quizzes_salvos' => $quizzes_salvos,
        'disciplinas' => $disciplinas
    ];
    
    // Template HTML
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Painel Admin - Inútil App</title>
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
            }
            
            .container { 
                max-width: 1200px; 
                margin: 20px auto;
            }
            
            .header {
                background: var(--bg-card);
                padding: 25px 30px;
                border-radius: 8px;
                margin-bottom: 25px;
                border: 1px solid var(--border-color);
                text-align: center;
            }
            
            .header h1 {
                color: var(--text-light);
                font-size: 1.8em;
                margin-bottom: 8px;
            }
            
            .header p {
                color: var(--text-muted);
                font-size: 1.1em;
            }
            
            .card {
                background: var(--bg-card); 
                padding: 25px; 
                border-radius: 8px; 
                border: 1px solid var(--border-color);
                margin-bottom: 25px;
            }
            
            .two-columns {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 25px;
                margin-top: 20px;
            }
            
            @media (max-width: 768px) {
                .two-columns {
                    grid-template-columns: 1fr;
                }
            }
            
            .column {
                display: flex;
                flex-direction: column;
            }
            
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 25px;
            }
            
            .stat-card {
                background: var(--secondary-color);
                padding: 20px;
                border-radius: 6px;
                text-align: center;
                border: 1px solid var(--border-color);
            }
            
            .stat-number {
                font-size: 2em;
                font-weight: bold;
                color: var(--accent-color);
            }
            
            .tabs {
                display: flex;
                margin-bottom: 20px;
                border-bottom: 2px solid var(--border-color);
                flex-wrap: wrap;
                background: var(--secondary-color);
                border-radius: 6px 6px 0 0;
                padding: 5px;
            }
            
            .tab {
                padding: 12px 25px;
                cursor: pointer;
                border-radius: 4px;
                transition: all 0.3s ease;
                white-space: nowrap;
                color: var(--text-muted);
                font-weight: 500;
            }
            
            .tab.active {
                background: var(--accent-color);
                color: var(--text-light);
            }
            
            .tab-content {
                display: none;
            }
            
            .tab-content.active {
                display: block;
            }
            
            textarea {
                width: 100%;
                height: 400px;
                background: var(--secondary-color);
                color: var(--text-light);
                border: 1px solid var(--border-color);
                border-radius: 6px;
                padding: 15px;
                font-family: 'Courier New', monospace;
                font-size: 14px;
                resize: vertical;
            }
            
            textarea:focus {
                outline: none;
                border-color: var(--accent-color);
            }
            
            .btn {
                background: var(--accent-color);
                color: var(--text-light);
                border: none;
                padding: 12px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 0.95em;
                margin: 5px 0;
                transition: all 0.3s ease;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                width: 100%;
                box-sizing: border-box;
                border: 1px solid var(--border-color);
                font-weight: 500;
            }
            
            .btn:hover {
                background: #2980b9;
                transform: translateY(-1px);
            }
            
            .btn-success { background: var(--success-color); }
            .btn-warning { background: var(--warning-color); }
            .btn-error { background: var(--error-color); }
            .btn-secondary { background: var(--secondary-color); }
            
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-top: 10px;
            }
            
            .btn-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            
            .upload-area {
                border: 2px dashed var(--border-color);
                border-radius: 6px;
                padding: 40px;
                text-align: center;
                margin: 20px 0;
                transition: all 0.3s ease;
                background: var(--secondary-color);
            }
            
            .upload-area.dragover {
                border-color: var(--accent-color);
                background: var(--bg-card);
            }
            
            .questoes-list {
                max-height: 500px;
                overflow-y: auto;
            }
            
            .questao-item {
                background: var(--secondary-color);
                padding: 15px;
                margin: 10px 0;
                border-radius: 6px;
                border-left: 4px solid var(--accent-color);
                border: 1px solid var(--border-color);
            }
            
            .alert {
                padding: 15px;
                border-radius: 6px;
                margin: 15px 0;
                display: none;
                border: 1px solid transparent;
            }
            
            .alert-success { 
                background: rgba(39, 174, 96, 0.1); 
                border-color: var(--success-color);
                color: var(--success-color);
            }
            
            .alert-error { 
                background: rgba(231, 76, 60, 0.1); 
                border-color: var(--error-color);
                color: var(--error-color);
            }
            
            .action-panel {
                background: var(--secondary-color);
                padding: 20px;
                border-radius: 6px;
                margin-top: 20px;
                border: 1px solid var(--border-color);
            }
            
            .action-title {
                font-size: 1.1em;
                font-weight: bold;
                margin-bottom: 15px;
                color: var(--accent-color);
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 8px;
            }
            
            pre {
                background: var(--bg-dark);
                padding: 15px;
                border-radius: 4px;
                font-size: 0.8em;
                overflow-x: auto;
                border: 1px solid var(--border-color);
                color: var(--text-muted);
            }
            
            .badge {
                background: var(--accent-color);
                padding: 2px 8px;
                border-radius: 12px;
                margin-right: 5px;
                font-size: 0.75em;
                color: var(--text-light);
            }
            
            h3 {
                color: var(--text-light);
                margin-bottom: 15px;
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 10px;
            }
            
            p {
                color: var(--text-muted);
                margin-bottom: 15px;
            }

            /* NOVOS ESTILOS PARA QUIZZES SALVOS */
            .quiz-card {
                background: var(--secondary-color);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 15px;
                transition: all 0.3s ease;
            }
            
            .quiz-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
            
            .quiz-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .quiz-title {
                font-size: 1.2em;
                font-weight: bold;
                color: var(--text-light);
                margin: 0;
            }
            
            .quiz-disciplina {
                background: var(--accent-color);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.8em;
            }
            
            .quiz-info {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
                margin-bottom: 15px;
                font-size: 0.9em;
                color: var(--text-muted);
            }
            
            .quiz-stats {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 10px;
            }
            
            .stat-badge {
                background: var(--bg-dark);
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.8em;
            }
            
            .quiz-actions {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            
            .btn-small {
                padding: 8px 15px;
                font-size: 0.9em;
                width: auto;
            }
            
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            
            .modal-content {
                background: var(--bg-card);
                margin: 10% auto;
                padding: 25px;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                border: 1px solid var(--border-color);
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 5px;
                color: var(--text-light);
                font-weight: 500;
            }
            
            .form-group input,
            .form-group select {
                width: 100%;
                padding: 10px;
                background: var(--secondary-color);
                border: 1px solid var(--border-color);
                border-radius: 4px;
                color: var(--text-light);
                font-size: 1em;
            }
            
            .empty-state {
                text-align: center;
                padding: 40px;
                color: var(--text-muted);
            }
            
            .disciplina-section {
                margin-bottom: 30px;
            }
            
            .disciplina-header {
                font-size: 1.3em;
                color: var(--accent-color);
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 2px solid var(--border-color);
            }
            
            .grid-quizzes {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 20px;
            }

            .alert-fixed {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1001;
                min-width: 300px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⚙️ Painel de Administração</h1>
                <p>Inútil App - Gerenciamento de Quiz Jurídico</p>
            </div>

            <!-- Alertas fixos para mensagens do PHP -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-fixed" style="display: block;">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-error alert-fixed" style="display: block;">
                    <?php echo htmlspecialchars($_GET['erro']); ?>
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
                    <div class="tab" onclick="showTab('upload')">📁 Upload Arquivo</div>
                    <div class="tab" onclick="showTab('quizzes')">📚 Quizzes Salvos</div>
                    <div class="tab" onclick="showTab('questoes')">📊 Questões Atuais</div>
                </div>

                <!-- Alertas dinâmicos para JavaScript -->
                <div id="alertSuccess" class="alert alert-success"></div>
                <div id="alertError" class="alert alert-error"></div>

                <!-- Tab 1: Editor JSON -->
                <div id="editor" class="tab-content active">
                    <h3>Editor de JSON</h3>
                    <p>Edite diretamente o JSON das questões abaixo:</p>
                    
                    <div class="two-columns">
                        <div class="column">
                            <textarea id="jsonEditor" placeholder="Cole seu JSON aqui..."><?php echo htmlspecialchars($dados['json_atual']); ?></textarea>
                        </div>
                        <div class="column">
                            <div class="action-panel">
                                <div class="action-title">📋 Ações do Editor</div>
                                <div class="btn-group">
                                    <button class="btn btn-success" onclick="saveJson()">
                                        💾 Salvar JSON
                                    </button>
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
                                            📥 Download
                                        </button>
                                        <button class="btn btn-error" onclick="resetToDefault()">
                                            🔄 Restaurar
                                        </button>
                                    </div>
                                    <a href="index.php" class="btn">
                                        🎮 Voltar ao Quiz
                                    </a>
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
                        </div>
                        
                        <div class="column">
                            <div class="action-panel">
                                <div class="action-title">💡 Informações</div>
                                <p><strong>Formato esperado:</strong></p>
                                <pre>
[
  {
    "id": 1,
    "pergunta": "Texto da pergunta...",
    "resposta_correta": "Opção Correta",
    "opcoes_disponiveis": ["Opção A", "Opção B"],
    "explicacao_feedback": "Explicação detalhada...",
    "topico": "Direito",
    "nivel": "Básico"
  }
]</pre>
                                <div class="btn-group" style="margin-top: 15px;">
                                    <a href="admin.php?acao=download-json" class="btn btn-secondary" target="_blank">
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
                                    <div style="font-size: 0.75em; color: #7f8c8d; margin-top: 5px;">
                                        Opções: <?php 
                                        if (isset($questao['opcoes_disponiveis']) && is_array($questao['opcoes_disponiveis'])) {
                                            echo implode(', ', $questao['opcoes_disponiveis']);
                                        } else {
                                            echo 'N/A ou formato inválido';
                                        }
                                        ?>
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
                
                event.currentTarget.classList.add('active');
                document.getElementById(tabName).classList.add('active');
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
                showAlert('JSON atual carregado!', 'success');
            }

            async function saveJson() {
                const jsonData = document.getElementById('jsonEditor').value;
                
                try {
                    // Validação básica
                    const dados = JSON.parse(jsonData);
                    
                    // Validação da estrutura
                    let dadosValidos = true;
                    for (const questao of dados) {
                        if (!questao.opcoes_disponiveis || !Array.isArray(questao.opcoes_disponiveis)) {
                            dadosValidos = false;
                            break;
                        }
                    }
                    
                    if (!dadosValidos) {
                        showAlert('Erro: O campo "opcoes_disponiveis" deve ser um array em todas as questões.', 'error');
                        return;
                    }
                    
                    const response = await fetch('admin.php?acao=salvar-json', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'json_data=' + encodeURIComponent(jsonData)
                    });
                    
                    const result = await response.text();
                    showAlert('Dados salvos com sucesso!', 'success');
                    
                    // Atualiza a página após 2 segundos
                    setTimeout(() => location.reload(), 2000);
                } catch (error) {
                    showAlert('Erro ao salvar JSON: ' + error, 'error');
                }
            }

            function formatJson() {
                try {
                    const jsonData = JSON.parse(document.getElementById('jsonEditor').value);
                    document.getElementById('jsonEditor').value = JSON.stringify(jsonData, null, 2);
                    showAlert('JSON formatado com sucesso!', 'success');
                } catch (error) {
                    showAlert('Erro ao formatar JSON: ' + error, 'error');
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
                if (!file.name.endsWith('.json')) {
                    showAlert('Por favor, selecione apenas arquivos JSON.', 'error');
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

                try {
                    const response = await fetch('admin.php?acao=upload-json', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.text();
                    showAlert('Arquivo carregado com sucesso!', 'success');
                    
                    // Atualiza a página após 2 segundos
                    setTimeout(() => location.reload(), 2000);
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
        </script>
    </body>
    </html>
    <?php
}

// ========== FUNÇÕES PHP ==========

function salvarJson() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_data = $_POST['json_data'] ?? '';
        
        if (!empty($json_data)) {
            try {
                $dados = json_decode($json_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Validar estrutura dos dados
                    $dados_validos = true;
                    foreach ($dados as $questao) {
                        if (!isset($questao['opcoes_disponiveis']) || !is_array($questao['opcoes_disponiveis'])) {
                            $dados_validos = false;
                            break;
                        }
                    }
                    
                    if ($dados_validos) {
                        if (salvarDadosQuiz($dados)) {
                            echo "Dados salvos com sucesso!";
                        } else {
                            http_response_code(500);
                            echo "Erro ao salvar dados.";
                        }
                    } else {
                        http_response_code(400);
                        echo "JSON inválido: campo 'opcoes_disponiveis' deve ser um array em todas as questões.";
                    }
                } else {
                    http_response_code(400);
                    echo "JSON inválido.";
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo "Erro: " . $e->getMessage();
            }
        } else {
            http_response_code(400);
            echo "Dados vazios.";
        }
    }
    exit;
}

function uploadJson() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
        $file = $_FILES['json_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($file['tmp_name']);
            $dados = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                // Validar estrutura dos dados
                $dados_validos = true;
                foreach ($dados as $questao) {
                    if (!isset($questao['opcoes_disponiveis']) || !is_array($questao['opcoes_disponiveis'])) {
                        $dados_validos = false;
                        break;
                    }
                }
                
                if ($dados_validos) {
                    if (salvarDadosQuiz($dados)) {
                        echo "Arquivo carregado com sucesso!";
                    } else {
                        http_response_code(500);
                        echo "Erro ao salvar arquivo.";
                    }
                } else {
                    http_response_code(400);
                    echo "Arquivo JSON inválido: campo 'opcoes_disponiveis' deve ser um array.";
                }
            } else {
                http_response_code(400);
                echo "Arquivo JSON inválido.";
            }
        } else {
            http_response_code(400);
            echo "Erro no upload do arquivo.";
        }
    }
    exit;
}

function downloadJson($quiz_data) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="quiz_data.json"');
    echo json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
    $caminho = $_GET['caminho'] ?? '';
    
    if (empty($caminho)) {
        header('Location: admin.php?erro=Caminho não especificado');
        exit;
    }
    
    if (excluirQuiz($caminho)) {
        header('Location: admin.php?success=Quiz excluído com sucesso');
    } else {
        header('Location: admin.php?erro=Erro ao excluir quiz');
    }
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
?>