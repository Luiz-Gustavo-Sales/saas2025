<?php
// CORE
include('../../_core/_includes/config.php');

// RESTRICT
restrict_estabelecimento();
restrict_expirado();


// SEO
$seo_subtitle = "Gerenciar Garçons";
$seo_description = "";
$seo_keywords = "";

// HEADER
$system_header .= "
<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
<link href='https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js'></script>
";
include('../../_layout/head.php');
include('../../_layout/top.php');
include('../../_layout/sidebars.php');
include('../../_layout/modal.php');

$eid = $_SESSION['estabelecimento']['id'];

// Buscar dados do estabelecimento para gerar URL específica
$estabelecimento_query = mysqli_query($db_con, "SELECT subdominio FROM estabelecimentos WHERE id = '$eid'");
$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);
$subdominio = $estabelecimento_data['subdominio'];

// Processar formulários
if ($_POST['action'] == 'add_garcom') {
    $nome = mysqli_real_escape_string($db_con, $_POST['nome']);
    $usuario = mysqli_real_escape_string($db_con, $_POST['usuario']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = mysqli_real_escape_string($db_con, $_POST['telefone']);
    
    // Verificar se o usuário já existe
    $check_sql = mysqli_query($db_con, "SELECT id FROM garcons WHERE usuario = '$usuario' AND rel_estabelecimentos_id = '$eid'");
    if (mysqli_num_rows($check_sql) == 0) {
        $sql = "INSERT INTO garcons (nome, usuario, senha, telefone, rel_estabelecimentos_id, ativo, data_criacao) 
                VALUES ('$nome', '$usuario', '$senha', '$telefone', '$eid', 1, NOW())";
        
        if (mysqli_query($db_con, $sql)) {
            echo "<script>
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Garçom cadastrado com sucesso!',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Erro!',
                text: 'Já existe um garçom com este usuário!',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        </script>";
    }
}

if ($_POST['action'] == 'edit_garcom') {
    $id = intval($_POST['id']);
    $nome = mysqli_real_escape_string($db_con, $_POST['nome']);
    $usuario = mysqli_real_escape_string($db_con, $_POST['usuario']);
    $telefone = mysqli_real_escape_string($db_con, $_POST['telefone']);
    $ativo = intval($_POST['ativo']);
    
    $sql = "UPDATE garcons SET nome = '$nome', usuario = '$usuario', telefone = '$telefone', ativo = '$ativo'";
    
    if (!empty($_POST['senha'])) {
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $sql .= ", senha = '$senha'";
    }
    
    $sql .= " WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";
    
    if (mysqli_query($db_con, $sql)) {
        echo "<script>
            Swal.fire({
                title: 'Sucesso!',
                text: 'Garçom atualizado com sucesso!',
                icon: 'success',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>";
    }
}

if ($_GET['action'] == 'delete' && $_GET['id']) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM garcons WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";
    
    if (mysqli_query($db_con, $sql)) {
        $affected_rows = mysqli_affected_rows($db_con);
        if ($affected_rows > 0) {
            echo "<script>
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Garçom excluído com sucesso!',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Erro!',
                    text: 'Garçom não encontrado ou já foi excluído!',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Erro!',
                text: 'Erro ao excluir garçom: " . mysqli_error($db_con) . "',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        </script>";
    }
}

// Buscar dados
$garcons_sql = mysqli_query($db_con, "SELECT * FROM garcons WHERE rel_estabelecimentos_id = '$eid' ORDER BY nome");

// Estatísticas
$total_garcons = mysqli_num_rows($garcons_sql);
$garcons_ativos = mysqli_num_rows(mysqli_query($db_con, "SELECT id FROM garcons WHERE rel_estabelecimentos_id = '$eid' AND ativo = 1"));
$garcons_inativos = $total_garcons - $garcons_ativos;

// Gerar URL do app para garçons específica por estabelecimento
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$app_url = $protocol . $subdominio . '.digitavitrine.com.br/garcom/';
?>

<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-radius: 12px;
    --box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.garcons-container {
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.header-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
    position: relative;
    overflow: hidden;
}

.header-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-title {
    margin: 0;
    color: var(--primary-color);
    font-size: 2.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-title i {
    color: var(--secondary-color);
}

.header-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--secondary-color), #2980b9);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, var(--success-color), #229954);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, var(--info-color), #138496);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning-color), #d68910);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger-color), #c0392b);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card.total::before { background: var(--info-color); }
.stat-card.ativos::before { background: var(--success-color); }
.stat-card.inativos::before { background: var(--danger-color); }

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-title {
    font-size: 0.9rem;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.total { background: var(--info-color); }
.stat-icon.ativos { background: var(--success-color); }
.stat-icon.inativos { background: var(--danger-color); }

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
}

.content-section {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.section-header {
    background: var(--light-color);
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-title {
    margin: 0;
    color: var(--primary-color);
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.garcons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.garcom-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: var(--transition);
    position: relative;
}

.garcom-card.ativo { border-color: var(--success-color); }
.garcom-card.inativo { border-color: var(--danger-color); }

.garcom-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow);
}

.garcom-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.garcom-avatar {
    background: var(--primary-color);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
}

.garcom-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-ativo {
    background: rgba(39, 174, 96, 0.1);
    color: var(--success-color);
}

.status-inativo {
    background: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
}

.garcom-info {
    margin-bottom: 1rem;
}

.garcom-nome {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.garcom-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.garcom-detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.garcom-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 1rem;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}

.modal-header {
    background: var(--primary-color);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.close:hover {
    background: rgba(255,255,255,0.1);
}

.modal-body {
    padding: 2rem;
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

/* Scroll personalizado para modais */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Ajustes responsivos para modais */
@media (max-width: 768px) {
    .modal-content {
        margin: 5% auto;
        width: 95%;
        max-height: 90vh;
    }
    
    .modal-body {
        max-height: calc(90vh - 200px);
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .modal-content {
        margin: 2% auto;
        width: 98%;
        max-height: 96vh;
    }
    
    .modal-body {
        max-height: calc(96vh - 150px);
        padding: 1rem;
    }
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--primary-color);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    appearance: none;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.qr-section {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.qr-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.app-url {
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
    font-family: monospace;
    word-break: break-all;
}

.info-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--box-shadow);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-card {
    background: var(--light-color);
    border-radius: 8px;
    padding: 1.5rem;
    border-left: 4px solid var(--secondary-color);
}

.info-title {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .garcons-container {
        padding: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .garcons-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }
}
</style>

<div class="garcons-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-users"></i>
                Gerenciar Garçons
            </h1>
            <div class="header-actions">
                <a href="../mesas/" class="btn btn-primary">
                    <i class="fas fa-chair"></i>
                    Voltar às Mesas
                </a>
                <button onclick="openModal('modalAddGarcom')" class="btn btn-success">
                    <i class="fas fa-user-plus"></i>
                    Novo Garçom
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-header">
                <span class="stat-title">Total de Garçons</span>
                <div class="stat-icon total">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $total_garcons; ?></h3>
        </div>
        
        <div class="stat-card ativos">
            <div class="stat-header">
                <span class="stat-title">Garçons Ativos</span>
                <div class="stat-icon ativos">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $garcons_ativos; ?></h3>
        </div>
        
        <div class="stat-card inativos">
            <div class="stat-header">
                <span class="stat-title">Garçons Inativos</span>
                <div class="stat-icon inativos">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $garcons_inativos; ?></h3>
        </div>
    </div>

    <!-- App Download Section -->
    <div class="qr-section">
        <h2 class="qr-title">
            <i class="fas fa-mobile-alt"></i>
            App para Garçons
        </h2>
        <p>Compartilhe este link com seus garçons para acessarem o sistema:</p>
        <div class="app-url"><?php echo $app_url; ?></div>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
            <button onclick="copyAppUrl()" class="btn btn-success">
                <i class="fas fa-copy"></i>
                Copiar Link
            </button>
            <button onclick="shareApp()" class="btn btn-info">
                <i class="fas fa-share"></i>
                Compartilhar
            </button>
            <button onclick="generateQR()" class="btn btn-warning">
                <i class="fas fa-qrcode"></i>
                Gerar QR Code
            </button>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
            <i class="fas fa-info-circle"></i>
            Como funciona o acesso dos garçons
        </h3>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-user-plus"></i>
                    Cadastro
                </div>
                <p>Cadastre seus garçons com nome, usuário único e senha de acesso.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-mobile"></i>
                    Acesso Mobile
                </div>
                <p>Garçons acessam via navegador móvel usando o link/QR Code fornecido.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-lock"></i>
                    Segurança
                </div>
                <p>Login por subdomínio garante que só acessem dados do seu estabelecimento.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-table"></i>
                    Gestão de Mesas
                </div>
                <p>Cada garçom pode gerenciar pedidos das mesas sob sua responsabilidade.</p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-user-tie"></i>
                Garçons Cadastrados (<?php echo $total_garcons; ?>)
            </h2>
        </div>

        <?php if ($total_garcons > 0): ?>
            <div class="garcons-grid">
                <?php 
                // Reset query
                $garcons_sql = mysqli_query($db_con, "SELECT * FROM garcons WHERE rel_estabelecimentos_id = '$eid' ORDER BY nome");
                while ($garcom = mysqli_fetch_array($garcons_sql)): 
                ?>
                    <div class="garcom-card <?php echo $garcom['ativo'] ? 'ativo' : 'inativo'; ?>">
                        <div class="garcom-header">
                            <div class="garcom-avatar">
                                <?php echo strtoupper(substr($garcom['nome'], 0, 2)); ?>
                            </div>
                            <span class="garcom-status status-<?php echo $garcom['ativo'] ? 'ativo' : 'inativo'; ?>">
                                <?php echo $garcom['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </div>
                        
                        <div class="garcom-info">
                            <div class="garcom-nome"><?php echo htmlclean($garcom['nome']); ?></div>
                            <div class="garcom-details">
                                <div class="garcom-detail">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlclean($garcom['usuario']); ?></span>
                                </div>
                                <?php if ($garcom['telefone']): ?>
                                    <div class="garcom-detail">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlclean($garcom['telefone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="garcom-detail">
                                    <i class="fas fa-calendar"></i>
                                    <span>Cadastrado em <?php echo databr($garcom['data_criacao']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="garcom-actions">
                            <button onclick="editGarcom(<?php echo $garcom['id']; ?>, '<?php echo addslashes($garcom['nome']); ?>', '<?php echo addslashes($garcom['usuario']); ?>', '<?php echo addslashes($garcom['telefone']); ?>', <?php echo $garcom['ativo']; ?>)" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button onclick="deleteGarcom(<?php echo $garcom['id']; ?>, '<?php echo addslashes($garcom['nome']); ?>')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                                Excluir
                            </button>
                            <button onclick="sendAppLink('<?php echo htmlclean($garcom['telefone']); ?>', '<?php echo addslashes($garcom['nome']); ?>')" class="btn btn-info btn-sm">
                                <i class="fas fa-paper-plane"></i>
                                Enviar App
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-plus"></i>
                <h3>Nenhum garçom cadastrado ainda</h3>
                <p>Adicione o primeiro garçom para começar a usar o sistema de mesas.</p>
                <button onclick="openModal('modalAddGarcom')" class="btn btn-success">
                    <i class="fas fa-user-plus"></i>
                    Cadastrar Primeiro Garçom
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adicionar Garçom -->
<div id="modalAddGarcom" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-plus"></i>
                Cadastrar Novo Garçom
            </h3>
            <button type="button" class="close" onclick="closeModal('modalAddGarcom')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_garcom">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Nome Completo *
                    </label>
                    <input type="text" name="nome" class="form-control" required placeholder="Ex: João da Silva">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-at"></i>
                        Nome de Usuário *
                    </label>
                    <input type="text" name="usuario" class="form-control" required placeholder="Ex: joao.silva (sem espaços)">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Senha *
                    </label>
                    <input type="password" name="senha" class="form-control" required placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-phone"></i>
                        Telefone/WhatsApp
                    </label>
                    <input type="text" name="telefone" class="form-control" placeholder="Ex: (11) 99999-9999">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalAddGarcom')" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Cadastrar Garçom
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Garçom -->
<div id="modalEditGarcom" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-edit"></i>
                Editar Garçom
            </h3>
            <button type="button" class="close" onclick="closeModal('modalEditGarcom')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_garcom">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Nome Completo *
                    </label>
                    <input type="text" name="nome" id="edit_nome" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-at"></i>
                        Nome de Usuário *
                    </label>
                    <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Nova Senha
                    </label>
                    <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter a atual">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-phone"></i>
                        Telefone/WhatsApp
                    </label>
                    <input type="text" name="telefone" id="edit_telefone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status
                    </label>
                    <select name="ativo" id="edit_ativo" class="form-control form-select">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEditGarcom')" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i>
                    Atualizar Garçom
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Garantir que SweetAlert2 esteja carregado
if (typeof Swal === 'undefined') {
    console.log('SweetAlert2 não encontrado, carregando novamente...');
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js';
    document.head.appendChild(script);
    
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css';
    document.head.appendChild(link);
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editGarcom(id, nome, usuario, telefone, ativo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nome').value = nome;
    document.getElementById('edit_usuario').value = usuario;
    document.getElementById('edit_telefone').value = telefone;
    document.getElementById('edit_ativo').value = ativo;
    openModal('modalEditGarcom');
}

function deleteGarcom(id, nome) {
    console.log('deleteGarcom chamada:', id, nome); // Debug
    
    // Verificar se SweetAlert2 está carregado
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Exclusão',
            text: `Tem certeza que deseja excluir o garçom "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `index.php?action=delete&id=${id}`;
            }
        });
    } else {
        // Fallback usando confirm() nativo do navegador
        if (confirm(`Tem certeza que deseja excluir o garçom "${nome}"?`)) {
            window.location.href = `index.php?action=delete&id=${id}`;
        }
    }
}

function copyAppUrl() {
    const url = '<?php echo $app_url; ?>';
    navigator.clipboard.writeText(url).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Copiado!',
                text: 'Link do app copiado para área de transferência',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Link copiado para área de transferência!');
        }
    }).catch(() => {
        // Fallback se clipboard não funcionar
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Link do App',
                text: url,
                icon: 'info'
            });
        } else {
            prompt('Copie o link:', url);
        }
    });
}

function shareApp() {
    const url = '<?php echo $app_url; ?>';
    const text = 'Acesse o app para garçons do nosso estabelecimento:';
    
    if (navigator.share) {
        navigator.share({
            title: 'App Garçom',
            text: text,
            url: url
        });
    } else {
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
        window.open(whatsappUrl, '_blank');
    }
}

function generateQR() {
    const url = '<?php echo $app_url; ?>';
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'QR Code do App',
            html: `
                <div style="text-align: center;">
                    <img src="${qrUrl}" alt="QR Code" style="max-width: 100%; height: auto; margin: 20px 0;">
                    <p>Escaneie este código para acessar o app</p>
                </div>
            `,
            width: 400,
            showConfirmButton: true,
            confirmButtonText: 'Fechar'
        });
    } else {
        // Fallback - abrir QR Code em nova janela
        window.open(qrUrl, '_blank');
    }
}

function sendAppLink(telefone, nome) {
    if (!telefone) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Telefone não cadastrado',
                text: 'Adicione o telefone do garçom para enviar o link',
                icon: 'warning'
            });
        } else {
            alert('Telefone não cadastrado! Adicione o telefone do garçom para enviar o link.');
        }
        return;
    }
    
    const url = '<?php echo $app_url; ?>';
    const message = `Olá ${nome}! Acesse o app do garçom através deste link: ${url}`;
    const whatsappUrl = `https://wa.me/${telefone.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`;
    
    window.open(whatsappUrl, '_blank');
}

// Fechar modal clicando fora
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Adicionar animação aos cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.garcom-card, .stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<?php include('../../_layout/rdp.php'); ?>
<?php include('../../_layout/footer.php'); ?>
