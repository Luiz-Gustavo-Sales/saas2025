<?php

// Incluir configurações

session_start();

include('../_core/_includes/config.php');



// Verificar se garçom está logado

if (!isset($_SESSION['garcom_id'])) {

    header("Location: login.php");

    exit;

}



// Buscar dados básicos

$garcom_id = $_SESSION['garcom_id'];

$estabelecimento_id = $_SESSION['garcom_estabelecimento_id'];



// Verificar se o garçom ainda é válido

$garcom_check = mysqli_query($db_con, "SELECT nome FROM garcons WHERE id = '$garcom_id' AND ativo = 1");

if (mysqli_num_rows($garcom_check) == 0) {

    session_destroy();

    header("Location: login.php");

    exit;

}

$garcom_data = mysqli_fetch_array($garcom_check);



// Buscar dados do estabelecimento

$estabelecimento_query = mysqli_query($db_con, "SELECT nome, perfil FROM estabelecimentos WHERE id = '$estabelecimento_id' AND status = '1'");

if (mysqli_num_rows($estabelecimento_query) == 0) {

    session_destroy();

    header("Location: login.php");

    exit;

}

$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);



// Buscar todas as mesas do estabelecimento

$mesas_query = mysqli_query($db_con, "SELECT m.*, 

    (SELECT COUNT(*) FROM mesa_pedido_itens mpi 

     INNER JOIN mesa_pedidos mp ON mpi.rel_mesa_pedidos_id = mp.id 

     WHERE mp.rel_mesas_id = m.id AND mp.status = 'andamento') as total_itens,

    (SELECT SUM(mpi.preco_unitario * mpi.quantidade) FROM mesa_pedido_itens mpi 

     INNER JOIN mesa_pedidos mp ON mpi.rel_mesa_pedidos_id = mp.id 

     WHERE mp.rel_mesas_id = m.id AND mp.status = 'andamento') as valor_total,

    (SELECT mp.id FROM mesa_pedidos mp WHERE mp.rel_mesas_id = m.id AND mp.status = 'andamento' LIMIT 1) as pedido_id

    FROM mesas m 

    WHERE m.rel_estabelecimentos_id = '$estabelecimento_id' AND m.status = '1'

    ORDER BY m.numero");



// Estatísticas básicas

$total_mesas_query = mysqli_query($db_con, "SELECT COUNT(*) as total FROM mesas WHERE rel_estabelecimentos_id = '$estabelecimento_id' AND status = '1'");

$total_mesas = mysqli_fetch_array($total_mesas_query)['total'];



$mesas_ocupadas_query = mysqli_query($db_con, "SELECT COUNT(DISTINCT rel_mesas_id) as ocupadas FROM mesa_pedidos WHERE rel_estabelecimentos_id = '$estabelecimento_id' AND status = 'andamento'");

$mesas_ocupadas = mysqli_fetch_array($mesas_ocupadas_query)['ocupadas'];



$mesas_livres = $total_mesas - $mesas_ocupadas;

?>



<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>App Garçom - <?php echo $estabelecimento_data['nome']; ?></title>

    <link rel="manifest" href="manifest.json">

    <meta name="theme-color" content="#667eea">

    <link rel="manifest" href="/manifest.json">

    <meta name="apple-mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <meta name="apple-mobile-web-app-title" content="App Garçom">

    <meta name="mobile-web-app-capable" content="yes">

    <meta name="msapplication-TileColor" content="#667eea">

    <meta name="msapplication-config" content="browserconfig.xml">

    

    <!-- Ícones para iOS -->

    <link rel="apple-touch-icon" href="icon-192x192.png">

    <link rel="apple-touch-icon" sizes="152x152" href="icon-152x152.png">

    <link rel="apple-touch-icon" sizes="180x180" href="icon-192x192.png">

    

    <!-- Ícone para Android -->

    <link rel="icon" type="image/png" sizes="192x192" href="icon-192x192.png">

    

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

            --info-color: #17a2b8;

        }

        

        body {

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);

            min-height: 100vh;

            color: #333;

            margin: 0;

            padding: 0;

        }

        

        .app-container {

            min-height: 100vh;

            display: flex;

            flex-direction: column;

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

        

        .estabelecimento-logo {

            width: 50px;

            height: 50px;

            border-radius: 25px;

            object-fit: cover;

            border: 2px solid white;

        }

        

        .estabelecimento-info h1 {

            font-size: 1.2rem;

            margin: 0;

            font-weight: 600;

        }

        

        .estabelecimento-info .garcom-name {

            font-size: 0.9rem;

            opacity: 0.9;

            margin: 0;

        }

        

        .logout-btn {

            background: rgba(255,255,255,0.2);

            border: 1px solid rgba(255,255,255,0.3);

            color: white;

            padding: 0.5rem 1rem;

            border-radius: 25px;

            text-decoration: none;

            font-size: 0.9rem;

            transition: all 0.3s ease;

        }

        

        .logout-btn:hover {

            background: rgba(255,255,255,0.3);

            color: white;

            text-decoration: none;

        }

        

        .app-content {

            flex: 1;

            padding: 1rem;

        }

        

        .stats-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));

            gap: 1rem;

            margin-bottom: 2rem;

        }

        

        .stat-card {

            background: white;

            border-radius: 12px;

            padding: 1.5rem;

            text-align: center;

            box-shadow: 0 4px 20px rgba(0,0,0,0.1);

            transition: transform 0.3s ease;

        }

        

        .stat-card:hover {

            transform: translateY(-5px);

        }

        

        .stat-icon {

            font-size: 2.5rem;

            margin-bottom: 0.5rem;

        }

        

        .stat-number {

            font-size: 2rem;

            font-weight: bold;

            margin: 0;

        }

        

        .stat-label {

            color: #666;

            font-size: 0.9rem;

            margin: 0;

        }

        

        .actions-section {

            background: white;

            border-radius: 12px;

            padding: 2rem;

            box-shadow: 0 4px 20px rgba(0,0,0,0.1);

        }

        

        .actions-title {

            font-size: 1.5rem;

            margin-bottom: 1.5rem;

            color: var(--primary-color);

            font-weight: 600;

        }

        

        .mesas-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));

            gap: 1rem;

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

            text-decoration: none;

            color: inherit;

        }

        

        .mesa-card:hover {

            transform: translateY(-5px);

            box-shadow: 0 8px 25px rgba(0,0,0,0.15);

            text-decoration: none;

            color: inherit;

        }

        

        .mesa-card.livre {

            border-color: var(--success-color);

        }

        

        .mesa-card.ocupada {

            border-color: var(--danger-color);

            background: linear-gradient(135deg, #ffe6e6, #fff0f0);

        }

        

        .mesa-numero {

            font-size: 1.5rem;

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

            display: flex;

            flex-direction: column;

            gap: 0.5rem;

        }

        

        .status-badge {

            padding: 0.3rem 0.8rem;

            border-radius: 20px;

            font-size: 0.8rem;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 0.5px;

        }

        

        .status-badge.livre {

            background: var(--success-color);

            color: white;

        }

        

        .status-badge.ocupada {

            background: var(--danger-color);

            color: white;

        }

        

        .mesa-info {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 0.5rem;

        }

        

        .mesa-info small {

            font-size: 0.8rem;

            color: #666;

        }

        

        .no-mesas {

            grid-column: 1 / -1;

            text-align: center;

            padding: 2rem;

            color: #666;

        }

        

        .no-mesas p {

            font-size: 1.1rem;

            margin-bottom: 0.5rem;

        }

        

        .lni-spinning {

            animation: spin 1s linear infinite;

        }

        

        @keyframes spin {

            from { transform: rotate(0deg); }

            to { transform: rotate(360deg); }

        }

        

        @media (max-width: 768px) {

            .header-info {

                gap: 0.5rem;

            }

            

            .estabelecimento-logo {

                width: 40px;

                height: 40px;

            }

            

            .estabelecimento-info h1 {

                font-size: 1rem;

            }

            

            .estabelecimento-info .garcom-name {

                font-size: 0.8rem;

            }

            

            .logout-btn {

                padding: 0.4rem 0.8rem;

                font-size: 0.8rem;

            }

            

            .stats-grid {

                grid-template-columns: repeat(2, 1fr);

            }

            

            .mesas-grid {

                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));

            }

            

            .mesa-card {

                padding: 1rem;

            }

            

            .mesa-numero {

                font-size: 1.2rem;

            }

        }

    </style>

</head>

<body>

    <div class="app-container">

        <header class="app-header">

            <div class="header-info">

                <?php if ($estabelecimento_data['perfil']) { ?>

                    <img src="<?php echo thumber($estabelecimento_data['perfil'], 100); ?>" alt="Logo" class="estabelecimento-logo">

                <?php } else { ?>

                    <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 25px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; border: 2px solid white;">

                        <i class="lni lni-dinner"></i>

                    </div>

                <?php } ?>

                <div class="estabelecimento-info">

                    <h1><?php echo $estabelecimento_data['nome']; ?></h1>

                    <p class="garcom-name">Olá, <?php echo $garcom_data['nome']; ?>!</p>

                </div>

            </div>

            <a href="logout.php" class="logout-btn">

                <i class="lni lni-exit"></i> Sair

            </a>

        </header>

        

        <main class="app-content">

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-icon">🍽️</div>

                    <p class="stat-number"><?php echo $total_mesas; ?></p>

                    <p class="stat-label">Total de Mesas</p>

                </div>

                

                <div class="stat-card">

                    <div class="stat-icon">🔴</div>

                    <p class="stat-number" style="color: var(--danger-color);"><?php echo $mesas_ocupadas; ?></p>

                    <p class="stat-label">Mesas Ocupadas</p>

                </div>

                

                <div class="stat-card">

                    <div class="stat-icon">🟢</div>

                    <p class="stat-number" style="color: var(--success-color);"><?php echo $mesas_livres; ?></p>

                    <p class="stat-label">Mesas Livres</p>

                </div>

            </div>

            

            <div class="actions-section">

                <h2 class="actions-title">Mesas do Estabelecimento</h2>

                <div class="mesas-grid">

                    <?php 

                    if (mysqli_num_rows($mesas_query) > 0) {

                        while ($mesa = mysqli_fetch_array($mesas_query)) {

                            $mesa_ocupada = $mesa['total_itens'] > 0;

                            $valor_total = $mesa['valor_total'] ?? 0;

                    ?>

                    <a href="mesa.php?id=<?php echo $mesa['id']; ?>" class="mesa-card <?php echo $mesa_ocupada ? 'ocupada' : 'livre'; ?>">

                        <div class="mesa-numero <?php echo $mesa_ocupada ? 'ocupada' : 'livre'; ?>">

                            Mesa <?php echo $mesa['numero']; ?>

                        </div>

                        <div class="mesa-status">

                            <?php if ($mesa_ocupada) { ?>

                                <span class="status-badge ocupada">OCUPADA</span>

                                <div class="mesa-info">

                                    <small><?php echo $mesa['total_itens']; ?> item(s)</small>

                                    <small>R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></small>

                                </div>

                            <?php } else { ?>

                                <span class="status-badge livre">LIVRE</span>

                                <div class="mesa-info">

                                    <small>Toque para atender</small>

                                </div>

                            <?php } ?>

                        </div>

                    </a>

                    <?php 

                        }

                    } else { 

                    ?>

                    <div class="no-mesas">

                        <p>Nenhuma mesa cadastrada no estabelecimento.</p>

                        <small>Entre em contato com o administrador para cadastrar mesas.</small>

                    </div>

                    <?php } ?>

                </div>

            </div>

        </main>

        

        <!-- Botões de Instalação PWA -->

        <div class="install-buttons" style="display: flex; justify-content: center; align-items: center; margin: 20px 0; gap: 10px;">

            <button id="installButton" class="btn btn-primary btn-sm" style="display: none;">

                <i class="lni lni-download"></i> Instalar App (Android)

            </button>

            <button id="showInstructionsButton" class="btn btn-info btn-sm" style="display: none;">

                <i class="lni lni-mobile"></i> Instalar App (iOS)

            </button>

        </div>



        <!-- Popup de Instruções iOS -->

        <div id="instructionsPopup" style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); z-index: 1050; align-items: center; justify-content: center;">

            <div style="background-color: white; padding: 25px; border-radius: 8px; max-width: 450px; margin: 20px auto; position: relative; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">

                <button id="closePopupButton" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 2em; color: #888; line-height: 1;">&times;</button>

                <h4 style="margin-top: 0; margin-bottom: 15px; font-size: 1.3em; color: #333;">📱 Instruções para iOS</h4>

                <p style="margin-bottom: 15px; color: #555;">Para adicionar este app à sua Tela de Início:</p>

                <ol style="padding-left: 20px; margin-bottom: 0; color: #555;">

                    <li style="margin-bottom: 10px;">Toque no botão de <strong>Compartilhar</strong> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16" style="vertical-align: -2px;"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708l3-3z"/></svg> (na barra inferior do Safari).</li>

                    <li style="margin-bottom: 10px;">Role para baixo e toque em "<strong>Adicionar à Tela de Início</strong>".</li>

                    <li>Confirme tocando em "<strong>Adicionar</strong>" no canto superior direito.</li>

                </ol>

            </div>

        </div>

        <!-- Fim Popup -->

    </div>

    

    <script src="../_cdn/jquery/jquery.min.js"></script>

    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>

    <script>
    // Registra o Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw2.js')
            .then(registration => {
            console.log('ServiceWorker registrado com sucesso:', registration.scope);
            })
            .catch(error => {
            console.log('Falha ao registrar ServiceWorker:', error);
            });
        });
    }

    // Botões e variáveis
    const installButton = document.getElementById('installButton');
    const showInstructionsButton = document.getElementById('showInstructionsButton');
    const instructionsPopup = document.getElementById('instructionsPopup');
    const closePopupButton = document.getElementById('closePopupButton');
    let deferredPrompt;

    // Instalação Android
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installButton.style.display = 'inline-block';
    });

    installButton.addEventListener('click', async () => {
        if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`Usuário ${outcome} a instalação`);
        deferredPrompt = null;
        } else if (isAppleDevice()) {
        // Se for iOS, exibe popup de instruções
        if (instructionsPopup) instructionsPopup.style.display = 'flex';
        }
        installButton.style.display = 'none';
    });

    // Detectar iOS e exibir botão de instrução
    function isAppleDevice() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) ||
            (navigator.userAgent.includes('Macintosh') && 'ontouchend' in document);
    }

    function isPwaInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone;
    }

    // Exibir botão iOS se necessário
    document.addEventListener('DOMContentLoaded', () => {
        const isStandalone = isPwaInstalled();

        if (isStandalone) {
        // App já está instalado
        installButton.style.display = 'none';
        if (showInstructionsButton) showInstructionsButton.style.display = 'none';
        } else {
        if (isAppleDevice() && showInstructionsButton) {
            showInstructionsButton.style.display = 'inline-block';
        }
        }

        // Abrir popup de instruções iOS
        if (showInstructionsButton && instructionsPopup) {
        showInstructionsButton.addEventListener('click', () => {
            instructionsPopup.style.display = 'flex';
        });
        }

        // Fechar popup
        if (closePopupButton && instructionsPopup) {
        closePopupButton.addEventListener('click', () => {
            instructionsPopup.style.display = 'none';
        });

        // Fechar clicando fora
        instructionsPopup.addEventListener('click', (e) => {
            if (e.target === instructionsPopup) {
            instructionsPopup.style.display = 'none';
            }
        });
        }
    });

    // Evento de instalação
    window.addEventListener('appinstalled', () => {
        installButton.style.display = 'none';
        if (showInstructionsButton) showInstructionsButton.style.display = 'none';
        console.log('PWA instalado com sucesso');
    });
    </script>




    <style>

        /* CSS para animação de carregamento */

        @keyframes spin {

            0% { transform: rotate(0deg); }

            100% { transform: rotate(360deg); }

        }

    </style>

</body>

</html>

