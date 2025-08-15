<?php 
include('../_core/_includes/config.php'); 
session_start();

$error_message = '';

// Verificar mensagens de erro da URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'estabelecimento_nao_encontrado':
            $error_message = 'Estabelecimento não encontrado para este subdomínio.';
            break;
        case 'acesso_negado':
            $error_message = 'Acesso negado. Você não pertence a este estabelecimento.';
            break;
    }
}

// Verificar se foi logout
if (isset($_GET['logout'])) {
    $error_message = ''; // Limpar erro se foi logout
}

if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $usuario = mysqli_real_escape_string($db_con, $_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Buscar garçom em qualquer estabelecimento ativo (simplificado para teste)
    $garcom_query = mysqli_query($db_con, "SELECT g.*, e.id as estabelecimento_id FROM garcons g INNER JOIN estabelecimentos e ON g.rel_estabelecimentos_id = e.id WHERE g.usuario = '$usuario' AND g.ativo = 1 AND e.status = '1' LIMIT 1");
    
    if (mysqli_num_rows($garcom_query) == 1) {
        $garcom_data = mysqli_fetch_array($garcom_query);
        
        if (password_verify($senha, $garcom_data['senha'])) {
            // Login bem-sucedido
            $_SESSION['garcom_id'] = $garcom_data['id'];
            $_SESSION['garcom_estabelecimento_id'] = $garcom_data['estabelecimento_id'];
            $_SESSION['garcom_nome'] = $garcom_data['nome'];
            
            header("Location: index.php");
            exit;
        } else {
            $error_message = 'Usuário ou senha incorretos.';
        }
    } else {
        $error_message = 'Usuário ou senha incorretos.';
    }
}

// Detectar o estabelecimento baseado no subdomínio da URL
$host_parts = explode('.', $_SERVER['HTTP_HOST']);
$subdominio = '';

if (count($host_parts) > 2) {
    $subdominio = $host_parts[0]; // Pega o subdomínio (ex: shopburger)
}

// Buscar dados do estabelecimento baseado no subdomínio
if ($subdominio) {
    $estabelecimento_query = mysqli_query($db_con, "SELECT nome, perfil FROM estabelecimentos WHERE subdominio = '$subdominio' AND status = '1' LIMIT 1");
    if (mysqli_num_rows($estabelecimento_query) > 0) {
        $estabelecimento_data = mysqli_fetch_array($estabelecimento_query);
    } else {
        // Se não encontrou pelo subdomínio, pega o primeiro ativo como fallback
        $estabelecimento_query = mysqli_query($db_con, "SELECT nome, perfil FROM estabelecimentos WHERE status = '1' LIMIT 1");
        if (mysqli_num_rows($estabelecimento_query) > 0) {
            $estabelecimento_data = mysqli_fetch_array($estabelecimento_query);
        } else {
            $estabelecimento_data = array('nome' => 'Sistema Garçom', 'perfil' => '');
        }
    }
} else {
    // Sem subdomínio, pega o primeiro estabelecimento ativo
    $estabelecimento_query = mysqli_query($db_con, "SELECT nome, perfil FROM estabelecimentos WHERE status = '1' LIMIT 1");
    if (mysqli_num_rows($estabelecimento_query) > 0) {
        $estabelecimento_data = mysqli_fetch_array($estabelecimento_query);
    } else {
        $estabelecimento_data = array('nome' => 'Sistema Garçom', 'perfil' => '');
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $estabelecimento_data['nome']; ?></title>
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="App Garçom">
    <link rel="manifest" href="manifest.json">
    
    <!-- Ícones PWA -->
    <link rel="apple-touch-icon" href="icon-192x192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icon-192x192.png">
    
    <!-- Bootstrap CSS -->
    <link href="../_cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../_cdn/lineicons/LineIcons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-estabelecimento {
            width: 80px;
            height: 80px;
            border-radius: 40px;
            object-fit: cover;
            border: 3px solid #667eea;
            margin-bottom: 15px;
        }
        
        .establishment-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            height: 50px;
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 25px 0 0 25px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 25px 25px 0;
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            border-radius: 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        
        .alert {
            border-radius: 15px;
            border: none;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="logo-container">
                        <?php if ($estabelecimento_data['perfil']) { ?>
                            <img src="<?php echo thumber($estabelecimento_data['perfil'], 150); ?>" alt="Logo" class="logo-estabelecimento">
                        <?php } else { ?>
                            <div style="width: 80px; height: 80px; background: #667eea; border-radius: 40px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 30px;">
                                <i class="lni lni-dinner"></i>
                            </div>
                        <?php } ?>
                        <div class="establishment-name"><?php echo $estabelecimento_data['nome']; ?></div>
                        <div class="login-subtitle">Sistema para Garçons</div>
                    </div>
                    
                    <?php if ($error_message) { ?>
                        <div class="alert alert-danger">
                            <i class="lni lni-warning"></i> <?php echo $error_message; ?>
                        </div>
                    <?php } ?>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-user"></i>
                            </span>
                            <input type="text" name="usuario" class="form-control" placeholder="Usuário" required autofocus>
                        </div>
                        
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-lock"></i>
                            </span>
                            <input type="password" name="senha" class="form-control" placeholder="Senha" required>
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            <i class="lni lni-enter"></i> Entrar
                        </button>
                    </form>
                    
                    <div class="footer-text">
                        Desenvolvido para facilitar o atendimento<br>
                        <strong>Sistema de Mesas</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../_cdn/jquery/jquery.min.js"></script>
    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>
    
    <script>
        // Registrar Service Worker de forma mais robusta
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('./sw.js', { scope: './' })
                    .then(function(registration) {
                        console.log('SW registrado com sucesso: ', registration.scope);
                    })
                    .catch(function(err) {
                        console.log('SW falhou ao registrar: ', err);
                        // Continuar mesmo se o SW falhar
                    });
            });
        }
    </script>
</body>
</html>
