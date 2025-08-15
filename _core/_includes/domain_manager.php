<?php
/**
 * Utilitário para Gerenciar Domínios Autorizados
 * 
 * Este arquivo ajuda a adicionar novos domínios ao sistema
 * Execute este arquivo apenas quando precisar adicionar novos domínios
 */

// Configuração de exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tenta incluir as configurações de proteção
try {
    require_once(__DIR__ . '/protection_config.php');
} catch (Exception $e) {
    die("Erro ao carregar protection_config.php: " . $e->getMessage());
}

// Verifica se o IP pode acessar este gerenciador
try {
    if (!canAccessManager()) {
        http_response_code(403);
        die('Acesso negado. IP não autorizado. Seu IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
} catch (Exception $e) {
    die("Erro na verificação de IP: " . $e->getMessage());
}

// Inclui o sistema de autenticação
try {
    require_once(__DIR__ . '/domain_auth.php');
} catch (Exception $e) {
    die("Erro ao carregar domain_auth.php: " . $e->getMessage());
}

// Função para adicionar um novo domínio
function addNewDomain($domain) {
    echo "<h3>Adicionando domínio: $domain</h3>";
    
    // Gera o hash do domínio
    $hash = DomainAuth::generateDomainHash($domain);
    
    echo "<p><strong>Hash gerado:</strong> $hash</p>";
    echo "<p><strong>Linha para adicionar ao domain_auth.php:</strong></p>";
    echo "<code>'$hash' => '$domain',</code>";
    echo "<hr>";
}

// Função para verificar o status atual do sistema
function checkSystemStatus() {
    echo "<h3>Status do Sistema</h3>";
    
    $status = DomainAuth::systemCheck();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Propriedade</th><th>Valor</th></tr>";
    
    foreach ($status as $key => $value) {
        echo "<tr><td>$key</td><td>";
        if (is_bool($value)) {
            echo $value ? 'SIM' : 'NÃO';
        } else {
            echo htmlspecialchars($value);
        }
        echo "</td></tr>";
    }
    
    echo "</table>";
    echo "<hr>";
}

// Função para listar logs de acesso
function showAccessLogs() {
    echo "<h3>Logs de Acesso (últimas 50 entradas)</h3>";
    
    $log_file = $_SERVER['DOCUMENT_ROOT'] . '/_core/_uploads/domain_access.log';
    
    if (file_exists($log_file)) {
        $logs = file($log_file, FILE_IGNORE_NEW_LINES);
        $recent_logs = array_slice($logs, -50);
        
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px;'>";
        foreach (array_reverse($recent_logs) as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>Nenhum log encontrado.</p>";
    }
    
    echo "<hr>";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Domínios - Sistema Anti-Pirataria</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 30px;
        }
        .section { 
            margin-bottom: 30px; 
            padding: 20px; 
            background: #f9f9f9; 
            border-radius: 8px;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold;
        }
        input[type="text"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
        }
        button { 
            background: #007cba; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
        }
        button:hover { 
            background: #005a87; 
        }
        .alert { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px;
        }
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .alert-warning { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        table { 
            width: 100%; 
            border-collapse: collapse;
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
            border: 1px solid #ddd;
        }
        th { 
            background: #f8f9fa;
        }
        code { 
            background: #e9ecef; 
            padding: 2px 4px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        pre { 
            max-height: 300px; 
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Gerenciador de Domínios Anti-Pirataria</h1>
        
        <div class="alert alert-warning">
            <strong>⚠️ Atenção:</strong> Este arquivo deve ser removido ou protegido em produção!
        </div>
        
        <div class="section">
            <h2>📊 Status do Sistema</h2>
            <?php checkSystemStatus(); ?>
        </div>
        
        <div class="section">
            <h2>➕ Adicionar Novo Domínio</h2>
            
            <?php
            if (isset($_POST['add_domain']) && !empty($_POST['domain'])) {
                $domain = trim($_POST['domain']);
                $domain = strtolower($domain);
                $domain = preg_replace('/^www\./', '', $domain);
                
                addNewDomain($domain);
                
                echo "<div class='alert alert-success'>";
                echo "<strong>✅ Domínio processado!</strong><br>";
                echo "Copie a linha gerada acima e adicione ao arquivo <code>domain_auth.php</code> no array <code>\$authorized_domains</code>";
                echo "</div>";
            }
            ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="domain">Domínio a ser autorizado:</label>
                    <input type="text" id="domain" name="domain" placeholder="exemplo.com.br" required>
                    <small>Digite apenas o domínio, sem www, http ou https</small>
                </div>
                <button type="submit" name="add_domain">Gerar Hash do Domínio</button>
            </form>
        </div>
        
        <div class="section">
            <h2>📋 Como Usar</h2>
            <ol>
                <li>Digite o domínio que deseja autorizar no campo acima</li>
                <li>Clique em "Gerar Hash do Domínio"</li>
                <li>Copie a linha gerada e adicione ao arquivo <code>_core/_includes/domain_auth.php</code></li>
                <li>Procure pelo array <code>$authorized_domains</code> e adicione a nova linha</li>
                <li>Salve o arquivo e teste o acesso no novo domínio</li>
            </ol>
        </div>
        
        <div class="section">
            <h2>🔍 Logs de Acesso</h2>
            <?php showAccessLogs(); ?>
        </div>
        
        <div class="section">
            <h2>🛡️ Instruções de Segurança</h2>
            <ul>
                <li><strong>Remova este arquivo</strong> após configurar todos os domínios</li>
                <li>Mantenha backups do arquivo <code>domain_auth.php</code></li>
                <li>Não compartilhe os hashes dos domínios</li>
                <li>Monitore os logs regularmente</li>
                <li>Use HTTPS sempre que possível</li>
            </ul>
        </div>
    </div>
</body>
</html>
