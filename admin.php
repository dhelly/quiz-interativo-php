<?php
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
    default:
        exibirPainelAdmin($quiz_data);
        break;
}

function exibirPainelAdmin($quiz_data) {
    $dados = [
        'total_questoes' => count($quiz_data),
        'arquivo_atual' => 'quiz_data.json',
        'questoes' => $quiz_data,
        'json_atual' => json_encode($quiz_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ];
    
    // Template HTML vai aqui (similar ao admin.html anterior, mas em PHP)
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Painel Admin - Inútil App</title>
        <style>
            /* CSS idêntico ao do template admin.html anterior */
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
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⚙️ Painel de Administração</h1>
                <p>Inútil App - Gerenciamento de Quiz Interativo</p>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dados['total_questoes']; ?></div>
                    <div>Total de Questões</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $dados['arquivo_atual']; ?></div>
                    <div>Arquivo Atual</div>
                </div>
            </div>

            <div class="card">
                <div class="tabs">
                    <div class="tab active" onclick="showTab('editor')">📝 Editor JSON</div>
                    <div class="tab" onclick="showTab('upload')">📁 Upload Arquivo</div>
                    <div class="tab" onclick="showTab('questoes')">📊 Visualizar Questões</div>
                </div>

                <!-- Alertas -->
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
                                        <button class="btn btn-secondary" onclick="downloadJson()">
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
                            <form id="uploadForm" enctype="multipart/form-data">
                                <div class="upload-area" id="uploadArea">
                                    <p>📁 Arraste e solte um arquivo JSON aqui ou</p>
                                    <input type="file" id="fileInput" name="json_file" accept=".json" style="display: none;">
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
                            </form>
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

                <!-- Tab 3: Visualizar Questões -->
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
                                        Opções: <?php echo implode(', ', $questao['opcoes_disponiveis']); ?>
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

        <script>
            // JavaScript similar ao anterior, mas adaptado para PHP
            let currentFile = null;

            function showTab(tabName) {
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                event.currentTarget.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            }

            async function saveJson() {
                const jsonData = document.getElementById('jsonEditor').value;
                
                try {
                    const formData = new FormData();
                    formData.append('json_data', jsonData);
                    
                    const response = await fetch('admin.php?acao=salvar-json', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.text();
                    showAlert('Dados salvos com sucesso!', 'success');
                    setTimeout(() => location.reload(), 2000);
                } catch (error) {
                    showAlert('Erro ao salvar JSON: ' + error, 'error');
                }
            }

            function loadCurrentJson() {
                // Já está carregado
                showAlert('JSON atual carregado!', 'success');
            }

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
                    setTimeout(() => location.reload(), 2000);
                } catch (error) {
                    showAlert('Erro no upload: ' + error, 'error');
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

            function downloadJson() {
                window.open('admin.php?acao=download-json', '_blank');
            }

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

            document.addEventListener('DOMContentLoaded', function() {
                setupUploadArea();
            });
        </script>
    </body>
    </html>
    <?php
}

function salvarJson() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_data = $_POST['json_data'] ?? '';
        
        if (!empty($json_data)) {
            try {
                $dados = json_decode($json_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (salvarDadosQuiz($dados)) {
                        echo "Dados salvos com sucesso!";
                    } else {
                        http_response_code(500);
                        echo "Erro ao salvar dados.";
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
                if (salvarDadosQuiz($dados)) {
                    echo "Arquivo carregado com sucesso!";
                } else {
                    http_response_code(500);
                    echo "Erro ao salvar arquivo.";
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
        header('Location: admin.php?acao=panel');
        exit;
    } else {
        die("Erro ao restaurar dados padrão.");
    }
}
?>