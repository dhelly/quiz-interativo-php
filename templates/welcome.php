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
                <h1>Quiz Interativo</h1>
                <p>Teste seu conhecimento e conquiste o topo do ranking!</p>
            </div>

            <form action="index.php?acao=login" method="POST" class="welcome-form">
                <div class="form-group">
                    <label for="username">Qual é o seu apelido?</label>
                    <input type="text" id="username" name="username" required placeholder="Digite aqui..." minlength="3">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Desafio 🚀</button>
            </form>

            <div class="welcome-footer" style="margin-top: 30px; text-align: center;">
                <a href="ranking.php" class="btn btn-secondary">🏆 Ver Ranking Global</a>
            </div>
        </div>
    </div>
</body>
</html>
