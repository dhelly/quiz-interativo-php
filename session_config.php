<?php
/**
 * Configuração de Sessão Segura e Padronizada
 */
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de segurança do cookie ANTES de iniciar a sessão
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_only_cookies', 1);
    
    // Aumenta a duração da sessão para 24 horas
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    // Ativa cookie seguro se estiver em HTTPS
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

require_once 'sanitize.php';

// Inicializa variáveis de sessão essenciais se não existirem
if (!isset($_SESSION['questoes_erradas'])) {
    $_SESSION['questoes_erradas'] = [];
}
