<?php
// Incluir configurações
include('../_core/_includes/config.php');

// Verificar se há sessão ativa
session_start();

// Verificar se está logado
if (!isset($_SESSION['garcom_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Verificar se foi enviado ID do produto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do produto inválido']);
    exit;
}

$produto_id = (int)$_GET['id'];

// Buscar dados do estabelecimento pelo subdomínio
$host_parts = explode('.', $_SERVER['HTTP_HOST']);
$insubdominio = $host_parts[0];

$estabelecimento_query = mysqli_query($db_con, "SELECT * FROM estabelecimentos WHERE subdominio = '$insubdominio' AND status = '1'");

if (mysqli_num_rows($estabelecimento_query) == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Estabelecimento não encontrado']);
    exit;
}

$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);

// Buscar produto específico
$produto_query = mysqli_query($db_con, "
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    LEFT JOIN categorias c ON p.rel_categorias_id = c.id 
    WHERE p.id = '$produto_id' 
    AND p.rel_estabelecimentos_id = '".$estabelecimento_data['id']."' 
    AND p.status = '1' 
    AND p.visible = '1'
");

if (mysqli_num_rows($produto_query) == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Produto não encontrado']);
    exit;
}

$produto = mysqli_fetch_array($produto_query);

// Preparar resposta JSON
$response = [
    'id' => $produto['id'],
    'nome' => $produto['nome'],
    'descricao' => $produto['descricao'] ?? '',
    'valor' => (float)$produto['valor'],
    'imagem' => $produto['imagem'] ?? '',
    'categoria' => $produto['categoria_nome'] ?? 'Sem Categoria',
    'thumb_url' => !empty($produto['imagem']) ? thumber($produto['imagem'], 400) : ''
];

header('Content-Type: application/json');
echo json_encode($response);
?>
