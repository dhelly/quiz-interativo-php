<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - Quiz Interativo</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-home">
    <div class="container-quiz" style="max-width: 900px;"> <!-- Ligeiramente maior para a grade -->
        <div class="header-home" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
            <div>
                <h1>Olá, <?php echo h($dados['username']); ?>! 👋</h1>
                <p>Escolha um desafio para começar:</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="index.php?acao=logout" class="btn btn-secondary btn-small">🚪 Sair</a>
            </div>
        </div>

        <?php
        $quizzes_por_disciplina = [];
        foreach ($dados['quizzes'] as $quiz) {
            $disciplina = $quiz['disciplina'] ?? 'geral';
            $quizzes_por_disciplina[$disciplina][] = $quiz;
        }
        ?>

        <?php foreach ($quizzes_por_disciplina as $disciplina => $quizzes): ?>
            <div class="disciplina-container" style="margin-bottom: 40px;">
                <h2 style="font-size: 1.1rem; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📂 <?php echo h(ucfirst($disciplina)); ?>
                </h2>
                
                <div class="grid-quizzes">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header" style="margin-bottom: 8px;">
                                <h3 class="quiz-title"><?php echo h($quiz['nome']); ?></h3>
                            </div>
                            
                            <div class="quiz-info" style="margin-bottom: 12px; flex-grow: 1;">
                                <div style="font-size: 0.8rem; margin-bottom: 4px;">
                                    <strong><?php echo $quiz['total_questoes']; ?></strong> questões
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3;">
                                    <?php 
                                    $topicos = array_keys($quiz['topicos']);
                                    $exibir = array_slice($topicos, 0, 2);
                                    echo implode(', ', $exibir);
                                    if (count($topicos) > 2) echo '...';
                                    ?>
                                </div>
                            </div>
                            
                            <div class="quiz-actions" style="border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: auto;">
                                <a href="index.php?carregar_quiz=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-small btn-block">Jogar 🎮</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (isset($dados['total_erros_persistentes']) && $dados['total_erros_persistentes'] > 0): ?>
            <div class="reforco-container" style="margin-top: 20px; padding: 20px; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border); text-align: center; box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 10px; color: var(--primary);">🎯 Reforçar Aprendizado</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">
                    Você tem <strong><?php echo $dados['total_erros_persistentes']; ?></strong> questões para revisar. 
                    Foque nos pontos onde você teve dificuldade!
                </p>
                <a href="index.php?acao=quiz_erros" class="btn btn-primary">Começar Revisão de Erros 🚀</a>
            </div>
        <?php endif; ?>

        <div class="home-footer" style="margin-top: 20px; text-align: center; border-top: 1px solid var(--border); padding-top: 30px; display: flex; justify-content: center; gap: 15px;">
            <a href="ranking.php" class="btn btn-secondary">🏆 Ranking Global</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin.php" class="btn btn-warning">⚙️ Painel Admin</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
