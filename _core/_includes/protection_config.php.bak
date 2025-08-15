<?php
/**
 * Configuração Anti-Pirataria - Sistema de Proteção
 * 
 * Este arquivo contém configurações específicas para o sistema de proteção
 * Mantenha este arquivo seguro e não compartilhe as informações
 */

// Configurações gerais do sistema de proteção
define('PROTECTION_ENABLED', true);
define('PROTECTION_LOG_ENABLED', true);
define('PROTECTION_STRICT_MODE', true);

// Configurações de contato para licenciamento
define('LICENSING_WHATSAPP', '558896941286'); // Seu WhatsApp para contato
define('LICENSING_EMAIL', 'licencas@seudominio.com'); // Seu email para licenças
define('LICENSING_WEBSITE', 'https://seusite.com'); // Seu site

// Configurações de log
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB máximo para logs
define('LOG_RETENTION_DAYS', 30); // Manter logs por 30 dias

// Configurações de segurança JavaScript
define('JS_PROTECTION_ENABLED', true);
define('JS_BLOCK_DEVTOOLS', true);
define('JS_BLOCK_RIGHTCLICK', true);
define('JS_CHECK_INTERVAL', 3000); // Verificar a cada 3 segundos

// Mensagens personalizadas
define('UNAUTHORIZED_MESSAGE_TITLE', 'Sistema Protegido');
define('UNAUTHORIZED_MESSAGE_TEXT', 'Este sistema possui licença ativa apenas para domínios autorizados.');

// Lista de IPs que podem acessar o gerenciador (opcional)
$allowed_manager_ips = [
    // Deixe vazio para permitir qualquer IP acessar o domain_manager.php
    // Adicione IPs específicos aqui se quiser restringir:
    // '192.168.1.100',
    // '203.45.67.89',
];

// Função para verificar se o IP pode acessar o gerenciador
function canAccessManager() {
    global $allowed_manager_ips;
    
    if (empty($allowed_manager_ips)) {
        return true; // Se não há restrição de IP, permite acesso
    }
    
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($user_ip, $allowed_manager_ips);
}

// Função para limpar logs antigos
function cleanupOldLogs() {
    $log_file = $_SERVER['DOCUMENT_ROOT'] . '/_core/_uploads/domain_access.log';
    
    if (file_exists($log_file)) {
        $file_size = filesize($log_file);
        
        // Se o arquivo for maior que o limite, remove entradas antigas
        if ($file_size > LOG_MAX_SIZE) {
            $logs = file($log_file, FILE_IGNORE_NEW_LINES);
            $recent_logs = array_slice($logs, -1000); // Mantém as últimas 1000 entradas
            
            file_put_contents($log_file, implode(PHP_EOL, $recent_logs) . PHP_EOL);
        }
    }
}

// Função para obter informações do sistema
function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'current_domain' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'protection_status' => PROTECTION_ENABLED ? 'ATIVO' : 'INATIVO',
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Executa limpeza automática dos logs (com probabilidade baixa para não sobrecarregar)
if (rand(1, 100) === 1) {
    cleanupOldLogs();
}

?>
