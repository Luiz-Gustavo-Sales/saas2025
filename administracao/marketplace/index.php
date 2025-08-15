<?php
// Configuração de segurança
session_start();
ini_set('display_errors', 0); // Desabilitar em produção
ini_set('display_startup_errors', 0);
error_reporting(0); // Desabilitar em produção

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Função para sanitizar dados
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Função para validar e processar imagens
function validate_image_url($url, $default_image) {
    if (empty($url)) return $default_image;
    
    // Verificar se a URL é válida
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $default_image;
    }
    
    return $url;
}

// Configuração de cache
$cache_time = 300; // 5 minutos
header("Cache-Control: public, max-age=$cache_time");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');

// Dados de conexão com validação
$config_file = '../config/database.php';
if (file_exists($config_file)) {
    include $config_file;
} else {
    // Fallback para dados diretos (apenas em desenvolvimento)
    $db_host = "localhost";
    $db_user = "tecautov_sistema";
    $db_pass = "hevRQ6#Ur]6R";
    $db_name = "tecautov_sistema";
}

// Conexão com tratamento robusto de erros
try {
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($db->connect_error) {
        throw new Exception("Falha na conexão: " . $db->connect_error);
    }
    
    $db->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Erro de conexão com banco: " . $e->getMessage());
    die("<div style='padding:20px;background:#ffebee;border:2px solid #f44336;margin:20px;'>
            <h2>Serviço Temporariamente Indisponível</h2>
            <p>Estamos trabalhando para resolver este problema. Tente novamente em alguns minutos.</p>
        </div>");
}

// --- INÍCIO: Definição de $simple_url (Exemplo) ---
// Certifique-se de que $simple_url esteja definida ANTES de usá-la.
// Se ela vem de um arquivo de configuração, inclua-o aqui.
// Exemplo de como ela PODE ser definida (adapte à sua realidade):
if (!isset($simple_url)) { // Evita redefinir se já existir
    // Tenta obter do host HTTP, removendo 'www.' se presente
    $host = $_SERVER['HTTP_HOST'] ?? 'dominio-padrao.com'; // Use um padrão se não houver host
    $simple_url = preg_replace('/^www\./', '', $host);
    // Você pode precisar de uma lógica mais robusta dependendo dos seus domínios
}
// --- FIM: Definição de $simple_url (Exemplo) ---

// --- INÍCIO: Configurações de SEO ---
$seo_title = "Marketplace - Encontre Lojas Online na Sua Região";
$seo_description = "Explore uma variedade de lojas e estabelecimentos locais em nosso marketplace. Encontre produtos e serviços perto de você.";
$seo_keywords = "marketplace, lojas online, compras locais, estabelecimentos, comércio local, " . $simple_url; // Adiciona o domínio base às keywords
$seo_og_image = "https://{$simple_url}/_core/_cdn/img/og-image-padrao.jpg"; // Crie uma imagem padrão para compartilhamento social
$seo_canonical_url = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; // URL canônica da página atual
// --- FIM: Configurações de SEO ---

// 1. Consulta DEBUG para verificar se há estabelecimentos
$debug_query = $db->query("SELECT COUNT(*) as total FROM estabelecimentos");
$total_estab = $debug_query->fetch_assoc()['total'];

// 2. Consulta DEBUG para verificar estabelecimentos ativos
$debug_query = $db->query("SELECT COUNT(*) as total FROM estabelecimentos WHERE status = '1' AND excluded != '1'");
$total_estab_ativos = $debug_query->fetch_assoc()['total'];

// Consulta OTIMIZADA para estabelecimentos com paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12; // Lojas por página
$offset = ($page - 1) * $per_page;

$estabelecimentos = [];

// Primeiro, contar total de estabelecimentos para paginação
$count_query = "SELECT COUNT(*) as total FROM estabelecimentos 
               WHERE status = '1' AND excluded != '1'";
$count_result = $db->query($count_query);
$total_estabelecimentos = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_estabelecimentos / $per_page);

$query_estab = "SELECT e.id, e.nome, c.nome as cidade, e.subdominio, e.perfil, e.descricao, s.nome as segmento 
               FROM estabelecimentos e
               LEFT JOIN cidades c ON e.cidade = c.id
               LEFT JOIN segmentos s ON e.segmento = s.id
               WHERE e.status = '1' AND e.excluded != '1'
               ORDER BY e.nome ASC 
               LIMIT $per_page OFFSET $offset";

$result = $db->query($query_estab);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Validação e sanitização dos dados
        $row['nome'] = sanitize_input($row['nome']);
        $row['cidade'] = sanitize_input($row['cidade']);
        $row['segmento'] = sanitize_input($row['segmento'] ?? '');
        $row['descricao'] = sanitize_input($row['descricao'] ?? '');
        
        // Pular estabelecimentos com dados inválidos
        if (empty($row['nome']) || empty($row['cidade'])) {
            continue;
        }
        
        // Define o domínio base (com ou sem subdomínio) usando $simple_url
        $baseUrl = !empty($row['subdominio'])
            ? "https://{$row['subdominio']}.{$simple_url}"
            : "https://{$simple_url}";

        // URL amigável para o botão/link da loja
        $row['url'] = !empty($row['subdominio'])
            ? $baseUrl
            : "{$baseUrl}/loja/{$row['id']}";

        // Validação e processamento da imagem
        if (!empty($row['perfil'])) {
            $image_url = "{$baseUrl}/_core/_uploads/{$row['perfil']}";
            $row['logo'] = validate_image_url($image_url, "https://{$simple_url}/_core/_cdn/img/no-image.png");
        } else {
            $row['logo'] = "https://{$simple_url}/_core/_cdn/img/no-image.png";
        }

        $estabelecimentos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seo_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical_url); ?>" />
    
    <!-- Robots Meta -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($seo_og_image); ?>">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($simple_url); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($seo_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($seo_description); ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($seo_og_image); ?>">
    
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "<?php echo htmlspecialchars($seo_title); ?>",
        "description": "<?php echo htmlspecialchars($seo_description); ?>",
        "url": "<?php echo htmlspecialchars($seo_canonical_url); ?>",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": <?php echo count($estabelecimentos); ?>,
            "itemListElement": [
                <?php 
                $schema_items = [];
                foreach(array_slice($estabelecimentos, 0, 10) as $index => $estab): 
                    $schema_items[] = '{
                        "@type": "ListItem",
                        "position": ' . ($index + 1) . ',
                        "item": {
                            "@type": "LocalBusiness",
                            "name": "' . addslashes($estab['nome']) . '",
                            "description": "' . addslashes($estab['descricao'] ?? '') . '",
                            "url": "' . $estab['url'] . '",
                            "address": {
                                "@type": "PostalAddress",
                                "addressLocality": "' . addslashes($estab['cidade']) . '"
                            }
                        }
                    }';
                endforeach;
                echo implode(',', $schema_items);
                ?>
            ]
        }
    }
    </script>
    <!-- Fim SEO Meta Tags -->

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://<?php echo $simple_url; ?>/_core/_cdn/img/favicon.png">
    <!-- Para outros tipos de favicon (opcional): -->
    <!-- <link rel="apple-touch-icon" sizes="180x180" href="https://<?php echo $simple_url; ?>/_core/_cdn/img/apple-touch-icon.png"> -->
    <!-- <link rel="icon" type="image/png" sizes="32x32" href="https://<?php echo $simple_url; ?>/_core/_cdn/img/favicon-32x32.png"> -->
    <!-- <link rel="icon" type="image/png" sizes="16x16" href="https://<?php echo $simple_url; ?>/_core/_cdn/img/favicon-16x16.png"> -->
    <!-- <link rel="manifest" href="https://<?php echo $simple_url; ?>/_core/_cdn/img/site.webmanifest"> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: #343a40;
    }
    .header {
        background: linear-gradient(135deg, #5664d3 0%, #6b4f9e 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2.5rem;
        border-bottom: 3px solid rgba(0,0,0,0.1);
    }
    .header h1 {
        font-weight: 600;
    }
    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        background-color: #ffffff;
    }
    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    .card-img-top {
        height: 180px;
        object-fit: cover;
        border-bottom: 1px solid #eee;
        transition: transform 0.3s ease;
    }
    .card:hover .card-img-top {
        transform: scale(1.05);
    }
    .card-body {
        padding: 0.7rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: #495057;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .card-body .text-muted {
        font-size: 0.78rem;
        margin-bottom: 0.4rem;
        line-height: 1.2;
    }
    .card-footer {
        background-color: #ffffff;
        border-top: none;
        padding: 0.3rem 0.7rem 0.5rem;
        margin-top: -20px;
    }
    .card-footer .btn {
        padding: 0.35rem 0.7rem;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    .card-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .debug-info {
        background-color: #e9ecef;
        border-left: 4px solid #adb5bd;
        padding: 15px 20px;
        margin-bottom: 2.5rem;
        font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
        font-size: 0.85rem;
        border-radius: 4px;
    }
    .alert-info {
        background-color: #e7f3fe;
        border-color: #d0eaff;
        color: #0c5464;
        padding: 2rem;
        border-radius: 8px;
    }
    
    /* Loading skeleton */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    /* Filtros responsivos */
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #5664d3;
        box-shadow: 0 0 0 0.2rem rgba(86, 100, 211, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
    
    /* Lazy loading placeholder */
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
    
    /* Badge para contador */
    .badge-counter {
        background: linear-gradient(45deg, #5664d3, #6b4f9e);
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
    }
    
    /* Responsividade melhorada */
    @media (max-width: 768px) {
        .header {
            padding: 2rem 0;
            margin-bottom: 1.5rem;
        }
        
        .card-img-top {
            height: 140px;
        }
        
        .debug-info {
            padding: 10px 15px;
            font-size: 0.8rem;
        }
        
        .col-6 {
            padding-left: 8px;
            padding-right: 8px;
        }
        
        .card {
            margin-bottom: 15px;
        }
        
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }
    
    @media (max-width: 576px) {
        .header h1 {
            font-size: 1.5rem;
        }
        
        .lead {
            font-size: 1rem;
        }
        
        .category-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Categorias Visuais */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }
    
    .category-item {
        cursor: pointer;
    }
    
    .category-card {
        background: linear-gradient(135deg, var(--category-color), color-mix(in srgb, var(--category-color) 80%, white));
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .category-icon i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        display: block;
    }
    
    .category-info h5 {
        margin: 10px 0 5px 0;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .category-count {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    /* Sistema de Avaliações */
    .rating-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }
    
    .stars {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .rating-value {
        font-weight: 600;
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .stars-container {
        color: #ffc107;
        font-size: 0.8rem;
    }
    
    .rating-count {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .avaliar-btn {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
    
    /* Sistema de Comparação */
    .compare-checkbox {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    
    .compare-item {
        display: none;
    }
    
    .compare-label {
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .compare-label:hover {
        background: #007bff;
        color: white;
        transform: scale(1.1);
    }
    
    .compare-item:checked + .compare-label {
        background: #28a745;
        color: white;
    }
    
    .compare-item:checked + .compare-label i:before {
        content: "\f00c";
    }
    
    /* Modal Avaliação */
    .rating-input {
        margin: 20px 0;
    }
    
    .rating-label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .stars-input {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin: 10px 0;
    }
    
    .star-input {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .star-input:hover,
    .star-input.active {
        color: #ffc107;
    }
    
    /* Mapa */
    #mapa {
        min-height: 400px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* Popups do mapa */
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    
    .map-popup {
        min-width: 200px;
        text-align: center;
    }
    
    .map-popup img {
        border: 2px solid #e9ecef;
        transition: transform 0.3s ease;
    }
    
    .map-popup img:hover {
        transform: scale(1.05);
    }
    
    .map-popup h6 {
        color: #343a40;
        margin: 8px 0;
        font-weight: bold;
    }
    
    .map-popup p {
        margin: 5px 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .map-popup .btn {
        margin-top: 8px;
        padding: 8px 16px;
        font-size: 0.85rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        background-color: #007bff !important;
        border-color: #007bff !important;
        color: white !important;
        text-decoration: none !important;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0,123,255,0.3);
        opacity: 1 !important;
        visibility: visible !important;
        border: 2px solid #007bff !important;
        display: inline-block !important;
        margin: 5px 3px !important;
        cursor: pointer !important;
    }
    
    .map-popup .btn:hover {
        background-color: #0056b3 !important;
        border-color: #0056b3 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,123,255,0.4) !important;
        color: white !important;
        opacity: 1 !important;
    }
    }
    
    .map-popup .btn:active,
    .map-popup .btn:focus {
        background-color: #0056b3 !important;
        border-color: #0056b3 !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(0,123,255,0.5) !important;
    }
    
    /* Controles do mapa */
    .leaflet-control-zoom {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .leaflet-control-zoom a {
        background-color: #fff;
        color: #333;
        border: none;
        transition: all 0.3s ease;
    }
    
    .leaflet-control-zoom a:hover {
        background-color: #5664d3;
        color: white;
    }
    
    /* Info box do mapa */
    .map-info {
        background: rgba(255,255,255,0.95);
        padding: 8px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 12px;
        border: 1px solid #dee2e6;
    }
    
    /* Tooltips dos marcadores */
    .leaflet-tooltip {
        background: rgba(33, 37, 41, 0.9);
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 0.8rem;
        padding: 4px 8px;
    }
    
    /* Garantir visibilidade de todos os elementos do popup */
    .leaflet-popup-content .btn,
    .leaflet-popup-content a.btn,
    .map-popup .btn,
    .popup-estabelecimento .btn {
        opacity: 1 !important;
        visibility: visible !important;
        background-color: #007bff !important;
        color: white !important;
        border: 2px solid #007bff !important;
        text-decoration: none !important;
        display: inline-block !important;
        font-weight: 500 !important;
        border-radius: 6px !important;
        padding: 8px 12px !important;
        margin: 3px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 4px rgba(0,123,255,0.3) !important;
        transition: all 0.2s ease !important;
    }
    
    .leaflet-popup-content .btn:hover,
    .leaflet-popup-content a.btn:hover,
    .map-popup .btn:hover,
    .popup-estabelecimento .btn:hover {
        background-color: #0056b3 !important;
        color: white !important;
        border-color: #0056b3 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(0,123,255,0.4) !important;
    }
    
    /* Para botões de outline */
    .leaflet-popup-content .btn-outline-primary,
    .map-popup .btn-outline-primary,
    .popup-estabelecimento .btn-outline-primary {
        background-color: white !important;
        color: #007bff !important;
        border: 2px solid #007bff !important;
    }
    
    .leaflet-popup-content .btn-outline-primary:hover,
    .map-popup .btn-outline-primary:hover,
    .popup-estabelecimento .btn-outline-primary:hover {
        background-color: #007bff !important;
        color: white !important;
        border-color: #007bff !important;
    }
    
    .leaflet-tooltip:before {
        border-top-color: rgba(33, 37, 41, 0.9);
    }
    
    /* Tabela de Comparação */
    .comparison-table {
        margin-top: 20px;
    }
    
    .comparison-header {
        background: linear-gradient(135deg, #5664d3, #6b4f9e);
        color: white;
        text-align: center;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }
    
    .comparison-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 10px;
        overflow: hidden;
    }
    
    .comparison-item img {
        height: 120px;
        object-fit: cover;
    }
    
    .comparison-info {
        padding: 15px;
    }
    
    /* ESTILOS DO NEWSLETTER REMOVIDOS POR SOLICITAÇÃO DO USUÁRIO
    .newsletter-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }
    */
    
    /* Animações */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
    
    /* Loader para mapa */
    .map-loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header text-center">
        <div class="container">
            <h1><i class="fas fa-store-alt"></i> Nossas Lojas Online</h1>
            <p class="lead">Encontre os melhores estabelecimentos para compra seus produtos</p>
        </div>
    </header>

    <!-- Debug Information -->
    <div class="container debug-info mb-4">
        <h4>Informações Estabelecimentos:</h4>
        <p>Total de estabelecimentos no banco: <strong><?php echo $total_estab; ?></strong></p>
        <p>Estabelecimentos ativos: <strong><?php echo $total_estab_ativos; ?></strong></p>
    </div>

    <!-- Categorias Visuais -->
    <div class="container mb-4">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">
                    <i class="fas fa-th-large"></i> Explore por Categoria
                </h3>
                <div class="category-grid">
                    <?php
                    $categorias_query = $db->query("
                        SELECT s.nome as segmento_nome, s.id as segmento_id, COUNT(e.id) as total_lojas,
                        CASE 
                            WHEN LOWER(s.nome) LIKE '%restaurante%' OR LOWER(s.nome) LIKE '%comida%' OR LOWER(s.nome) LIKE '%alimentação%' THEN 'fas fa-utensils'
                            WHEN LOWER(s.nome) LIKE '%moda%' OR LOWER(s.nome) LIKE '%roupa%' OR LOWER(s.nome) LIKE '%vestuário%' THEN 'fas fa-tshirt'
                            WHEN LOWER(s.nome) LIKE '%eletrônico%' OR LOWER(s.nome) LIKE '%tecnologia%' THEN 'fas fa-laptop'
                            WHEN LOWER(s.nome) LIKE '%farmácia%' OR LOWER(s.nome) LIKE '%saúde%' THEN 'fas fa-pills'
                            WHEN LOWER(s.nome) LIKE '%beleza%' OR LOWER(s.nome) LIKE '%estética%' THEN 'fas fa-spa'
                            WHEN LOWER(s.nome) LIKE '%automóvel%' OR LOWER(s.nome) LIKE '%auto%' THEN 'fas fa-car'
                            WHEN LOWER(s.nome) LIKE '%casa%' OR LOWER(s.nome) LIKE '%construção%' THEN 'fas fa-home'
                            WHEN LOWER(s.nome) LIKE '%esporte%' OR LOWER(s.nome) LIKE '%fitness%' THEN 'fas fa-dumbbell'
                            WHEN LOWER(s.nome) LIKE '%pet%' OR LOWER(s.nome) LIKE '%animal%' THEN 'fas fa-paw'
                            WHEN LOWER(s.nome) LIKE '%livraria%' OR LOWER(s.nome) LIKE '%educação%' THEN 'fas fa-book'
                            ELSE 'fas fa-store'
                        END as icone,
                        CASE 
                            WHEN LOWER(s.nome) LIKE '%restaurante%' OR LOWER(s.nome) LIKE '%comida%' THEN '#FF6B6B'
                            WHEN LOWER(s.nome) LIKE '%moda%' OR LOWER(s.nome) LIKE '%roupa%' THEN '#4ECDC4'
                            WHEN LOWER(s.nome) LIKE '%eletrônico%' OR LOWER(s.nome) LIKE '%tecnologia%' THEN '#45B7D1'
                            WHEN LOWER(s.nome) LIKE '%farmácia%' OR LOWER(s.nome) LIKE '%saúde%' THEN '#96CEB4'
                            WHEN LOWER(s.nome) LIKE '%beleza%' OR LOWER(s.nome) LIKE '%estética%' THEN '#FFEAA7'
                            WHEN LOWER(s.nome) LIKE '%automóvel%' OR LOWER(s.nome) LIKE '%auto%' THEN '#DDA0DD'
                            WHEN LOWER(s.nome) LIKE '%casa%' OR LOWER(s.nome) LIKE '%construção%' THEN '#98D8C8'
                            WHEN LOWER(s.nome) LIKE '%esporte%' OR LOWER(s.nome) LIKE '%fitness%' THEN '#F7DC6F'
                            WHEN LOWER(s.nome) LIKE '%pet%' OR LOWER(s.nome) LIKE '%animal%' THEN '#BB8FCE'
                            WHEN LOWER(s.nome) LIKE '%livraria%' OR LOWER(s.nome) LIKE '%educação%' THEN '#85C1E9'
                            ELSE '#5664d3'
                        END as cor
                        FROM estabelecimentos e 
                        INNER JOIN segmentos s ON e.segmento = s.id 
                        WHERE e.status = '1' AND e.excluded != '1' 
                        GROUP BY s.id, s.nome 
                        ORDER BY total_lojas DESC, s.nome ASC
                        LIMIT 12
                    ");
                    
                    while($cat = $categorias_query->fetch_assoc()):
                    ?>
                    <div class="category-item" data-categoria="<?php echo htmlspecialchars($cat['segmento_nome']); ?>">
                        <div class="category-card" style="--category-color: <?php echo $cat['cor']; ?>">
                            <div class="category-icon">
                                <i class="<?php echo $cat['icone']; ?>"></i>
                            </div>
                            <div class="category-info">
                                <h5><?php echo htmlspecialchars($cat['segmento_nome']); ?></h5>
                                <span class="category-count"><?php echo $cat['total_lojas']; ?> lojas</span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="container mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar lojas...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <select class="form-select" id="segmentoFilter">
                            <option value="">Todos os Segmentos</option>
                            <?php
                            $segmentos_query = $db->query("
                                SELECT DISTINCT s.nome as segmento_nome, s.id as segmento_id 
                                FROM estabelecimentos e 
                                INNER JOIN segmentos s ON e.segmento = s.id 
                                WHERE e.status = '1' AND e.excluded != '1' AND s.nome != '' 
                                ORDER BY s.nome
                            ");
                            while($seg = $segmentos_query->fetch_assoc()){
                                echo "<option value='{$seg['segmento_nome']}'>{$seg['segmento_nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <select class="form-select" id="cidadeFilter">
                            <option value="">Todas as Cidades</option>
                            <?php
                            $cidades_query = $db->query("
                                SELECT DISTINCT c.nome as cidade_nome, c.id as cidade_id 
                                FROM estabelecimentos e 
                                INNER JOIN cidades c ON e.cidade = c.id 
                                WHERE e.status = '1' AND e.excluded != '1' AND c.nome != '' 
                                ORDER BY c.nome
                            ");
                            while($cid = $cidades_query->fetch_assoc()){
                                echo "<option value='{$cid['cidade_nome']}'>{$cid['cidade_nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <!-- NEWSLETTER REMOVIDO POR SOLICITAÇÃO DO USUÁRIO
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#newsletterModal">
                            <i class="fas fa-envelope"></i> Newsletter
                        </button>
                        -->
                    </div>
                </div>
                
                <!-- Segunda linha com comparador e filtros extras -->
                <div class="row mt-3 align-items-center">
                    <div class="col-md-3">
                        <select class="form-select" id="ordenacao">
                            <option value="nome">Nome A-Z</option>
                            <option value="nome_desc">Nome Z-A</option>
                            <option value="cidade">Por Cidade</option>
                            <option value="avaliacao">Melhor Avaliadas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-info w-100" id="btnComparador" disabled>
                            <i class="fas fa-balance-scale"></i> Comparar (<span id="compareCount">0</span>)
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#mapaModal">
                            <i class="fas fa-map-marked-alt"></i> Ver no Mapa
                        </button>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="apenasAvaliadas">
                            <label class="form-check-label" for="apenasAvaliadas">
                                Apenas Avaliadas
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div>
            <?php if (empty($estabelecimentos)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>Nenhuma loja disponível no momento</h4>
                    <p>Verifique os critérios de filtro ou cadastre novos estabelecimentos.</p>
                    <p class="small">Total no banco: <?php echo $total_estab; ?> | Ativos (contagem): <?php echo $total_estab_ativos; ?></p>
                </div>
            <?php else: ?>
                <div class="row" id="lojas-container">
                    <?php foreach ($estabelecimentos as $estab): ?>
                        <div class="col-6 col-md-3 mb-4">
                            <div class="card h-100" data-loja-id="<?php echo $estab['id']; ?>">
                                <!-- Checkbox para comparação -->
                                <div class="compare-checkbox">
                                    <input type="checkbox" class="form-check-input compare-item" value="<?php echo $estab['id']; ?>" id="compare_<?php echo $estab['id']; ?>">
                                    <label for="compare_<?php echo $estab['id']; ?>" class="compare-label">
                                        <i class="fas fa-plus"></i>
                                    </label>
                                </div>
                                
                                <a href="<?php echo $estab['url']; ?>">
                                    <img src="<?php echo $estab['logo']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($estab['nome']); ?>" style="object-fit: cover;"> 
                                </a>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                         <?php echo htmlspecialchars($estab['nome']); ?>
                                    </h5>
                                    
                                    <!-- Sistema de Avaliações -->
                                    <div class="rating-display mb-2" data-loja-id="<?php echo $estab['id']; ?>">
                                        <div class="stars">
                                            <span class="rating-value">4.2</span>
                                            <div class="stars-container">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="far fa-star"></i>
                                            </div>
                                            <span class="rating-count">(127)</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary avaliar-btn" data-loja-id="<?php echo $estab['id']; ?>" data-loja-nome="<?php echo htmlspecialchars($estab['nome']); ?>">
                                            <i class="fas fa-star-half-alt"></i> Avaliar
                                        </button>
                                    </div>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt fa-fw"></i> <?php echo htmlspecialchars($estab['cidade']); ?>
                                        <?php if (!empty($estab['segmento'])): ?>
                                            <br><i class="fas fa-tag fa-fw"></i> <?php echo htmlspecialchars($estab['segmento']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <div class="card-footer bg-white border-top-0 pt-0 mt-auto">
                                    <div class="row">
                                        <div class="col-8">
                                            <a href="<?php echo $estab['url']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                                Visitar Loja <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                        <div class="col-4">
                                            <button class="btn btn-outline-info btn-sm w-100 info-btn" data-loja-id="<?php echo $estab['id']; ?>" title="Mais informações">
                                                <i class="fas fa-info"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginação -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Navegação de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Próximo">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Página <?php echo $page; ?> de <?php echo $total_pages; ?> 
                            (<?php echo $total_estabelecimentos; ?> lojas no total)
                        </small>
                    </div>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> Digita Vitrine Marketplace. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- MODAL NEWSLETTER REMOVIDO POR SOLICITAÇÃO DO USUÁRIO
    <div class="modal fade" id="newsletterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope"></i> Newsletter - Fique por Dentro!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newsletterForm">
                        <div class="text-center mb-4">
                            <i class="fas fa-bell fa-3x text-primary mb-3"></i>
                            <p>Receba novidades sobre novas lojas, promoções e ofertas especiais!</p>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newsletter_nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="newsletter_nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newsletter_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="newsletter_email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="newsletter_cidade" class="form-label">Cidade de Interesse</label>
                            <select class="form-select" id="newsletter_cidade">
                                <option value="">Todas as cidades</option>
                                <?php
                                $newsletter_cidades = $db->query("SELECT DISTINCT c.nome FROM estabelecimentos e INNER JOIN cidades c ON e.cidade = c.id WHERE e.status = '1' ORDER BY c.nome");
                                while($nc = $newsletter_cidades->fetch_assoc()):
                                ?>
                                <option value="<?php echo $nc['nome']; ?>"><?php echo $nc['nome']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter_promocoes" checked>
                                <label class="form-check-label" for="newsletter_promocoes">
                                    Receber promoções e ofertas especiais
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter_novas_lojas" checked>
                                <label class="form-check-label" for="newsletter_novas_lojas">
                                    Notificar sobre novas lojas
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane"></i> Inscrever-se
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    -->

    <!-- Modal Avaliação -->
    <div class="modal fade" id="avaliacaoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-star"></i> Avaliar Loja
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="avaliacaoForm">
                        <input type="hidden" id="avaliacao_loja_id">
                        
                        <div class="text-center mb-4">
                            <h6 id="avaliacao_loja_nome" class="mb-3"></h6>
                            <div class="rating-input">
                                <span class="rating-label">Sua avaliação:</span>
                                <div class="stars-input">
                                    <i class="far fa-star star-input" data-rating="1"></i>
                                    <i class="far fa-star star-input" data-rating="2"></i>
                                    <i class="far fa-star star-input" data-rating="3"></i>
                                    <i class="far fa-star star-input" data-rating="4"></i>
                                    <i class="far fa-star star-input" data-rating="5"></i>
                                </div>
                                <input type="hidden" id="rating_value" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="avaliacao_nome" class="form-label">Seu Nome</label>
                            <input type="text" class="form-control" id="avaliacao_nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="avaliacao_comentario" class="form-label">Comentário (opcional)</label>
                            <textarea class="form-control" id="avaliacao_comentario" rows="3" placeholder="Conte sua experiência..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-star"></i> Enviar Avaliação
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Comparador -->
    <div class="modal fade" id="comparadorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-balance-scale"></i> Comparar Lojas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="comparison-table" class="table-responsive">
                        <!-- Tabela de comparação será gerada via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mapa -->
    <div class="modal fade" id="mapaModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marked-alt"></i> Localização das Lojas
                        <small class="text-muted ms-2" id="mapaContador">Carregando...</small>
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="recarregarMapa()">
                            <i class="fas fa-sync-alt"></i> Recarregar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="debugMapa()">
                            <i class="fas fa-bug"></i> Debug
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div id="mapa" style="height: 500px; width: 100%;">
                        <!-- Mapa será carregado aqui -->
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                            <div class="text-center">
                                <i class="fas fa-map fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Carregando mapa...</p>
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Mapa Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    // Dados das lojas para JavaScript
    const lojasData = <?php echo json_encode($estabelecimentos, JSON_UNESCAPED_UNICODE); ?>;
    let lojasFiltradas = [...lojasData];
    let lojasComparadas = [];
    let map = null;
    
    // Debug: Mostrar dados no console
    console.log('Total de lojas carregadas:', lojasData.length);
    console.log('Dados das lojas:', lojasData);
    
    // Verificar se existem dados de cidades
    const cidadesUnicas = [...new Set(lojasData.map(loja => loja.cidade))];
    console.log('Cidades encontradas:', cidadesUnicas);
    
    // Simulação de dados de avaliação (em produção, vir do banco)
    const avaliacoesData = {
        <?php foreach($estabelecimentos as $index => $estab): ?>
        '<?php echo $estab['id']; ?>': {
            rating: <?php echo number_format(rand(30, 50) / 10, 1); ?>,
            total: <?php echo rand(15, 300); ?>
        }<?php echo $index < count($estabelecimentos) - 1 ? ',' : ''; ?>
        <?php endforeach; ?>
    };
    
    // Função para renderizar lojas
    function renderizarLojas(lojas) {
        const container = document.getElementById('lojas-container');
        container.innerHTML = '';
        
        if (lojas.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <h4>Nenhuma loja encontrada</h4>
                        <p>Tente ajustar os filtros de busca.</p>
                    </div>
                </div>
            `;
            return;
        }
        
        lojas.forEach(loja => {
            const avaliacao = avaliacoesData[loja.id] || { rating: 0, total: 0 };
            const starsHtml = gerarEstrelasHTML(avaliacao.rating);
            
            const lojaCard = `
                <div class="col-6 col-md-3 mb-4 loja-item fade-in-up">
                    <div class="card h-100" data-loja-id="${loja.id}">
                        <!-- Checkbox para comparação -->
                        <div class="compare-checkbox">
                            <input type="checkbox" class="form-check-input compare-item" value="${loja.id}" id="compare_${loja.id}">
                            <label for="compare_${loja.id}" class="compare-label">
                                <i class="fas fa-plus"></i>
                            </label>
                        </div>
                        
                        <a href="${loja.url}">
                            <img src="${loja.logo}" class="card-img-top" alt="${loja.nome}" style="object-fit: cover;" loading="lazy"> 
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${loja.nome}</h5>
                            
                            <!-- Sistema de Avaliações -->
                            <div class="rating-display mb-2" data-loja-id="${loja.id}">
                                <div class="stars">
                                    <span class="rating-value">${avaliacao.rating}</span>
                                    <div class="stars-container">
                                        ${starsHtml}
                                    </div>
                                    <span class="rating-count">(${avaliacao.total})</span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary avaliar-btn" data-loja-id="${loja.id}" data-loja-nome="${loja.nome}">
                                    <i class="fas fa-star-half-alt"></i> Avaliar
                                </button>
                            </div>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt fa-fw"></i> ${loja.cidade}
                                ${loja.segmento ? `<br><i class="fas fa-tag fa-fw"></i> ${loja.segmento}` : ''}
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0 pt-0 mt-auto">
                            <div class="row">
                                <div class="col-8">
                                    <a href="${loja.url}" class="btn btn-outline-primary btn-sm w-100">
                                        Visitar Loja <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-info btn-sm w-100 info-btn" data-loja-id="${loja.id}" title="Mais informações">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += lojaCard;
        });
        
        // Reattach event listeners
        attachEventListeners();
    }
    
    // Gerar HTML das estrelas
    function gerarEstrelasHTML(rating) {
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                starsHtml += '<i class="fas fa-star"></i>';
            } else if (i - 0.5 <= rating) {
                starsHtml += '<i class="fas fa-star-half-alt"></i>';
            } else {
                starsHtml += '<i class="far fa-star"></i>';
            }
        }
        return starsHtml;
    }
    
    // Função de busca e filtros
    function filtrarLojas() {
        const busca = document.getElementById('searchInput').value.toLowerCase();
        const segmento = document.getElementById('segmentoFilter').value.toLowerCase();
        const cidade = document.getElementById('cidadeFilter').value.toLowerCase();
        const ordenacao = document.getElementById('ordenacao').value;
        const apenasAvaliadas = document.getElementById('apenasAvaliadas').checked;
        
        lojasFiltradas = lojasData.filter(loja => {
            const matchBusca = !busca || loja.nome.toLowerCase().includes(busca) || loja.descricao.toLowerCase().includes(busca);
            const matchSegmento = !segmento || loja.segmento.toLowerCase().includes(segmento);
            const matchCidade = !cidade || loja.cidade.toLowerCase().includes(cidade);
            const matchAvaliacao = !apenasAvaliadas || (avaliacoesData[loja.id] && avaliacoesData[loja.id].total > 0);
            
            return matchBusca && matchSegmento && matchCidade && matchAvaliacao;
        });
        
        // Ordenação
        switch(ordenacao) {
            case 'nome':
                lojasFiltradas.sort((a, b) => a.nome.localeCompare(b.nome));
                break;
            case 'nome_desc':
                lojasFiltradas.sort((a, b) => b.nome.localeCompare(a.nome));
                break;
            case 'cidade':
                lojasFiltradas.sort((a, b) => a.cidade.localeCompare(b.cidade));
                break;
            case 'avaliacao':
                lojasFiltradas.sort((a, b) => {
                    const ratingA = avaliacoesData[a.id]?.rating || 0;
                    const ratingB = avaliacoesData[b.id]?.rating || 0;
                    return ratingB - ratingA;
                });
                break;
        }
        
        renderizarLojas(lojasFiltradas);
        atualizarContadores();
        
        // O mapa sempre mostra todos os estabelecimentos, não precisa atualizar
        console.log('Filtros aplicados. O mapa continua mostrando todos os estabelecimentos.');
    }
    
    // Filtrar por categoria visual
    function filtrarPorCategoria(categoria) {
        document.getElementById('segmentoFilter').value = categoria;
        filtrarLojas();
    }
    
    // Atualizar contadores
    function atualizarContadores() {
        const totalFiltradas = lojasFiltradas.length;
        const totalGeral = lojasData.length;
        
        document.querySelector('.debug-info').innerHTML = `
            <h4>Informações Estabelecimentos:</h4>
            <p>Total de estabelecimentos no banco: <strong><?php echo $total_estab; ?></strong></p>
            <p>Estabelecimentos ativos: <strong><?php echo $total_estab_ativos; ?></strong></p>
            <p>Exibindo: <strong>${totalFiltradas}</strong> de <strong>${totalGeral}</strong> lojas</p>
        `;
    }
    
    // Anexar event listeners
    function attachEventListeners() {
        // Comparador
        document.querySelectorAll('.compare-item').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const lojaId = this.value;
                if (this.checked) {
                    if (lojasComparadas.length < 3) {
                        lojasComparadas.push(lojaId);
                    } else {
                        this.checked = false;
                        alert('Você pode comparar no máximo 3 lojas por vez.');
                    }
                } else {
                    lojasComparadas = lojasComparadas.filter(id => id !== lojaId);
                }
                atualizarComparador();
            });
        });
        
        // Botões de avaliação
        document.querySelectorAll('.avaliar-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lojaId = this.dataset.lojaId;
                const lojaNome = this.dataset.lojaNome;
                abrirModalAvaliacao(lojaId, lojaNome);
            });
        });
        
        // Botões de informação
        document.querySelectorAll('.info-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lojaId = this.dataset.lojaId;
                mostrarInfoLoja(lojaId);
            });
        });
    }
    
    // Atualizar contador do comparador
    function atualizarComparador() {
        const count = lojasComparadas.length;
        document.getElementById('compareCount').textContent = count;
        document.getElementById('btnComparador').disabled = count < 2;
    }
    
    // Abrir modal de comparação
    function abrirComparador() {
        if (lojasComparadas.length < 2) return;
        
        const modal = new bootstrap.Modal(document.getElementById('comparadorModal'));
        gerarTabelaComparacao();
        modal.show();
    }
    
    // Gerar tabela de comparação
    function gerarTabelaComparacao() {
        const container = document.getElementById('comparison-table');
        const lojasParaComparar = lojasData.filter(loja => lojasComparadas.includes(loja.id));
        
        let html = `
            <div class="comparison-header">
                <h5><i class="fas fa-balance-scale"></i> Comparação de Lojas</h5>
            </div>
            <div class="row">
        `;
        
        lojasParaComparar.forEach(loja => {
            const avaliacao = avaliacoesData[loja.id] || { rating: 0, total: 0 };
            html += `
                <div class="col-md-4">
                    <div class="comparison-item">
                        <img src="${loja.logo}" class="w-100" alt="${loja.nome}">
                        <div class="comparison-info">
                            <h6>${loja.nome}</h6>
                            <p><i class="fas fa-map-marker-alt"></i> ${loja.cidade}</p>
                            <p><i class="fas fa-tag"></i> ${loja.segmento || 'N/A'}</p>
                            <div class="d-flex align-items-center">
                                <span class="rating-value text-warning">${avaliacao.rating}</span>
                                <div class="ms-2">${gerarEstrelasHTML(avaliacao.rating)}</div>
                                <small class="ms-2 text-muted">(${avaliacao.total})</small>
                            </div>
                            <a href="${loja.url}" class="btn btn-primary btn-sm mt-2 w-100">Visitar</a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
    
    /* SISTEMA DE NEWSLETTER REMOVIDO POR SOLICITAÇÃO DO USUÁRIO
    function enviarNewsletter(dados) {
        return fetch('newsletter_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Erro newsletter:', error);
            return { success: false, message: 'Erro de conexão' };
        });
    }
    */
    
    // Sistema de Avaliação
    function abrirModalAvaliacao(lojaId, lojaNome) {
        document.getElementById('avaliacao_loja_id').value = lojaId;
        document.getElementById('avaliacao_loja_nome').textContent = lojaNome;
        document.getElementById('rating_value').value = 0;
        
        // Reset stars
        document.querySelectorAll('.star-input').forEach(star => {
            star.classList.remove('active');
            star.className = 'far fa-star star-input';
        });
        
        const modal = new bootstrap.Modal(document.getElementById('avaliacaoModal'));
        modal.show();
    }
    
    function enviarAvaliacao(dados) {
        return fetch('avaliacoes_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Erro avaliação:', error);
            return { success: false, message: 'Erro de conexão' };
        });
    }
    
    // Carregar avaliações reais
    function carregarAvaliacoes(estabelecimentoId) {
        return fetch(`avaliacoes_api.php?estabelecimento_id=${estabelecimentoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return data.data.estatisticas;
                }
                return { total: 0, media: 0 };
            })
            .catch(error => {
                console.error('Erro ao carregar avaliações:', error);
                return { total: 0, media: 0 };
            });
    }
    
    // Inicializar Mapa
    function inicializarMapa() {
        if (map) {
            map.remove();
        }
        
        // Coordenadas padrão do Brasil (centro geográfico)
        map = L.map('mapa').setView([-14.235, -51.9253], 4);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);
        
        // Array para armazenar todos os marcadores
        const markers = [];
        let totalLojas = 0;
        
        // USAR SEMPRE TODOS OS DADOS, NÃO OS FILTRADOS
        const lojasParaMapa = lojasData; // Sempre usar todos os dados
        
        console.log('=== CARREGANDO MARCADORES NO MAPA ===');
        console.log('Total de lojas para processar:', lojasParaMapa.length);
        console.log('Dados originais (lojasData):', lojasData.length);
        console.log('Dados filtrados (lojasFiltradas):', lojasFiltradas.length);
        
        if (lojasParaMapa.length === 0) {
            console.warn('AVISO: Nenhuma loja encontrada para exibir no mapa!');
            console.log('Dados originais disponíveis:', lojasData.length);
        }
        
        lojasParaMapa.forEach((loja, index) => {
            // Log para debug
            console.log(`Processando loja ${index + 1}:`, loja.nome, 'Cidade:', loja.cidade, 'ID:', loja.id);
            
            // Obter coordenadas da cidade com ID da loja para posição fixa
            const coords = obterCoordenadasCidade(loja.cidade, loja.id);
            
            if (coords && coords.length === 2) {
                const latFinal = coords[0];
                const lngFinal = coords[1];
                
                console.log(`Coordenadas fixas para ${loja.nome}:`, [latFinal, lngFinal]);
                
                // Criar marcador
                const marker = L.marker([latFinal, lngFinal]).addTo(map);
                
                // Criar popup com informações da loja
                const popupContent = `
                    <div class="map-popup" style="min-width: 200px; text-align: center;">
                        <img src="${loja.logo}" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" 
                             alt="${loja.nome}" 
                             onerror="this.src='https://via.placeholder.com/80x80?text=Logo'">
                        <h6 style="margin: 8px 0; font-weight: bold;">${loja.nome}</h6>
                        <p style="margin: 5px 0; color: #666;">
                            <i class="fas fa-map-marker-alt" style="color: #dc3545;"></i> ${loja.cidade}
                        </p>
                        ${loja.segmento ? `<p style="margin: 5px 0; color: #666;">
                            <i class="fas fa-tag" style="color: #28a745;"></i> ${loja.segmento}
                        </p>` : ''}
                        <a href="${loja.url}" 
                           class="btn btn-sm btn-primary" 
                           target="_blank" 
                           style="margin-top: 8px; padding: 5px 15px;">
                            <i class="fas fa-external-link-alt"></i> Visitar Loja
                        </a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                markers.push(marker);
                totalLojas++;
                
                // Adicionar tooltip com nome da loja
                marker.bindTooltip(loja.nome, {
                    permanent: false,
                    direction: 'top',
                    offset: [0, -10]
                });
            } else {
                console.warn(`Coordenadas não encontradas para a cidade: ${loja.cidade}`);
            }
        });
        
        console.log(`Total de marcadores criados: ${totalLojas}`);
        
        // Atualizar contador no modal
        const contador = document.getElementById('mapaContador');
        if (contador) {
            contador.textContent = `(${totalLojas} estabelecimentos)`;
        }
        
        // Ajustar zoom para mostrar todos os marcadores
        if (markers.length > 0) {
            if (markers.length === 1) {
                // Se há apenas um marcador, centralizar nele com zoom adequado
                map.setView(markers[0].getLatLng(), 12);
            } else {
                // Se há múltiplos marcadores, ajustar bounds para mostrar todos
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds(), {
                    padding: [20, 20],
                    maxZoom: 15
                });
            }
        } else {
            // Se não há marcadores, mostrar o Brasil inteiro
            console.warn('Nenhum marcador foi criado. Verificar dados das lojas.');
            console.log('Tentando usar dados originais como fallback...');
            
            // Fallback: tentar usar todas as lojas disponíveis
            if (lojasData && lojasData.length > 0) {
                console.log('Usando fallback com todas as lojas disponíveis:', lojasData.length);
                // Recarregar com todos os dados
                lojasFiltradas = [...lojasData];
                // Chamar recursivamente mas apenas uma vez
                if (totalLojas === 0) {
                    console.log('Tentando recarregar marcadores com todos os dados...');
                    // Re-executar o loop com todos os dados
                    lojasData.forEach((loja, index) => {
                        console.log(`Processando loja fallback ${index + 1}:`, loja.nome, 'Cidade:', loja.cidade);
                        
                        const coords = obterCoordenadasCidade(loja.cidade);
                        
                        if (coords && coords.length === 2) {
                            const latVariacao = (Math.random() - 0.5) * 0.02;
                            const lngVariacao = (Math.random() - 0.5) * 0.02;
                            
                            const latFinal = coords[0] + latVariacao;
                            const lngFinal = coords[1] + lngVariacao;
                            
                            console.log(`Coordenadas fallback para ${loja.nome}:`, [latFinal, lngFinal]);
                            
                            const marker = L.marker([latFinal, lngFinal]).addTo(map);
                            
                            const popupContent = `
                                <div class="map-popup" style="min-width: 200px; text-align: center;">
                                    <img src="${loja.logo}" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" 
                                         alt="${loja.nome}" 
                                         onerror="this.src='https://via.placeholder.com/80x80?text=Logo'">
                                    <h6 style="margin: 8px 0; font-weight: bold;">${loja.nome}</h6>
                                    <p style="margin: 5px 0; color: #666;">
                                        <i class="fas fa-map-marker-alt" style="color: #dc3545;"></i> ${loja.cidade}
                                    </p>
                                    ${loja.segmento ? `<p style="margin: 5px 0; color: #666;">
                                        <i class="fas fa-tag" style="color: #28a745;"></i> ${loja.segmento}
                                    </p>` : ''}
                                    <a href="${loja.url}" 
                                       class="btn btn-sm btn-primary" 
                                       target="_blank" 
                                       style="margin-top: 8px; padding: 5px 15px;">
                                        <i class="fas fa-external-link-alt"></i> Visitar Loja
                                    </a>
                                </div>
                            `;
                            
                            marker.bindPopup(popupContent);
                            markers.push(marker);
                            totalLojas++;
                            
                            marker.bindTooltip(loja.nome, {
                                permanent: false,
                                direction: 'top',
                                offset: [0, -10]
                            });
                        }
                    });
                    
                    console.log(`Total de marcadores fallback criados: ${totalLojas}`);
                    
                    if (markers.length > 0) {
                        if (markers.length === 1) {
                            map.setView(markers[0].getLatLng(), 12);
                        } else {
                            const group = new L.featureGroup(markers);
                            map.fitBounds(group.getBounds(), {
                                padding: [20, 20],
                                maxZoom: 15
                            });
                        }
                    }
                }
            } else {
                console.error('Nenhum dado de loja disponível!');
            }
            
            map.setView([-14.235, -51.9253], 4);
            
            // Adicionar marcador informativo
            const infoMarker = L.marker([-14.235, -51.9253]).addTo(map);
            infoMarker.bindPopup(`
                <div style="text-align: center; padding: 10px;">
                    <i class="fas fa-info-circle fa-2x text-primary mb-2"></i>
                    <h6>Nenhuma loja encontrada</h6>
                    <p>Ajuste os filtros para ver as lojas no mapa.</p>
                </div>
            `).openPopup();
        }
        
        // Adicionar controle de camadas (opcional)
        const baseMaps = {
            "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            })
        };
        
        // Adicionar controle de escala
        L.control.scale({
            position: 'bottomleft',
            imperial: false
        }).addTo(map);
        
        // Adicionar informações no canto
        const info = L.control({position: 'topright'});
        info.onAdd = function () {
            const div = L.DomUtil.create('div', 'map-info');
            div.style.background = 'rgba(255,255,255,0.9)';
            div.style.padding = '8px';
            div.style.borderRadius = '5px';
            div.style.fontSize = '12px';
            div.innerHTML = `
                <strong><i class="fas fa-store"></i> ${totalLojas} lojas</strong><br>
                <small>Clique nos marcadores para mais detalhes</small>
            `;
            return div;
        };
        info.addTo(map);
    }
    
    // Função para obter coordenadas aproximadas das cidades (simulação)
    function obterCoordenadasCidade(cidade, lojaId) {
        const coordenadas = {
            // Capitais
            'São Paulo': [-23.5505, -46.6333],
            'Rio de Janeiro': [-22.9068, -43.1729],
            'Belo Horizonte': [-19.9167, -43.9345],
            'Brasília': [-15.8267, -47.9218],
            'Salvador': [-12.9714, -38.5014],
            'Curitiba': [-25.4244, -49.2654],
            'Fortaleza': [-3.7172, -38.5433],
            'Recife': [-8.0476, -34.8770],
            'Porto Alegre': [-30.0346, -51.2177],
            'Manaus': [-3.1190, -60.0217],
            'Belém': [-1.4558, -48.4902],
            'Goiânia': [-16.6869, -49.2648],
            'Vitória': [-20.2976, -40.2958],
            'João Pessoa': [-7.1195, -34.8450],
            'Natal': [-5.7945, -35.2110],
            'Aracaju': [-10.9472, -37.0731],
            'Maceió': [-9.6658, -35.7353],
            'Teresina': [-5.0892, -42.8019],
            'São Luís': [-2.5387, -44.2824],
            'Palmas': [-10.1689, -48.3317],
            'Macapá': [0.0389, -51.0664],
            'Boa Vista': [2.8235, -60.6758],
            'Rio Branco': [-9.9754, -67.8243],
            'Campo Grande': [-20.4697, -54.6201],
            'Cuiabá': [-15.6014, -56.0979],
            'Florianópolis': [-27.5954, -48.5480],
            
            // Região Metropolitana de São Paulo
            'Campinas': [-22.9099, -47.0626],
            'Santos': [-23.9618, -46.3322],
            'São Bernardo do Campo': [-23.6944, -46.5653],
            'Guarulhos': [-23.4538, -46.5333],
            'Osasco': [-23.5329, -46.7918],
            'Santo André': [-23.6543, -46.5339],
            'Ribeirão Preto': [-21.1775, -47.8099],
            'Sorocaba': [-23.5018, -47.4581],
            'Mauá': [-23.6681, -46.4614],
            'São José dos Campos': [-23.2237, -45.9009],
            'Jundiaí': [-23.1864, -46.8842],
            'Piracicaba': [-22.7253, -47.6492],
            'Bauru': [-22.3147, -49.0608],
            'Franca': [-20.5386, -47.4006],
            'São Vicente': [-23.9629, -46.3918],
            'Taubaté': [-23.0365, -45.5554],
            'Limeira': [-22.5647, -47.4017],
            'Suzano': [-23.5425, -46.3108],
            'Taboão da Serra': [-23.609, -46.7587],
            'Sumaré': [-22.8219, -47.2669],
            
            // Rio de Janeiro
            'Niterói': [-22.8833, -43.1036],
            'Duque de Caxias': [-22.7858, -43.3059],
            'Nova Iguaçu': [-22.7592, -43.4503],
            'Petrópolis': [-22.5050, -43.1781],
            'Campos dos Goytacazes': [-21.7574, -41.3298],
            'São Gonçalo': [-22.8267, -43.0537],
            'Volta Redonda': [-22.5231, -44.1036],
            'Magé': [-22.6556, -43.0408],
            'Macaé': [-22.3711, -41.7869],
            'Cabo Frio': [-22.8794, -42.0194],
            
            // Minas Gerais
            'Uberlândia': [-18.9146, -48.2754],
            'Contagem': [-19.9320, -44.0537],
            'Juiz de Fora': [-21.7642, -43.3467],
            'Betim': [-19.9681, -44.1987],
            'Montes Claros': [-16.7289, -43.8636],
            'Uberaba': [-19.7483, -47.9319],
            'Governador Valadares': [-18.8511, -41.9494],
            'Ipatinga': [-19.4683, -42.5369],
            'Sete Lagoas': [-19.4658, -44.2467],
            'Divinópolis': [-20.1439, -44.8839],
            
            // Paraná
            'Londrina': [-23.3045, -51.1696],
            'Maringá': [-23.4205, -51.9331],
            'Ponta Grossa': [-25.0945, -50.1624],
            'Cascavel': [-24.9555, -53.4552],
            'São José dos Pinhais': [-25.5304, -49.2063],
            'Foz do Iguaçu': [-25.5478, -54.5882],
            'Colombo': [-25.2917, -49.2242],
            'Guarapuava': [-25.3842, -51.4617],
            'Paranaguá': [-25.5200, -48.5097],
            'Araucária': [-25.5928, -49.4069],
            
            // Rio Grande do Sul
            'Caxias do Sul': [-29.1678, -51.1794],
            'Pelotas': [-31.7654, -52.3376],
            'Canoas': [-29.9177, -51.1834],
            'Santa Maria': [-29.6868, -53.8069],
            'Gravataí': [-29.9443, -50.9923],
            'Novo Hamburgo': [-29.6783, -51.1306],
            'São Leopoldo': [-29.7604, -51.1496],
            'Rio Grande': [-32.0350, -52.0986],
            'Alvorada': [-29.9903, -51.0811],
            'Passo Fundo': [-28.2636, -52.4069],
            
            // Bahia
            'Feira de Santana': [-12.2664, -38.9663],
            'Vitória da Conquista': [-14.8619, -40.8444],
            'Camaçari': [-12.6975, -38.3242],
            'Itabuna': [-14.7856, -39.2803],
            'Juazeiro': [-9.4144, -40.4986],
            'Lauro de Freitas': [-12.8944, -38.3222],
            'Ilhéus': [-14.7889, -39.0394],
            'Jequié': [-13.8569, -40.0833],
            'Teixeira de Freitas': [-17.5389, -39.7378],
            'Alagoinhas': [-12.1356, -38.4178],
            
            // Ceará
            'Caucaia': [-3.7361, -38.6531],
            'Juazeiro do Norte': [-7.2128, -39.3153],
            'Maracanaú': [-3.8767, -38.6253],
            'Sobral': [-3.6880, -40.3497],
            'Crato': [-7.2339, -39.4092],
            'Itapipoca': [-3.4944, -39.5786],
            'Maranguape': [-3.8889, -38.6836],
            'Iguatu': [-6.3597, -39.2986],
            'Quixadá': [-4.9719, -39.0147],
            'Canindé': [-4.3575, -39.3128],
            
            // Pernambuco
            'Olinda': [-8.0089, -34.8553],
            'Caruaru': [-8.2839, -35.9758],
            'Petrolina': [-9.3891, -40.5030],
            'Paulista': [-7.9406, -34.8728],
            'Cabo de Santo Agostinho': [-8.2114, -35.0344],
            'Camaragibe': [-8.0206, -35.0244],
            'Garanhuns': [-8.8908, -36.4961],
            'Vitória de Santo Antão': [-8.1219, -35.2889],
            'Igarassu': [-7.8347, -34.9064],
            'São Lourenço da Mata': [-8.0031, -35.0192],
            
            // Pará
            'Ananindeua': [-1.3656, -48.3722],
            'Santarém': [-2.4083, -54.7083],
            'Marabá': [-5.3686, -49.1178],
            'Castanhal': [-1.2936, -47.9261],
            'Abaetetuba': [-1.7219, -48.8786],
            'Cametá': [-2.2442, -49.4958],
            'Marituba': [-1.3489, -48.3439],
            'Parauapebas': [-6.0675, -49.9017],
            'Altamira': [-3.2033, -52.2089],
            'Tucuruí': [-3.7661, -49.6728],
            
            // Espírito Santo
            'Vila Velha': [-20.3297, -40.2925],
            'Serra': [-20.1287, -40.3080],
            'Cariacica': [-20.2621, -40.4187],
            'Viana': [-20.3903, -40.4178],
            'Cachoeiro de Itapemirim': [-20.8486, -41.1128],
            'Linhares': [-19.3911, -40.0719],
            'São Mateus': [-18.7169, -39.8586],
            'Colatina': [-19.5397, -40.6306],
            'Guarapari': [-20.6667, -40.4975],
            'Aracruz': [-19.8203, -40.2733],
            
            // Santa Catarina
            'Joinville': [-26.3044, -48.8487],
            'Blumenau': [-26.9194, -49.0661],
            'São José': [-27.6167, -48.6331],
            'Criciúma': [-28.6775, -49.3692],
            'Chapecó': [-27.1009, -52.6156],
            'Itajaí': [-26.9078, -48.6611],
            'Jaraguá do Sul': [-26.4856, -49.0669],
            'Lages': [-27.8156, -50.3264],
            'Palhoça': [-27.6386, -48.6703],
            'Balneário Camboriú': [-26.9906, -48.6356],
            
            // Goiás
            'Aparecida de Goiânia': [-16.8239, -49.2439],
            'Anápolis': [-16.3281, -48.9531],
            'Rio Verde': [-17.7983, -50.9264],
            'Águas Lindas de Goiás': [-15.7536, -48.2828],
            'Valparaíso de Goiás': [-15.8978, -48.1339],
            'Trindade': [-16.6464, -49.4889],
            'Formosa': [-15.5372, -47.3342],
            'Novo Gama': [-15.7892, -48.1339],
            'Itumbiara': [-18.4192, -49.2153],
            'Senador Canedo': [-16.7008, -49.0917],
            
            // Maranhão
            'São José de Ribamar': [-2.5611, -44.0536],
            'Imperatriz': [-5.5264, -47.4917],
            'Timon': [-5.0953, -42.8364],
            'Caxias': [-4.8586, -43.3561],
            'Codó': [-4.4553, -43.8856],
            'Paço do Lumiar': [-2.5286, -44.1047],
            'Açailândia': [-4.9478, -47.5069],
            'Bacabal': [-4.2253, -43.2658],
            'Balsas': [-7.5325, -46.0356],
            'Barra do Corda': [-5.5053, -45.2431],
            
            // Rondônia
            'Ji-Paraná': [-10.8856, -61.9286],
            'Ariquemes': [-9.9131, -63.0406],
            'Cacoal': [-11.4386, -61.4472],
            'Vilhena': [-12.7403, -60.1481],
            'Jaru': [-10.4394, -62.4661],
            'Rolim de Moura': [-11.7272, -61.7808],
            'Guajará-Mirim': [-10.7833, -65.3411],
            'Pimenta Bueno': [-11.6719, -61.1936],
            'Ouro Preto do Oeste': [-10.7486, -62.2156],
            'Presidente Médici': [-11.1742, -61.8983],
            
            // Amazonas
            'Parintins': [-2.6281, -56.7358],
            'Itacoatiara': [-3.1394, -58.4442],
            'Manacapuru': [-3.2969, -60.6175],
            'Coari': [-4.0850, -63.1417],
            'Tefé': [-3.3528, -64.7103],
            'Tabatinga': [-4.2564, -69.9431],
            'Maués': [-3.3831, -57.7189],
            'São Gabriel da Cachoeira': [-0.1308, -67.0892],
            'Humaitá': [-7.5081, -63.0167],
            'Iranduba': [-3.2839, -60.1908]
        };
        
        // Se a cidade estiver na lista, retornar coordenadas precisas
        if (coordenadas[cidade]) {
            // Usar o ID da loja para gerar variação consistente (sempre a mesma posição)
            const seed = lojaId ? parseInt(lojaId) : cidade.length;
            const latVariacao = ((seed * 13) % 100 - 50) / 10000; // Variação fixa baseada no ID
            const lngVariacao = ((seed * 17) % 100 - 50) / 10000; // Variação fixa baseada no ID
            
            return [
                coordenadas[cidade][0] + latVariacao,
                coordenadas[cidade][1] + lngVariacao
            ];
        }
        
        // Se não estiver na lista, usar coordenadas baseadas na região mais provável
        const coordenadasPorRegiao = {
            // Padrão para cidades do Sudeste
            'sudeste': { lat: [-24, -19], lng: [-48, -40] },
            // Padrão para cidades do Sul
            'sul': { lat: [-33, -22], lng: [-57, -48] },
            // Padrão para cidades do Nordeste
            'nordeste': { lat: [-18, -3], lng: [-47, -35] },
            // Padrão para cidades do Norte
            'norte': { lat: [-15, 4], lng: [-73, -49] },
            // Padrão para cidades do Centro-Oeste
            'centro-oeste': { lat: [-23, -8], lng: [-65, -47] }
        };
        
        // Escolher região baseada em palavras-chave da cidade (método simples)
        let regiao = 'sudeste'; // padrão
        
        const cidadeLower = cidade.toLowerCase();
        if (cidadeLower.includes('fortaleza') || cidadeLower.includes('recife') || cidadeLower.includes('salvador') ||
            cidadeLower.includes('natal') || cidadeLower.includes('joão pessoa') || cidadeLower.includes('aracaju') ||
            cidadeLower.includes('maceió') || cidadeLower.includes('teresina') || cidadeLower.includes('são luís')) {
            regiao = 'nordeste';
        } else if (cidadeLower.includes('manaus') || cidadeLower.includes('belém') || cidadeLower.includes('macapá') ||
                   cidadeLower.includes('palmas') || cidadeLower.includes('boa vista') || cidadeLower.includes('rio branco')) {
            regiao = 'norte';
        } else if (cidadeLower.includes('curitiba') || cidadeLower.includes('porto alegre') || cidadeLower.includes('florianópolis')) {
            regiao = 'sul';
        } else if (cidadeLower.includes('goiânia') || cidadeLower.includes('brasília') || cidadeLower.includes('campo grande') ||
                   cidadeLower.includes('cuiabá')) {
            regiao = 'centro-oeste';
        }
        
        const coord = coordenadasPorRegiao[regiao];
        // Usar seed para gerar coordenadas consistentes
        const seed = lojaId ? parseInt(lojaId) : cidade.length;
        const latRandom = (seed * 7) % 100 / 100;
        const lngRandom = (seed * 11) % 100 / 100;
        
        const lat = coord.lat[0] + latRandom * (coord.lat[1] - coord.lat[0]);
        const lng = coord.lng[0] + lngRandom * (coord.lng[1] - coord.lng[0]);
        
        return [lat, lng];
    }
    
    // Função para recarregar o mapa
    function recarregarMapa() {
        console.log('Recarregando mapa...');
        inicializarMapa();
    }
    
    // Função de debug do mapa
    function debugMapa() {
        console.log('=== DEBUG DO MAPA ===');
        console.log('Total de lojas disponíveis (sempre mostradas no mapa):', lojasData.length);
        console.log('Lojas filtradas na listagem:', lojasFiltradas.length);
        console.log('Dados completos das lojas:', lojasData);
        
        // Verificar coordenadas de cada cidade nos dados completos
        const cidadesTestadas = {};
        lojasData.forEach(loja => {
            if (!cidadesTestadas[loja.cidade]) {
                const coords = obterCoordenadasCidade(loja.cidade, loja.id);
                cidadesTestadas[loja.cidade] = coords;
                console.log(`Cidade: ${loja.cidade} -> Coordenadas:`, coords);
            }
        });
        
        console.log('Resumo de coordenadas por cidade:', cidadesTestadas);
        
        // Mostrar status do mapa
        if (map) {
            console.log('Mapa inicializado:', map);
            console.log('Centro atual do mapa:', map.getCenter());
            console.log('Zoom atual:', map.getZoom());
        } else {
            console.log('Mapa não inicializado');
        }
        
        alert(`Debug concluído! Verifique o console do navegador.\n\nResumo:\n- Lojas no mapa: ${lojasData.length}\n- Lojas filtradas na lista: ${lojasFiltradas.length}\n- Cidades diferentes: ${Object.keys(cidadesTestadas).length}\n\nO mapa sempre mostra TODOS os estabelecimentos!`);
    }
    
    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Filtros
        document.getElementById('searchInput').addEventListener('input', filtrarLojas);
        document.getElementById('segmentoFilter').addEventListener('change', filtrarLojas);
        document.getElementById('cidadeFilter').addEventListener('change', filtrarLojas);
        document.getElementById('ordenacao').addEventListener('change', filtrarLojas);
        document.getElementById('apenasAvaliadas').addEventListener('change', filtrarLojas);
        
        // Categorias visuais
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', function() {
                const categoria = this.dataset.categoria;
                filtrarPorCategoria(categoria);
            });
        });
        
        // Comparador
        document.getElementById('btnComparador').addEventListener('click', abrirComparador);
        
        /* NEWSLETTER EVENT LISTENER REMOVIDO POR SOLICITAÇÃO DO USUÁRIO
        document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const dados = {
                nome: document.getElementById('newsletter_nome').value,
                email: document.getElementById('newsletter_email').value,
                cidade: document.getElementById('newsletter_cidade').value,
                promocoes: document.getElementById('newsletter_promocoes').checked,
                novas_lojas: document.getElementById('newsletter_novas_lojas').checked
            };
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;
            
            try {
                const result = await enviarNewsletter(dados);
                if (result.success) {
                    this.innerHTML = `
                        <div class="newsletter-success">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h5>Inscrição Realizada!</h5>
                            <p>${result.message}</p>
                        </div>
                    `;
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('newsletterModal')).hide();
                    }, 3000);
                } else {
                    alert(result.message || 'Erro ao enviar inscrição. Tente novamente.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                alert('Erro ao enviar inscrição. Tente novamente.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        */
        
        // Sistema de estrelas na avaliação
        document.querySelectorAll('.star-input').forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                document.getElementById('rating_value').value = rating;
                
                document.querySelectorAll('.star-input').forEach((s, index) => {
                    if (index < rating) {
                        s.className = 'fas fa-star star-input active';
                    } else {
                        s.className = 'far fa-star star-input';
                    }
                });
            });
            
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.dataset.rating);
                document.querySelectorAll('.star-input').forEach((s, index) => {
                    if (index < rating) {
                        s.className = 'fas fa-star star-input';
                    } else {
                        s.className = 'far fa-star star-input';
                    }
                });
            });
        });
        
        // Form de avaliação
        document.getElementById('avaliacaoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const rating = document.getElementById('rating_value').value;
            if (rating == 0) {
                alert('Por favor, selecione uma avaliação.');
                return;
            }
            
            const dados = {
                estabelecimento_id: document.getElementById('avaliacao_loja_id').value,
                nome_avaliador: document.getElementById('avaliacao_nome').value,
                rating: parseInt(rating),
                comentario: document.getElementById('avaliacao_comentario').value
            };
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;
            
            try {
                const result = await enviarAvaliacao(dados);
                if (result.success) {
                    alert(result.message || 'Avaliação enviada com sucesso!');
                    bootstrap.Modal.getInstance(document.getElementById('avaliacaoModal')).hide();
                    
                    // Atualizar avaliação na interface (opcional)
                    const lojaCard = document.querySelector(`[data-loja-id="${dados.estabelecimento_id}"]`);
                    if (lojaCard) {
                        // Recarregar as avaliações desta loja
                        carregarAvaliacoes(dados.estabelecimento_id).then(avaliacoes => {
                            const ratingDisplay = lojaCard.querySelector('.rating-display');
                            if (ratingDisplay && avaliacoes.total > 0) {
                                ratingDisplay.querySelector('.rating-value').textContent = avaliacoes.media;
                                ratingDisplay.querySelector('.rating-count').textContent = `(${avaliacoes.total})`;
                            }
                        });
                    }
                } else {
                    alert(result.message || 'Erro ao enviar avaliação. Tente novamente.');
                }
            } catch (error) {
                alert('Erro ao enviar avaliação. Tente novamente.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        
        // Inicializar mapa quando modal abrir
        document.getElementById('mapaModal').addEventListener('shown.bs.modal', function() {
            console.log('Modal do mapa aberto, inicializando...');
            setTimeout(() => {
                inicializarMapa();
                
                // Debug adicional: Testar com dados fixos se não houver lojas
                if (lojasFiltradas.length === 0) {
                    console.log('Nenhuma loja filtrada, adicionando dados de teste...');
                    
                    // Adicionar marcadores de teste
                    const testMarkers = [
                        { nome: 'Loja Teste 1', cidade: 'São Paulo', lat: -23.5505, lng: -46.6333 },
                        { nome: 'Loja Teste 2', cidade: 'Rio de Janeiro', lat: -22.9068, lng: -43.1729 },
                        { nome: 'Loja Teste 3', cidade: 'Belo Horizonte', lat: -19.9167, lng: -43.9345 }
                    ];
                    
                    testMarkers.forEach(teste => {
                        const marker = L.marker([teste.lat, teste.lng]).addTo(map);
                        marker.bindPopup(`
                            <div style="text-align: center;">
                                <h6>${teste.nome}</h6>
                                <p><i class="fas fa-map-marker-alt"></i> ${teste.cidade}</p>
                                <small>Dados de teste - Configure suas lojas</small>
                            </div>
                        `);
                    });
                }
            }, 300);
        });
        
        // Carregar lojas inicialmente
        renderizarLojas(lojasData);
        atualizarContadores();
        
        // Inicializar lojasFiltradas com todos os dados inicialmente
        lojasFiltradas = [...lojasData];
        console.log('Lojas iniciais carregadas:', lojasData.length);
        console.log('Lojas filtradas inicializadas:', lojasFiltradas.length);
    });
    </script>
</body>
</html>
<?php
// Fechar conexão com o banco de dados ao final do script
if (isset($db) && $db instanceof mysqli) {
    $db->close();
}
?>