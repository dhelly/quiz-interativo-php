<?php
session_start();
require_once 'carregar_dados.php';

$quiz_id = $_GET['quiz_id'] ?? 1;
$ranking = obterRanking($quiz_id);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Quiz Interativo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-ranking">
    <div class="container-quiz">
        <div class="header-quiz">
            <h1>🏆 Ranking de Líderes</h1>
            <p>Os melhores desempenhos no Quiz</p>
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
                            <tr class="ranking-row <?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?>">
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
                <a href="index.php" class="btn btn-primary">🎮 Jogar Agora</a>
                <a href="admin.php" class="btn btn-secondary">⚙️ Painel Admin</a>
            </div>
        </div>
    </div>
</body>
</html>
