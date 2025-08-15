<?php
// CORE
include('../../_core/_includes/config.php');
include('../../_core/_includes/functions/mesas.php');

// RESTRICT
restrict_estabelecimento();
restrict_expirado();

// SEO
$seo_subtitle = "Sistema de Mesas";
$seo_description = "";
$seo_keywords = "";

// HEADER
$system_header .= "
<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
<link href='https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js'></script>
";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');

$eid = $_SESSION['estabelecimento']['id'];

// Buscar dados do estabelecimento para gerar URL específica
$estabelecimento_query = mysqli_query($db_con, "SELECT subdominio FROM estabelecimentos WHERE id = '$eid'");
$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);
$subdominio = $estabelecimento_data['subdominio'];

// Processar formulários
if ($_POST['action'] == 'add_mesa') {
    $numero = mysqli_real_escape_string($db_con, $_POST['numero']);
    $nome = mysqli_real_escape_string($db_con, $_POST['nome']);
    
    // Verificar se o número já existe
    $check_sql = mysqli_query($db_con, "SELECT id FROM mesas WHERE numero = '$numero' AND rel_estabelecimentos_id = '$eid'");
    if (mysqli_num_rows($check_sql) == 0) {
        $sql = "INSERT INTO mesas (numero, nome, rel_estabelecimentos_id, status, created) 
                VALUES ('$numero', '$nome', '$eid', '1', NOW())";
        
        if (mysqli_query($db_con, $sql)) {
            echo "<script>
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Mesa cadastrada com sucesso!',
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
                text: 'Já existe uma mesa com este número!',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        </script>";
    }
}

if ($_POST['action'] == 'edit_mesa') {
    $id = intval($_POST['id']);
    $numero = mysqli_real_escape_string($db_con, $_POST['numero']);
    $nome = mysqli_real_escape_string($db_con, $_POST['nome']);
    $status = mysqli_real_escape_string($db_con, $_POST['status']);
    
    $sql = "UPDATE mesas SET numero = '$numero', nome = '$nome', status = '$status'
            WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";
    
    if (mysqli_query($db_con, $sql)) {
        echo "<script>
            Swal.fire({
                title: 'Sucesso!',
                text: 'Mesa atualizada com sucesso!',
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
    $sql = "DELETE FROM mesas WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";
    
    if (mysqli_query($db_con, $sql)) {
        echo "<script>
            Swal.fire({
                title: 'Sucesso!',
                text: 'Mesa excluída com sucesso!',
                icon: 'success',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>";
    }
}

// Buscar dados
$mesas_sql = mysqli_query($db_con, "SELECT * FROM mesas WHERE rel_estabelecimentos_id = '$eid' ORDER BY numero");

// Estatísticas
$total_mesas = mysqli_num_rows($mesas_sql);
$mesas_livres = mysqli_num_rows(mysqli_query($db_con, "SELECT id FROM mesas WHERE rel_estabelecimentos_id = '$eid' AND status = '1'"));
$mesas_ocupadas = mysqli_num_rows(mysqli_query($db_con, "SELECT id FROM mesas WHERE rel_estabelecimentos_id = '$eid' AND status = '0'"));

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

.mesas-container {
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
.stat-card.ativas::before { background: var(--success-color); }
.stat-card.inativas::before { background: var(--danger-color); }
.stat-card.reservadas::before { background: var(--danger-color); }

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
.stat-icon.ativas { background: var(--success-color); }
.stat-icon.inativas { background: var(--danger-color); }
.stat-icon.reservadas { background: var(--danger-color); }

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
    justify-content: between;
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

.mesas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.mesa-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: var(--transition);
    position: relative;
}

.mesa-card.ativa { border-color: var(--success-color); }
.mesa-card.inativa { border-color: var(--danger-color); }
.mesa-card.reservada { border-color: var(--danger-color); }

.mesa-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--box-shadow);
}

.mesa-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.mesa-numero {
    background: var(--primary-color);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.mesa-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-ativa {
    background: rgba(39, 174, 96, 0.1);
    color: var(--success-color);
}

.status-inativa {
    background: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
}

.status-reservada {
    background: rgba(231, 76, 60, 0.1);
    color: var(--danger-color);
}

.mesa-info {
    margin-bottom: 1rem;
}

.mesa-nome {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.mesa-details {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.mesa-actions {
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

@media (max-width: 768px) {
    .mesas-container {
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
    
    .mesas-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }
}
</style>

<div class="mesas-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-chair"></i>
                Sistema de Mesas
            </h1>
            <div class="header-actions">
                <a href="<?php panel_url(); ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Painel
                </a>
                <button onclick="openModal('modalAddMesa')" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Nova Mesa
                </button>
                <a href="../garcons/" class="btn btn-info">
                    <i class="fas fa-users"></i>
                    Gerenciar Garçons
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-header">
                <span class="stat-title">Total de Mesas</span>
                <div class="stat-icon total">
                    <i class="fas fa-chair"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $total_mesas; ?></h3>
        </div>
        
        <div class="stat-card ativas">
            <div class="stat-header">
                <span class="stat-title">Mesas Ativas</span>
                <div class="stat-icon ativas">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $mesas_livres; ?></h3>
        </div>
        
        <div class="stat-card inativas">
            <div class="stat-header">
                <span class="stat-title">Mesas Inativas</span>
                <div class="stat-icon inativas">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <h3 class="stat-value"><?php echo $mesas_ocupadas; ?></h3>
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
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">
            <i class="fas fa-info-circle"></i>
            Como funciona o sistema de mesas
        </h3>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-list-ol"></i>
                    Sem Limite
                </div>
                <p>Você pode cadastrar diversas mesas para seu estabelecimento.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-hashtag"></i>
                    Numeração
                </div>
                <p>Cada mesa deve ter um número único para identificação.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-user-tie"></i>
                    Garçons
                </div>
                <p>Associe garçons às mesas para organizar o atendimento.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-receipt"></i>
                    Pedidos
                </div>
                <p>Os pedidos das mesas aparecem integrados no seu painel principal.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-mobile"></i>
                    App PWA
                </div>
                <p>Garçons acessam um app móvel para gerenciar pedidos das mesas.</p>
            </div>
            <div class="info-card">
                <div class="info-title">
                    <i class="fas fa-cog"></i>
                    Configuração
                </div>
                <p>Configure garçons no menu "Gerenciar Garçons" para dar acesso ao app.</p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-table"></i>
                Mesas Cadastradas (<?php echo $total_mesas; ?>/50)
            </h2>
        </div>

        <?php if ($total_mesas > 0): ?>
            <div class="mesas-grid">
                <?php 
                mysqli_data_seek($mesas_sql, 0);
                while ($mesa = mysqli_fetch_array($mesas_sql)): 
                ?>
                    <div class="mesa-card <?php echo $mesa['status'] == '1' ? 'ativa' : 'inativa'; ?>">
                        <div class="mesa-header">
                            <div class="mesa-numero"><?php echo $mesa['numero']; ?></div>
                            <span class="mesa-status status-<?php echo $mesa['status'] == '1' ? 'ativa' : 'inativa'; ?>">
                                <?php echo $mesa['status'] == '1' ? 'Ativa' : 'Inativa'; ?>
                            </span>
                        </div>
                        
                        <div class="mesa-info">
                            <div class="mesa-nome"><?php echo htmlclean($mesa['nome']); ?></div>
                        </div>
                        
                        <div class="mesa-actions">
                            <button onclick="editMesa(<?php echo $mesa['id']; ?>, '<?php echo htmlclean($mesa['numero']); ?>', '<?php echo htmlclean($mesa['nome']); ?>', '<?php echo $mesa['status']; ?>')" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button onclick="deleteMesa(<?php echo $mesa['id']; ?>, '<?php echo htmlclean($mesa['nome']); ?>')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                                Excluir
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chair"></i>
                <h3>Nenhuma mesa cadastrada ainda</h3>
                <p>Adicione a primeira mesa usando o formulário acima.</p>
                <button onclick="openModal('modalAddMesa')" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Adicionar Primeira Mesa
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adicionar Mesa -->
<div id="modalAddMesa" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-plus"></i>
                Adicionar Nova Mesa
            </h3>
            <button type="button" class="close" onclick="closeModal('modalAddMesa')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_mesa">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-hashtag"></i>
                        Número da Mesa *
                    </label>
                    <input type="number" name="numero" class="form-control" min="1" max="999" required placeholder="Ex: 1, 2, 3...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Nome da Mesa
                    </label>
                    <input type="text" name="nome" class="form-control" placeholder="Ex: Mesa VIP, Mesa da Janela...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalAddMesa')" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Salvar Mesa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Mesa -->
<div id="modalEditMesa" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-edit"></i>
                Editar Mesa
            </h3>
            <button type="button" class="close" onclick="closeModal('modalEditMesa')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_mesa">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-hashtag"></i>
                        Número da Mesa *
                    </label>
                    <input type="number" name="numero" id="edit_numero" class="form-control" min="1" max="999" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Nome da Mesa
                    </label>
                    <input type="text" name="nome" id="edit_nome" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-info-circle"></i>
                        Status da Mesa
                    </label>
                    <select name="status" id="edit_status" class="form-control form-select">
                        <option value="1">Ativa</option>
                        <option value="0">Inativa</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEditMesa')" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i>
                    Atualizar Mesa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editMesa(id, numero, nome, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_numero').value = numero;
    document.getElementById('edit_nome').value = nome;
    document.getElementById('edit_status').value = status;
    openModal('modalEditMesa');
}

function deleteMesa(id, nome) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: `Tem certeza que deseja excluir a mesa "${nome}"?`,
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
}

function copyAppUrl() {
    const url = '<?php echo $app_url; ?>';
    navigator.clipboard.writeText(url).then(() => {
        Swal.fire({
            title: 'Copiado!',
            text: 'Link do app copiado para área de transferência',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
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
        // Fallback para WhatsApp
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
        window.open(whatsappUrl, '_blank');
    }
}

// Fechar modal clicando fora
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Adicionar animação aos cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.mesa-card, .stat-card');
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

<?php
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>