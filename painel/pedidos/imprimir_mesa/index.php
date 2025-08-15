<?php
// CORE
include('../../../_core/_includes/config.php');

// RESTRICT  
restrict_estabelecimento();
restrict_expirado();

// Obter parâmetros
$id = mysqli_real_escape_string($db_con, $_GET['id']);
$forma_pagamento = mysqli_real_escape_string($db_con, $_GET['forma_pagamento']);
$taxa_garcom = floatval($_GET['taxa_garcom']);
$eid = $_SESSION['estabelecimento']['id'];

// Buscar pedido
$pedido = mysqli_query($db_con, "
    SELECT p.*, e.nome as estabelecimento_nome, e.endereco_rua as est_rua, 
           e.endereco_numero as est_numero, e.endereco_bairro as est_bairro,
           e.endereco_cep as est_cep, e.contato_whatsapp as est_telefone
    FROM pedidos p
    LEFT JOIN estabelecimentos e ON p.rel_estabelecimentos_id = e.id
    WHERE p.id = '$id' 
    LIMIT 1
");

if(mysqli_num_rows($pedido) == 0) {
    die("<script>
        alert('Pedido #$id não encontrado!');
        window.close();
    </script>");
}

$data = mysqli_fetch_array($pedido);

// Verificar se o pedido pertence ao estabelecimento atual
if(!empty($data['rel_estabelecimentos_id']) && $data['rel_estabelecimentos_id'] != $eid) {
    die("<script>
        alert('Pedido #$id não pertence a este estabelecimento!');
        window.close();
    </script>");
}

// Buscar informações de mesa e garçom
$mesa_info = null;
$produtos_info = null;
$subtotal = 0;

if($data['rel_mesas_id']) {
    $mesa_query = mysqli_query($db_con, "
        SELECT m.numero, g.nome as garcom_nome, g.telefone as garcom_telefone
        FROM mesa_pedidos mp
        JOIN mesas m ON mp.rel_mesas_id = m.id
        LEFT JOIN garcons g ON mp.rel_garcons_id = g.id
        WHERE mp.rel_pedido_id = '{$data['id']}'
        LIMIT 1
    ");

    $lista_produtos_query = mysqli_query($db_con, "SELECT 
                mpi.nome_produto, 
                mpi.preco_unitario, 
                mpi.quantidade,
                (mpi.preco_unitario * mpi.quantidade) AS subtotal_item
              FROM mesa_pedido_itens mpi
              JOIN mesa_pedidos mp ON mpi.rel_mesa_pedidos_id = mp.id
              WHERE mp.rel_pedido_id = '{$data['id']}'
    ");

    if(mysqli_num_rows($mesa_query) > 0) {
        $mesa_info = mysqli_fetch_array($mesa_query);
    }

    if(mysqli_num_rows($lista_produtos_query) > 0) {
        $produtos_info = mysqli_fetch_all($lista_produtos_query, MYSQLI_ASSOC);
        foreach($produtos_info as $produto) {
            $subtotal += $produto['subtotal_item'];
        }
    }
}

// Calcular valores
$valor_taxa = $subtotal * ($taxa_garcom / 100);
$total_final = $subtotal + $valor_taxa;
$estabelecimento_nome = $data['estabelecimento_nome'] ?: "Estabelecimento";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comprovante #<?php echo $id; ?></title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 5px; }
        .comprovante { width: 100%; max-width: 300px; font-size: 14px }
        strong { font-weight: bold; }
        hr { border: none; border-top: 1px dashed #000; margin: 5px 0; }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                setTimeout(function() { window.close(); }, 2000);
            }, 500);
        };
    </script>
</head>
<body>
    <div class="comprovante">
        <strong><?php echo htmlspecialchars($estabelecimento_nome); ?></strong><br>
        <?php if(!empty($data['est_rua'])): ?>
            <?php echo htmlspecialchars($data['est_rua'] . ', ' . $data['est_numero']); ?>
            <?php if(!empty($data['est_bairro'])) echo ' - ' . htmlspecialchars($data['est_bairro']); ?>
            <br>
        <?php endif; ?>
        <?php if(!empty($data['est_telefone'])): ?>
            <?php echo htmlspecialchars($data['est_telefone']); ?><br>
        <?php endif; ?>
        <hr>
        <strong>Pedido #<?php echo $id; ?></strong><br>
        <hr>
        <?php echo date('d/m/Y H:i:s'); ?><br>
        <hr>
        
        <?php if ($mesa_info): ?>
            <strong>Mesa:</strong> <?php echo htmlspecialchars($mesa_info['numero']); ?><br>
            <?php if(!empty($mesa_info['garcom_nome'])): ?>
                <strong>Garçom:</strong> <?php echo htmlspecialchars($mesa_info['garcom_nome']); ?><br>
            <?php endif; ?>
            <br>
        <?php endif; ?>
        
        <strong>Forma de pagamento:</strong><br>
        <?php echo htmlspecialchars($forma_pagamento); ?><br>
        <hr>
        
        <strong>PRODUTOS</strong><br>
        <hr>
        <br>
        
        <?php if(!empty($produtos_info)): ?>
            <?php foreach($produtos_info as $item): ?>
                <strong><?php echo $item['quantidade']; ?> x</strong> <?php echo $item['nome_produto']; ?><br>
                <strong>Valor:</strong> R$ <?php echo number_format($item['subtotal_item'], 2, ',', '.'); ?><br>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            Nenhum produto encontrado<br>
        <?php endif; ?>
        
        <hr>
        <strong>Subtotal:</strong> R$ <?php echo number_format($subtotal, 2, ',', '.'); ?><br>
        
        <?php if($taxa_garcom > 0): ?>
            <strong>Taxa do Garçom (<?php echo number_format($taxa_garcom, 1); ?>%):</strong> 
            R$ <?php echo number_format($valor_taxa, 2, ',', '.'); ?><br>
        <?php endif; ?>
        
        <strong>Total:</strong> R$ <?php echo number_format($total_final, 2, ',', '.'); ?><br>
        <hr>
        <br>
        
        
        Obrigado pela preferência!<br>
    </div>
</body>
</html>