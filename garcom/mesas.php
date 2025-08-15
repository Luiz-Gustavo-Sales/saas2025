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

// Buscar mesas do estabelecimento
$mesas_query = mysqli_query($db_con, "SELECT * FROM mesas WHERE rel_estabelecimentos_id = '".$estabelecimento_data['id']."' ORDER BY numero");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesas - <?php echo $estabelecimento_data['nome']; ?></title>
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
        
        .header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        
        .mesas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .mesa-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }
        
        .mesa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .mesa-card.livre {
            border-color: var(--success-color);
        }
        
        .mesa-card.ocupada {
            border-color: var(--danger-color);
            background: linear-gradient(135deg, #ffe6e6, #fff0f0);
        }
        
        .mesa-numero {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .mesa-numero.livre {
            color: var(--success-color);
        }
        
        .mesa-numero.ocupada {
            color: var(--danger-color);
        }
        
        .mesa-status {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-bottom: 1rem;
        }
        
        .mesa-status.livre {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }
        
        .mesa-status.ocupada {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .mesa-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .btn-ocupar {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-liberar {
            background: var(--success-color);
            color: white;
        }
        
        .btn-action:hover {
            text-decoration: none;
            color: white;
            transform: scale(1.05);
        }
        
        .stats-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            text-align: center;
        }
        
        .stat-item {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .mesas-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .mesa-card {
                padding: 1rem;
            }
            
            .mesa-numero {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-info">
            <h1><i class="lni lni-dinner"></i> Mesas - <?php echo $estabelecimento_data['nome']; ?></h1>
        </div>
        <a href="index.php" class="back-btn">
            <i class="lni lni-arrow-left"></i> Voltar
        </a>
    </header>
    
    <main class="app-content">
        <?php
        // Calcular estatísticas
        $total_mesas = mysqli_num_rows($mesas_query);
        $mesas_ocupadas = 0;
        
        // Reset query
        mysqli_data_seek($mesas_query, 0);
        while ($mesa = mysqli_fetch_array($mesas_query)) {
            if ($mesa['status'] == 1) {
                $mesas_ocupadas++;
            }
        }
        
        $mesas_livres = $total_mesas - $mesas_ocupadas;
        ?>
        
        <div class="stats-header">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" style="color: var(--primary-color);"><?php echo $total_mesas; ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: var(--success-color);"><?php echo $mesas_livres; ?></div>
                    <div class="stat-label">Livres</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: var(--danger-color);"><?php echo $mesas_ocupadas; ?></div>
                    <div class="stat-label">Ocupadas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: var(--info-color);"><?php echo $total_mesas > 0 ? round(($mesas_livres / $total_mesas) * 100) : 0; ?>%</div>
                    <div class="stat-label">Disponibilidade</div>
                </div>
            </div>
        </div>
        
        <div class="mesas-grid">
            <?php
            // Reset query para mostrar mesas
            mysqli_data_seek($mesas_query, 0);
            while ($mesa = mysqli_fetch_array($mesas_query)) {
                $status_class = $mesa['status'] == 1 ? 'ocupada' : 'livre';
                $status_text = $mesa['status'] == 1 ? 'Ocupada' : 'Livre';
                ?>
                <div class="mesa-card <?php echo $status_class; ?>" onclick="toggleMesa(<?php echo $mesa['id']; ?>, <?php echo $mesa['status']; ?>)">
                    <div class="mesa-numero <?php echo $status_class; ?>">
                        Mesa <?php echo $mesa['numero']; ?>
                    </div>
                    <div class="mesa-status <?php echo $status_class; ?>">
                        <?php echo $status_text; ?>
                    </div>
                    <div class="mesa-actions">
                        <?php if ($mesa['status'] == 0) { ?>
                            <button class="btn-action btn-ocupar" onclick="event.stopPropagation(); changeMesaStatus(<?php echo $mesa['id']; ?>, 1)">
                                <i class="lni lni-lock"></i> Ocupar
                            </button>
                        <?php } else { ?>
                            <button class="btn-action btn-liberar" onclick="event.stopPropagation(); changeMesaStatus(<?php echo $mesa['id']; ?>, 0)">
                                <i class="lni lni-unlock"></i> Liberar
                            </button>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            
            if ($total_mesas == 0) {
                echo "<div style='grid-column: 1 / -1; text-align: center; padding: 2rem; background: white; border-radius: 12px; color: #666;'>";
                echo "<i class='lni lni-dinner' style='font-size: 3rem; margin-bottom: 1rem; color: #ddd;'></i><br>";
                echo "Nenhuma mesa cadastrada ainda.";
                echo "</div>";
            }
            ?>
        </div>
    </main>
    
    <script src="../_cdn/jquery/jquery.min.js"></script>
    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function toggleMesa(mesaId, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            changeMesaStatus(mesaId, newStatus);
        }
        
        function changeMesaStatus(mesaId, newStatus) {
            const statusText = newStatus == 1 ? 'ocupar' : 'liberar';
            
            Swal.fire({
                title: 'Confirmar ação',
                text: `Deseja ${statusText} esta mesa?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aqui você pode implementar a chamada AJAX para atualizar o status
                    // Por enquanto vamos simular com reload da página
                    
                    // Simulação de sucesso
                    Swal.fire({
                        title: 'Sucesso!',
                        text: `Mesa ${statusText}da com sucesso!`,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Aqui você implementaria a atualização real via AJAX
                        // window.location.reload();
                        console.log(`Mesa ${mesaId} ${statusText}da`);
                    });
                }
            });
        }
        
        // Atualizar status a cada 30 segundos
        setInterval(function() {
            // Implementar atualização automática via AJAX
            console.log('Verificando atualizações das mesas...');
        }, 30000);
    </script>
</body>
</html>
