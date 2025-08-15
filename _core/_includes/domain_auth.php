<?php
/**
 * Sistema de Autenticação por Domínio
 * Protege o sistema contra uso não autorizado
 * 
 * @author Sistema Anti-Pirataria
 * @version 2.0
 */

// Inclui configurações de proteção
require_once(__DIR__ . '/protection_config.php');

class DomainAuth {
    
    // Lista de domínios autorizados (criptografados)
    private static $authorized_domains = [
        // Adicione seus domínios aqui usando o método encryptDomain()
        '8b9c7e4f5a2d1c6e3f8a9b7c4e5f6a2b' => 'digitavitrine.com.br',
        '7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f' => 'catalogo.zfoxx.xyz',
        'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6' => 'sistem.digitavitrine.com.br',
    '82d7feda73aac604ba980751f97609ae' => 'cardapio24h.com.br',
    'cd85c1f1d40e2e07cb556d1dec698205' => 'www.cardapio24h.com.br',
        // Para adicionar um novo domínio, use: DomainAuth::generateDomainHash('seudominio.com')
    ];
    
    // Chave de criptografia (mude esta chave para algo único)
    private static $secret_key = 'TecAutoVip@2025#Sistema$Protegido!';
    
    // Cache para evitar múltiplas verificações
    private static $auth_cache = null;
    
    /**
     * Verifica se o domínio atual está autorizado
     */
    public static function checkDomainAuth() {
        // Se já verificou nesta sessão, retorna o cache
        if (self::$auth_cache !== null) {
            return self::$auth_cache;
        }
        
        $current_domain = self::getCurrentDomain();
        $is_authorized = false;
        
        // Verifica se o domínio está na lista de autorizados
        foreach (self::$authorized_domains as $hash => $domain) {
            if (self::verifyDomain($current_domain, $domain)) {
                $is_authorized = true;
                self::logAccess($current_domain, 'AUTHORIZED');
                break;
            }
        }
        
        if (!$is_authorized) {
            self::logAccess($current_domain, 'UNAUTHORIZED');
            self::blockAccess();
        }
        
        // Armazena no cache
        self::$auth_cache = $is_authorized;
        return $is_authorized;
    }
    
    /**
     * Obtém o domínio atual de forma segura
     */
    private static function getCurrentDomain() {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        
        // Remove www. se existir
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove porta se existir
        $domain = preg_replace('/:\d+$/', '', $domain);
        
        return strtolower($domain);
    }
    
    /**
     * Verifica se o domínio atual corresponde a um domínio autorizado
     */
    private static function verifyDomain($current, $authorized) {
        // Verifica correspondência exata
        if ($current === $authorized) {
            return true;
        }
        
        // Verifica se é um subdomínio autorizado
        if (strpos($current, '.' . $authorized) !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Bloqueia o acesso não autorizado
     */
    private static function blockAccess() {
        // Limpa qualquer output anterior
        if (ob_get_length()) ob_clean();
        
        // Define header HTTP 403
        http_response_code(403);
        
        // Página de bloqueio personalizada
        echo self::getBlockPage();
        
        // Para a execução
        exit();
    }
    
    /**
     * Retorna a página de bloqueio
     */
    private static function getBlockPage() {
        $contact_whatsapp = defined('LICENSING_WHATSAPP') ? LICENSING_WHATSAPP : "5511999999999";
        $current_domain = self::getCurrentDomain();
        $title = defined('UNAUTHORIZED_MESSAGE_TITLE') ? UNAUTHORIZED_MESSAGE_TITLE : "Acesso Não Autorizado";
        $message = defined('UNAUTHORIZED_MESSAGE_TEXT') ? UNAUTHORIZED_MESSAGE_TEXT : "Este sistema está licenciado e só pode ser usado em domínios autorizados.";
        
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Não Autorizado</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: white;
        }
        .container { 
            text-align: center; 
            padding: 40px; 
            background: rgba(255,255,255,0.1); 
            border-radius: 15px; 
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .icon { font-size: 4em; margin-bottom: 20px; }
        h1 { margin: 20px 0; font-size: 2em; }
        p { margin: 15px 0; line-height: 1.6; }
        .domain { 
            background: rgba(255,255,255,0.2); 
            padding: 10px; 
            border-radius: 8px; 
            font-family: monospace; 
            font-weight: bold;
            margin: 20px 0;
        }
        .btn { 
            display: inline-block; 
            background: #25D366; 
            color: white; 
            padding: 15px 30px; 
            text-decoration: none; 
            border-radius: 25px; 
            margin: 20px 10px; 
            transition: all 0.3s;
            font-weight: bold;
        }
        .btn:hover { 
            background: #1FA952; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .footer { 
            margin-top: 30px; 
            font-size: 0.9em; 
            opacity: 0.8; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>' . htmlspecialchars($title) . '</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <div class="domain">Domínio atual: ' . htmlspecialchars($current_domain) . '</div>
        <p>Se você adquiriu uma licença para este domínio, entre em contato conosco para ativação.</p>
        <a href="https://api.whatsapp.com/send?phone=558896941286&text=Preciso%20ativar%20minha%20licença%20para%20o%20domínio%3A%20' . urlencode($current_domain) . '" class="btn" target="_blank">
            📱 Ativar Licença
        </a>
        <div class="footer">
            <p>Código de verificação: ' . substr(md5($current_domain . self::$secret_key), 0, 8) . '</p>
        </div>
    </div>
    
    <script>
        // Proteção adicional JavaScript
        (function() {
            const domain = window.location.hostname;
            const allowedDomains = ' . json_encode(array_values(self::$authorized_domains)) . ';
            
            function checkDomain() {
                let authorized = false;
                allowedDomains.forEach(function(allowed) {
                    if (domain === allowed || domain.endsWith("." + allowed)) {
                        authorized = true;
                    }
                });
                
                if (!authorized) {
                    document.body.style.display = "flex";
                    // Bloqueia teclas de desenvolvedor
                    document.addEventListener("keydown", function(e) {
                        if (e.key === "F12" || (e.ctrlKey && e.shiftKey && e.key === "I")) {
                            e.preventDefault();
                            return false;
                        }
                    });
                    
                    // Bloqueia menu de contexto
                    document.addEventListener("contextmenu", function(e) {
                        e.preventDefault();
                        return false;
                    });
                }
            }
            
            checkDomain();
            
            // Verifica periodicamente
            setInterval(checkDomain, 5000);
        })();
    </script>
</body>
</html>';
    }
    
    /**
     * Registra tentativas de acesso
     */
    private static function logAccess($domain, $status) {
        $log_file = $_SERVER['DOCUMENT_ROOT'] . '/_core/_uploads/domain_access.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $log_entry = "[$timestamp] Domain: $domain | Status: $status | IP: $ip | User-Agent: $user_agent" . PHP_EOL;
        
        // Cria o diretório se não existir
        $dir = dirname($log_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Adiciona ao log
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Gera hash para um novo domínio
     */
    public static function generateDomainHash($domain) {
        return md5($domain . self::$secret_key);
    }
    
    /**
     * Adiciona um novo domínio autorizado
     */
    public static function addAuthorizedDomain($domain) {
        $hash = self::generateDomainHash($domain);
        echo "Adicione esta linha ao array \$authorized_domains:\n";
        echo "'$hash' => '$domain',\n";
    }
    
    /**
     * Verifica se o sistema está funcionando corretamente
     */
    public static function systemCheck() {
        $current_domain = self::getCurrentDomain();
        $is_authorized = false;
        
        foreach (self::$authorized_domains as $hash => $domain) {
            if (self::verifyDomain($current_domain, $domain)) {
                $is_authorized = true;
                break;
            }
        }
        
        return [
            'current_domain' => $current_domain,
            'is_authorized' => $is_authorized,
            'total_authorized_domains' => count(self::$authorized_domains),
            'system_status' => $is_authorized ? 'ATIVO' : 'BLOQUEADO'
        ];
    }
}

// Verifica automaticamente quando o arquivo é incluído
DomainAuth::checkDomainAuth();
?>
