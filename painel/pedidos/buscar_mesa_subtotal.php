<?php

include('../../_core/_includes/config.php');

header('Content-Type: application/json');



$id = intval($_GET['id'] ?? 0);

$subtotal = 0;

$sucesso = false;



if ($id > 0) {
    $sucesso = false;
    $itens = array();
    $subtotal = 0;

    // Query para buscar os itens
    $query = "SELECT 
                mpi.nome_produto, 
                mpi.preco_unitario, 
                mpi.quantidade,
                (mpi.preco_unitario * mpi.quantidade) AS subtotal_item
              FROM mesa_pedido_itens mpi
              JOIN mesa_pedidos mp ON mpi.rel_mesa_pedidos_id = mp.id
              WHERE mp.rel_pedido_id = '$id'";
    
    $sql = mysqli_query($db_con, $query);
    
    if ($sql && mysqli_num_rows($sql) > 0) {
        $sucesso = true;
        
        while ($row = mysqli_fetch_assoc($sql)) {
            $itens[] = array(
                'nome_produto' => $row['nome_produto'],
                'preco_unitario' => number_format($row['preco_unitario'], 2, '.', ''),
                'quantidade' => $row['quantidade'],
                'subtotal_item' => number_format($row['subtotal_item'], 2, '.', '')
            );
            $subtotal += $row['preco_unitario'] * $row['quantidade'];
        }
    }
}

echo json_encode([
    'sucesso' => $sucesso,
    'itens' => $itens,
    'subtotal' => number_format($subtotal, 2, '.', ''),
    'total_itens' => count($itens)
]);

