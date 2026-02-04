<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo - Quiz Interativo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-welcome">
    <div class="container-welcome">
        <div class="welcome-card">
            <div class="welcome-header">
                <span class="emoji-hero">⚡</span>
                <h1>Quiz Interativo - Inutil.app</h1>
                <p>Teste seu conhecimento e conquiste o topo do ranking!</p>
            </div>

            <?php if (isset($erro_login)): ?>
                <div class="alert alert-error" style="display: block; margin-bottom: 20px;">
                    ❌ <?php echo h($erro_login); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?acao=login" method="POST" class="welcome-form">
                <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                <div class="form-group">
                    <label for="username">Apelido (Username)</label>
                    <input type="text" id="username" name="username" required placeholder="Seu apelido..." minlength="3">
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required placeholder="Sua senha...">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
                        *Se não tiver conta, ela será criada com essa senha.
                    </p>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">Entrar no Desafio 🚀</button>
            </form>

            <div class="welcome-footer" style="margin-top: 30px; text-align: center;">
                <a href="ranking.php" class="btn btn-secondary">🏆 Ver Ranking Global</a>
            </div>
        </div>
    </div>
</body>
</html>
