<?php
session_start();
require_once 'carregar_dados.php';

$quiz_id = $_GET['quiz_id'] ?? 1;
$ranking = obterRanking($quiz_id);
$quizzes = listarQuizzes();
$quiz_atual = null;
foreach ($quizzes as $q) {
    if ($q['id'] == $quiz_id) {
        $quiz_atual = $q;
        break;
    }
}
$nome_quiz = $quiz_atual ? $quiz_atual['name'] : 'Quiz';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - <?php echo htmlspecialchars($nome_quiz); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-ranking">
    <div class="container-quiz">
        <div class="header-quiz" style="position: relative;">
            <div style="position: absolute; left: 0; top: 0;">
                <a href="index.php?acao=home" class="btn btn-secondary btn-small">🏠 Início</a>
            </div>
            <h1>🏆 Ranking de Líderes</h1>
            <p>Simulado: <strong><?php echo htmlspecialchars($nome_quiz); ?></strong></p>
            
            <form action="ranking.php" method="GET" style="margin-top: 15px;">
                <select name="quiz_id" onchange="this.form.submit()" class="btn btn-secondary" style="background: white; color: var(--text-main); border: 1px solid var(--border); padding: 5px 15px;">
                    <?php foreach ($quizzes as $q): ?>
                        <option value="<?php echo $q['id']; ?>" <?php echo $q['id'] == $quiz_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($q['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="card ranking-card">
            <?php if (empty($ranking)): ?>
                <div class="empty-state">
                    <p>Ainda não há pontuações registradas. Seja o primeiro!</p>
                </div>
            <?php else: ?>
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Usuário</th>
                            <th>Acertos</th>
                            <th>Porcentagem</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $index => $row): ?>
                            <?php $is_current_user = (isset($_SESSION['username']) && $_SESSION['username'] === $row['username']); ?>
                            <tr class="ranking-row <?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?> <?php echo $is_current_user ? 'current-user-row' : ''; ?>" <?php echo $is_current_user ? 'style="background: #eef2ff; border-left: 4px solid var(--primary);"' : ''; ?>>
                                <td><?php echo $index + 1; ?>º</td>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo $row['score']; ?>/<?php echo $row['total']; ?></td>
                                <td>
                                    <div class="rank-percent-bg">
                                        <div class="rank-percent-fill" style="width: <?php echo $row['percentage']; ?>%"></div>
                                        <span><?php echo number_format($row['percentage'], 1); ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['completed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="action-buttons" style="margin-top: 40px; display: flex; gap: 15px; justify-content: center;">
                <a href="index.php?carregar_quiz=<?php echo $quiz_id; ?>" class="btn btn-primary">🎮 Jogar Este Quiz</a>
                <a href="index.php?acao=home" class="btn btn-secondary">🏠 Selecionar Outro</a>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <a href="admin.php" class="btn btn-warning">⚙️ Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
