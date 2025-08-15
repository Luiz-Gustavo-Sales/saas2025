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
    <title>Meu Perfil - <?php echo $estabelecimento_data['nome']; ?></title>
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
            max-width: 600px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            border: 3px solid white;
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .profile-role {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .profile-info {
            padding: 2rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.2rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
        }
        
        .estabelecimento-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .estabelecimento-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .estabelecimento-logo {
            width: 60px;
            height: 60px;
            border-radius: 30px;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }
        
        .estabelecimento-info h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.3rem;
        }
        
        .estabelecimento-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .app-content {
                padding: 1rem 0.5rem;
            }
            
            .profile-info {
                padding: 1.5rem;
            }
            
            .estabelecimento-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-info">
            <h1><i class="lni lni-user"></i> Meu Perfil</h1>
        </div>
        <a href="index.php" class="back-btn">
            <i class="lni lni-arrow-left"></i> Voltar
        </a>
    </header>
    
    <main class="app-content">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="lni lni-user"></i>
                </div>
                <div class="profile-name"><?php echo $garcom_data['nome']; ?></div>
                <div class="profile-role">Garçom</div>
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="lni lni-user"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Usuário</div>
                        <div class="info-value"><?php echo $garcom_data['usuario']; ?></div>
                    </div>
                </div>
                
                <?php if (!empty($garcom_data['telefone'])) { ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="lni lni-phone"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Telefone</div>
                        <div class="info-value"><?php echo $garcom_data['telefone']; ?></div>
                    </div>
                </div>
                <?php } ?>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="lni lni-checkmark-circle"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Status</div>
                        <div class="info-value" style="color: var(--success-color);">Ativo</div>
                    </div>
                </div>
                
                <?php if (!empty($garcom_data['data_cadastro'])) { ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="lni lni-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Cadastrado em</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($garcom_data['data_cadastro'])); ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <div class="estabelecimento-card">
            <div class="estabelecimento-header">
                <?php if ($estabelecimento_data['perfil']) { ?>
                    <img src="<?php echo thumber($estabelecimento_data['perfil'], 120); ?>" alt="Logo" class="estabelecimento-logo">
                <?php } else { ?>
                    <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 30px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; border: 2px solid var(--primary-color);">
                        <i class="lni lni-dinner"></i>
                    </div>
                <?php } ?>
                <div class="estabelecimento-info">
                    <h3><?php echo $estabelecimento_data['nome']; ?></h3>
                    <p>Estabelecimento onde você trabalha</p>
                </div>
            </div>
            
            <div class="stats-grid">
                <?php
                // Buscar estatísticas
                $total_mesas = mysqli_num_rows(mysqli_query($db_con, "SELECT id FROM mesas WHERE rel_estabelecimentos_id = '".$estabelecimento_data['id']."'"));
                $total_garcons = mysqli_num_rows(mysqli_query($db_con, "SELECT id FROM garcons WHERE rel_estabelecimentos_id = '".$estabelecimento_data['id']."' AND ativo = 1"));
                ?>
                
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_mesas; ?></div>
                    <div class="stat-label">Mesas</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_garcons; ?></div>
                    <div class="stat-label">Garçons</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number"><?php echo date('d'); ?></div>
                    <div class="stat-label">Dia Atual</div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../_cdn/jquery/jquery.min.js"></script>
    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
