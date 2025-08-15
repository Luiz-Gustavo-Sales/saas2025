<?php
// Incluir configurações
include('../_core/_includes/config.php');

// Verificar se há sessão ativa
session_start();

// Buscar dados do estabelecimento pelo subdomínio
$host_parts = explode('.', $_SERVER['HTTP_HOST']);
$insubdominio = $host_parts[0];

$estabelecimento_query = mysqli_query($db_con, "SELECT * FROM estabelecimentos WHERE subdominio = '$insubdominio' AND status = '1'");

if (mysqli_num_rows($estabelecimento_query) == 0) {
    header("Location: login.php?error=estabelecimento_nao_encontrado");
    exit;
}

$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);

// Se não está logado, redirecionar para login
if (!isset($_SESSION['garcom_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o garçom pertence a este estabelecimento
$garcom_check = mysqli_query($db_con, "SELECT * FROM garcons WHERE id = '".$_SESSION['garcom_id']."' AND rel_estabelecimentos_id = '".$estabelecimento_data['id']."' AND ativo = 1");

if (mysqli_num_rows($garcom_check) == 0) {
    session_destroy();
    header("Location: login.php?error=acesso_negado");
    exit;
}

$garcom_data = mysqli_fetch_array($garcom_check);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - <?php echo $estabelecimento_data['nome']; ?></title>
    <meta name="theme-color" content="#667eea">
    
    <!-- Bootstrap CSS -->
    <link href="../_cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../_cdn/lineicons/LineIcons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .app-header {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        .app-content {
            padding: 1rem;
        }
        
        .coming-soon {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .coming-soon-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .coming-soon h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .coming-soon p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .features-list {
            text-align: left;
            max-width: 400px;
            margin: 2rem auto 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0;
            color: #555;
        }
        
        .feature-icon {
            color: var(--success-color);
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-info">
            <h1><i class="lni lni-clipboard"></i> Pedidos - <?php echo $estabelecimento_data['nome']; ?></h1>
        </div>
        <a href="index.php" class="back-btn">
            <i class="lni lni-arrow-left"></i> Voltar
        </a>
    </header>
    
    <main class="app-content">
        <div class="coming-soon">
            <div class="coming-soon-icon">
                <i class="lni lni-construction"></i>
            </div>
            <h2>Módulo em Desenvolvimento</h2>
            <p>O sistema de pedidos está sendo desenvolvido e em breve estará disponível com todas as funcionalidades.</p>
            
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon"><i class="lni lni-checkmark-circle"></i></div>
                    <div>Acompanhar pedidos em tempo real</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="lni lni-checkmark-circle"></i></div>
                    <div>Notificações de novos pedidos</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="lni lni-checkmark-circle"></i></div>
                    <div>Status de preparação</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="lni lni-checkmark-circle"></i></div>
                    <div>Histórico de pedidos</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="lni lni-checkmark-circle"></i></div>
                    <div>Integração com cozinha</div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../_cdn/jquery/jquery.min.js"></script>
    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
