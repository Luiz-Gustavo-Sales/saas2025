<?php 

include('../_core/_includes/config.php'); 

session_start();



// Verificar se garçom está logado

if (!isset($_SESSION['garcom_id'])) {

    header("Location: login.php");

    exit;

}



$garcom_id = $_SESSION['garcom_id'];

$estabelecimento_id = $_SESSION['garcom_estabelecimento_id'];

$mesa_id = mysqli_real_escape_string($db_con, $_GET['id']);



// Buscar dados da mesa

$mesa_query = mysqli_query($db_con, "SELECT * FROM mesas WHERE id = '$mesa_id' AND rel_estabelecimentos_id = '$estabelecimento_id' AND status = '1'");

if (mysqli_num_rows($mesa_query) == 0) {

    header("Location: index.php");

    exit;

}

$mesa_data = mysqli_fetch_array($mesa_query);



// Buscar dados do estabelecimento

$estabelecimento_query = mysqli_query($db_con, "SELECT * FROM estabelecimentos WHERE id = '$estabelecimento_id'");

$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);



// Verificar se existe pedido em andamento para esta mesa

$pedido_andamento_query = mysqli_query($db_con, "SELECT * FROM mesa_pedidos WHERE rel_mesas_id = '$mesa_id' AND status = 'andamento' ORDER BY id DESC LIMIT 1");

$tem_pedido_andamento = mysqli_num_rows($pedido_andamento_query) > 0;



if ($tem_pedido_andamento) {

    $pedido_andamento = mysqli_fetch_array($pedido_andamento_query);

    $pedido_id = $pedido_andamento['id'];

} else {

    $pedido_id = null;

}



// Buscar produtos do estabelecimento

$busca = isset($_GET['busca']) ? mysqli_real_escape_string($db_con, $_GET['busca']) : '';

$where_busca = $busca ? " AND nome LIKE '%$busca%'" : '';

$produtos_query = mysqli_query($db_con, "SELECT id, nome, valor, oferta, valor_promocional FROM produtos WHERE rel_estabelecimentos_id = '$estabelecimento_id' AND status = '1' AND visible = '1' $where_busca ORDER BY nome");



// Buscar itens já adicionados na mesa (se houver pedido em andamento)

$itens_mesa = array();

$total_mesa = 0;

if ($tem_pedido_andamento) {

    $itens_query = mysqli_query($db_con, "SELECT * FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$pedido_id' ORDER BY id");

    while ($item = mysqli_fetch_array($itens_query)) {

        $itens_mesa[] = $item;

        $total_mesa += ($item['preco_unitario'] * $item['quantidade']);

    }

}



// Processar ações AJAX

if (isset($_POST['action'])) {

    header('Content-Type: application/json');

    

    if ($_POST['action'] == 'adicionar_produto') {

        $produto_id = mysqli_real_escape_string($db_con, $_POST['produto_id']);

        $quantidade = intval($_POST['quantidade']) ?: 1;

        

        // Se não há pedido em andamento, criar um

        if (!$tem_pedido_andamento) {

            // Verificar se mesa e garçom existem antes de criar pedido

            $mesa_check = mysqli_query($db_con, "SELECT id FROM mesas WHERE id = '$mesa_id' AND rel_estabelecimentos_id = '$estabelecimento_id' AND status = '1'");

            $garcom_check = mysqli_query($db_con, "SELECT id FROM garcons WHERE id = '$garcom_id' AND rel_estabelecimentos_id = '$estabelecimento_id' AND ativo = '1'");

            

            if (mysqli_num_rows($mesa_check) == 0) {

                echo json_encode(['success' => false, 'message' => 'Mesa não encontrada ou inativa']);

                exit;

            }

            

            if (mysqli_num_rows($garcom_check) == 0) {

                echo json_encode(['success' => false, 'message' => 'Garçom não encontrado ou inativo']);

                exit;

            }

            

            // Verificar estrutura da tabela mesa_pedidos

            $estrutura_check = mysqli_query($db_con, "SHOW COLUMNS FROM mesa_pedidos");

            $tem_data_hora = false;

            while ($col = mysqli_fetch_array($estrutura_check)) {

                if ($col['Field'] == 'data_hora') {

                    $tem_data_hora = true;

                    break;

                }

            }

            

            if ($tem_data_hora) {

                $insert_pedido = "INSERT INTO mesa_pedidos (rel_mesas_id, rel_garcons_id, rel_estabelecimentos_id, status, data_hora) VALUES ('$mesa_id', '$garcom_id', '$estabelecimento_id', 'andamento', NOW())";

            } else {

                $insert_pedido = "INSERT INTO mesa_pedidos (rel_mesas_id, rel_garcons_id, rel_estabelecimentos_id, status) VALUES ('$mesa_id', '$garcom_id', '$estabelecimento_id', 'andamento')";

            }

            

            if (mysqli_query($db_con, $insert_pedido)) {

                $pedido_id = mysqli_insert_id($db_con);

                $tem_pedido_andamento = true; // Atualizar flag

            } else {

                $error = mysqli_error($db_con);

                echo json_encode(['success' => false, 'message' => 'Erro ao iniciar pedido: ' . $error]);

                exit;

            }

        }

        

        // Buscar dados do produto

        $produto_query = mysqli_query($db_con, "SELECT * FROM produtos WHERE id = '$produto_id'");

        if (mysqli_num_rows($produto_query) > 0) {

            $produto_data = mysqli_fetch_array($produto_query);

            $nome_produto = $produto_data['nome'];

            $preco = $produto_data['oferta'] == '1' ? $produto_data['valor_promocional'] : $produto_data['valor'];

            

            // Verificar se já existe este produto no pedido

            $item_existente_query = mysqli_query($db_con, "SELECT id FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$pedido_id' AND rel_produtos_id = '$produto_id' AND (rel_produto_variacoes_id IS NULL OR rel_produto_variacoes_id = 0)");

            

            if (mysqli_num_rows($item_existente_query) > 0) {

                // Aumentar quantidade

                $update_query = "UPDATE mesa_pedido_itens SET quantidade = quantidade + $quantidade WHERE rel_mesa_pedidos_id = '$pedido_id' AND rel_produtos_id = '$produto_id' AND (rel_produto_variacoes_id IS NULL OR rel_produto_variacoes_id = 0)";

                if (mysqli_query($db_con, $update_query)) {

                    echo json_encode(['success' => true]);

                } else {

                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar quantidade: ' . mysqli_error($db_con)]);

                }

            } else {

                // Adicionar novo item

                $insert_item = "INSERT INTO mesa_pedido_itens (rel_mesa_pedidos_id, rel_produtos_id, nome_produto, preco_unitario, quantidade) VALUES ('$pedido_id', '$produto_id', '$nome_produto', '$preco', '$quantidade')";

                if (mysqli_query($db_con, $insert_item)) {

                    echo json_encode(['success' => true]);

                } else {

                    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar item: ' . mysqli_error($db_con)]);

                }

            }

        } else {

            echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);

        }

        exit;

    }

    

    if ($_POST['action'] == 'remover_item') {

        $item_id = mysqli_real_escape_string($db_con, $_POST['item_id']);

        

        $delete_query = "DELETE FROM mesa_pedido_itens WHERE id = '$item_id' AND rel_mesa_pedidos_id = '$pedido_id'";

        if (mysqli_query($db_con, $delete_query)) {

            echo json_encode(['success' => true]);

        } else {

            echo json_encode(['success' => false, 'message' => 'Erro ao remover item']);

        }

        exit;

    }

    

    if ($_POST['action'] == 'finalizar_pedido') {

        if ($tem_pedido_andamento && count($itens_mesa) > 0) {

            // Calcular total

            $total = 0;

            foreach ($itens_mesa as $item) {

                $total += ($item['preco_unitario'] * $item['quantidade']);

            }

            

            // Criar pedido na tabela principal

            $nome_cliente = "Mesa " . $mesa_data['numero'];

            $data_hora = date('Y-m-d H:i:s');

            

            $insert_pedido_principal = "INSERT INTO pedidos (

                rel_segmentos_id, 

                rel_estabelecimentos_id, 

                nome, 

                whatsapp, 

                forma_entrega, 

                origem,

                rel_mesas_id,

                rel_garcons_id,

                forma_pagamento, 

                status, 

                data_hora, 

                v_pedido

            ) VALUES (

                (SELECT segmento FROM estabelecimentos WHERE id = '$estabelecimento_id'),

                '$estabelecimento_id',

                '$nome_cliente',

                '',

                '3',

                'mesa',

                '$mesa_id',

                '$garcom_id',

                '1',

                '0',

                '$data_hora',

                '$total'

            )";

            

            if (mysqli_query($db_con, $insert_pedido_principal)) {

                $novo_pedido_id = mysqli_insert_id($db_con);

                

                // Transferir itens para tabela principal de pedidos

                foreach ($itens_mesa as $item) {

                    $insert_item_principal = "INSERT INTO pedido_itens (

                        rel_pedidos_id,

                        rel_produtos_id,

                        rel_produto_variacoes_id,

                        nome_produto,

                        preco_unitario,

                        quantidade,

                        observacoes

                    ) VALUES (

                        '$novo_pedido_id',

                        '{$item['rel_produtos_id']}',

                        " . ($item['rel_produto_variacoes_id'] ? "'{$item['rel_produto_variacoes_id']}'" : "NULL") . ",

                        '{$item['nome_produto']}',

                        '{$item['preco_unitario']}',

                        '{$item['quantidade']}',

                        '{$item['observacoes']}'

                    )";

                    mysqli_query($db_con, $insert_item_principal);

                }

                

                // Marcar pedido da mesa como finalizado

                mysqli_query($db_con, "UPDATE mesa_pedidos SET status = 'finalizado', rel_pedido_id = '$novo_pedido_id' WHERE id = '$pedido_id'");

                

                echo json_encode(['success' => true, 'pedido_id' => $novo_pedido_id]);

            } else {

                echo json_encode(['success' => false, 'message' => 'Erro ao finalizar pedido']);

            }

        } else {

            echo json_encode(['success' => false, 'message' => 'Nenhum item no pedido']);

        }

        exit;

    }

}



// Endpoint AJAX para dados da sacola

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {

    header('Content-Type: application/json');

    

    // Recarregar dados atualizados

    $pedido_andamento_query = mysqli_query($db_con, "SELECT * FROM mesa_pedidos WHERE rel_mesas_id = '$mesa_id' AND status = 'andamento' ORDER BY id DESC LIMIT 1");

    $tem_pedido_andamento = mysqli_num_rows($pedido_andamento_query) > 0;

    

    $itens_count = 0;

    $total_mesa = 0;

    

    if ($tem_pedido_andamento) {

        $pedido_andamento = mysqli_fetch_array($pedido_andamento_query);

        $pedido_id = $pedido_andamento['id'];

        

        $itens_query = mysqli_query($db_con, "SELECT * FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$pedido_id'");

        $itens_count = mysqli_num_rows($itens_query);

        

        while ($item = mysqli_fetch_array($itens_query)) {

            $total_mesa += ($item['preco_unitario'] * $item['quantidade']);

        }

    }

    

    echo json_encode([

        'itens_count' => $itens_count,

        'total' => $total_mesa,

        'total_formatado' => number_format($total_mesa, 2, ',', '.')

    ]);

    exit;

}



// Endpoint AJAX para itens detalhados do carrinho

if (isset($_GET['itens_ajax']) && $_GET['itens_ajax'] == '1') {

    header('Content-Type: application/json');

    

    // Recarregar dados atualizados

    $pedido_andamento_query = mysqli_query($db_con, "SELECT * FROM mesa_pedidos WHERE rel_mesas_id = '$mesa_id' AND status = 'andamento' ORDER BY id DESC LIMIT 1");

    $tem_pedido_andamento = mysqli_num_rows($pedido_andamento_query) > 0;

    

    $itens = array();

    $total_mesa = 0;

    

    if ($tem_pedido_andamento) {

        $pedido_andamento = mysqli_fetch_array($pedido_andamento_query);

        $pedido_id = $pedido_andamento['id'];

        

        $itens_query = mysqli_query($db_con, "SELECT * FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$pedido_id' ORDER BY id");

        

        while ($item = mysqli_fetch_array($itens_query)) {

            $itens[] = array(

                'id' => $item['id'],

                'nome_produto' => $item['nome_produto'],

                'preco_unitario' => $item['preco_unitario'],

                'quantidade' => $item['quantidade'],

                'observacoes' => $item['observacoes'] ?? ''

            );

            $total_mesa += ($item['preco_unitario'] * $item['quantidade']);

        }

    }

    

    echo json_encode([

        'success' => true,

        'itens' => $itens,

        'total' => $total_mesa,

        'total_formatado' => number_format($total_mesa, 2, ',', '.')

    ]);

    exit;

}

?>



<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mesa <?php echo $mesa_data['numero']; ?> - <?php echo $estabelecimento_data['nome']; ?></title>

    <meta name="theme-color" content="#667eea">

    

    <!-- Bootstrap CSS -->

    <link href="../_cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <link href="../_cdn/lineicons/LineIcons.css" rel="stylesheet">

    <!-- Font Awesome para ícones mais bonitos -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    

    <style>

        :root {

            --primary-color: #667eea;

            --secondary-color: #764ba2;

            --success-color: #27ae60;

            --danger-color: #e74c3c;

        }

        

        body {

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background: #f8f9fa;

            padding-bottom: 80px; /* Espaço para botão fixo */

            padding-top: 0;

        }

        

        .header-mesa {

            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));

            color: white;

            padding: 1rem;

            position: sticky;

            top: 0;

            z-index: 1020;

            box-shadow: 0 2px 10px rgba(0,0,0,0.1);

        }

        

        .back-btn {

            background: rgba(255,255,255,0.2);

            border: none;

            color: white;

            padding: 0.5rem 1rem;

            border-radius: 25px;

            text-decoration: none;

            font-size: 0.9rem;

            flex-shrink: 0;

        }

        

        .mesa-info {

            text-align: center;

            flex: 1;

            margin: 0 1rem;

        }

        

        .mesa-numero {

            font-size: 2rem;

            font-weight: bold;

            margin-bottom: 0.5rem;

        }

        

        .mesa-status {

            font-size: 1rem;

            opacity: 0.9;

        }

        

        .sacola-icon {

            position: fixed;

            top: 20px;

            right: 20px;

            color: white;

            font-size: 1.5rem;

            cursor: pointer;

            padding: 1rem;

            border-radius: 50%;

            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));

            transition: all 0.3s ease;

            z-index: 1030;

            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);

            border: 3px solid white;

            width: 60px;

            height: 60px;

            display: flex;

            align-items: center;

            justify-content: center;

        }

        

        .sacola-icon:hover {

            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));

            transform: scale(1.1);

            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);

        }

        

        .sacola-contador {

            position: absolute;

            top: -5px;

            right: -5px;

            background: linear-gradient(135deg, #e74c3c, #c0392b);

            color: white;

            border-radius: 50%;

            width: 26px;

            height: 26px;

            font-size: 0.85rem;

            font-weight: bold;

            display: flex;

            align-items: center;

            justify-content: center;

            min-width: 26px;

            border: 3px solid white;

            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);

        }

        

        .sacola-icon.empty {

            opacity: 0.6;

            background: linear-gradient(135deg, #95a5a6, #7f8c8d);

            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);

        }

        

        .content-container {

            padding: 1rem;

            position: relative;

            z-index: 1;

            margin-top: 10px;

            padding-top: 80px; /* Espaço para sacola flutuante */

        }

        

        .search-section {

            margin-bottom: 1.5rem;

            position: relative;

            z-index: 2;

        }

        

        .search-section .input-group {

            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);

            border-radius: 25px;

            overflow: hidden;

            border: 2px solid var(--primary-color);

        }

        

        .search-section .input-group-text {

            background: var(--primary-color);

            color: white;

            border: none;

            padding: 1rem 1.2rem;

            font-size: 1.1rem;

        }

        

        .search-section .form-control {

            border: none;

            padding: 1rem 1.2rem;

            font-size: 1.1rem;

            background: white;

        }

        

        .search-section .form-control:focus {

            border: none;

            box-shadow: none;

            outline: none;

        }

        

        .search-section .btn-outline-secondary {

            border: none;

            background: #f8f9fa;

            color: var(--primary-color);

            padding: 1rem 1.2rem;

            transition: all 0.3s ease;

        }

        

        .search-section .btn-outline-secondary:hover {

            background: var(--primary-color);

            color: white;

        }

        

        .section-title {

            font-size: 1.3rem;

            font-weight: 600;

            margin-bottom: 1rem;

            color: var(--primary-color);

        }

        

        .produto-item {

            background: white;

            border-radius: 10px;

            padding: 1rem;

            margin-bottom: 0.5rem;

            box-shadow: 0 2px 5px rgba(0,0,0,0.1);

            display: flex;

            justify-content: space-between;

            align-items: center;

        }

        

        .produto-info {

            flex: 1;

        }

        

        .produto-nome {

            font-weight: 600;

            margin-bottom: 0.3rem;

        }

        

        .produto-preco {

            color: var(--success-color);

            font-weight: bold;

            font-size: 1.1rem;

        }

        

        .produto-actions {

            display: flex;

            flex-direction: column;

            gap: 0.5rem;

            align-items: flex-end;

        }

        

        .quantidade-group {

            display: flex;

            align-items: center;

            gap: 0.3rem;

            background: #f8f9fa;

            border-radius: 20px;

            padding: 0.2rem;

        }

        

        .btn-quantidade {

            background: var(--primary-color);

            color: white;

            border: none;

            border-radius: 50%;

            width: 30px;

            height: 30px;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 1.1rem;

            font-weight: bold;

        }

        

        .btn-quantidade:hover {

            background: var(--secondary-color);

        }

        

        .input-quantidade {

            width: 50px;

            text-align: center;

            border: none;

            background: transparent;

            font-weight: bold;

            font-size: 1rem;

        }

        

        .input-quantidade:focus {

            outline: none;

        }

        

        .btn-adicionar {

            background: var(--primary-color);

            color: white;

            border: none;

            border-radius: 20px;

            padding: 0.5rem 1rem;

            font-size: 0.9rem;

            cursor: pointer;

            transition: all 0.3s ease;

        }

        

        .btn-adicionar:hover {

            background: var(--secondary-color);

            transform: translateY(-1px);

        }

        

        .carrinho-section {

            background: white;

            border-radius: 10px;

            padding: 1rem;

            margin-top: 2rem;

            box-shadow: 0 2px 10px rgba(0,0,0,0.1);

            transition: all 0.3s ease;

        }

        

        .carrinho-section.hidden {

            display: none;

        }

        

        .carrinho-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 1rem;

            padding-bottom: 0.5rem;

            border-bottom: 2px solid #f8f9fa;

        }

        

        .btn-toggle-carrinho {

            background: none;

            border: none;

            color: var(--primary-color);

            font-size: 1.2rem;

            cursor: pointer;

        }

        

        .item-carrinho {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 0.5rem 0;

            border-bottom: 1px solid #eee;

            transition: all 0.3s ease;

            opacity: 1;

            transform: translateX(0);

        }

        

        .item-carrinho.novo-item {

            animation: slideInRight 0.5s ease;

        }

        

        @keyframes slideInRight {

            from {

                opacity: 0;

                transform: translateX(100%);

            }

            to {

                opacity: 1;

                transform: translateX(0);

            }

        }

        

        .item-carrinho:last-child {

            border-bottom: none;

        }

        

        .item-info {

            flex: 1;

        }

        

        .item-nome {

            font-weight: 600;

            margin-bottom: 0.2rem;

        }

        

        .item-detalhes {

            font-size: 0.9rem;

            color: #666;

        }

        

        .btn-remover {

            background: var(--danger-color);

            color: white;

            border: none;

            border-radius: 50%;

            width: 30px;

            height: 30px;

            font-size: 0.8rem;

            cursor: pointer;

        }

        

        .total-carrinho {

            font-size: 1.3rem;

            font-weight: bold;

            text-align: center;

            margin: 1rem 0;

            color: var(--primary-color);

        }

        

        .btn-finalizar {

            background: var(--success-color);

            color: white;

            border: none;

            border-radius: 25px;

            padding: 1rem 2rem;

            width: 100%;

            font-size: 1.1rem;

            font-weight: 600;

            cursor: pointer;

            position: fixed;

            bottom: 10px;

            left: 10px;

            right: 10px;

            margin: 0 auto;

            max-width: 500px;

            z-index: 1000;

        }

        

        .btn-finalizar:hover {

            background: #229954;

        }

        

        .btn-finalizar:disabled {

            background: #ccc;

            cursor: not-allowed;

        }

        

        .empty-message {

            text-align: center;

            color: #666;

            padding: 2rem;

        }

        

        .loading {

            text-align: center;

            padding: 1rem;

        }

        

        .lni-spinning {

            animation: spin 1s linear infinite;

        }

        

        @keyframes spin {

            from { transform: rotate(0deg); }

            to { transform: rotate(360deg); }

        }

        

        @media (max-width: 768px) {

            .produto-item {

                flex-direction: column;

                align-items: flex-start;

                gap: 1rem;

            }

            

            .produto-actions {

                width: 100%;

                flex-direction: row;

                justify-content: space-between;

                align-items: center;

            }

            

            .btn-finalizar {

                left: 5px;

                right: 5px;

                max-width: none;

            }

            

            .search-section .input-group-text,

            .search-section .form-control,

            .search-section .btn-outline-secondary {

                padding: 0.8rem 1rem;

                font-size: 1rem;

            }

            

            .mesa-info {

                flex: 1;

                margin: 0 0.5rem;

                text-align: center;

            }

            

            .mesa-numero {

                font-size: 1.5rem;

            }

            

            .sacola-icon {

                top: 15px;

                right: 15px;

                font-size: 1.3rem;

                padding: 0.8rem;

                width: 50px;

                height: 50px;

            }

            

            .sacola-contador {

                width: 22px;

                height: 22px;

                font-size: 0.8rem;

                top: -3px;

                right: -3px;

            }

        }

    </style>

</head>

<body>

    <header class="header-mesa">

        <div class="d-flex justify-content-between align-items-center">

            <a href="index.php" class="back-btn">

                <i class="fas fa-arrow-left"></i> Voltar

            </a>

            <div class="mesa-info">

                <div class="mesa-numero">Mesa <?php echo $mesa_data['numero']; ?></div>

                <div class="mesa-status">

                    <?php if ($tem_pedido_andamento && count($itens_mesa) > 0) { ?>

                        Ocupada - Total: R$ <?php echo number_format($total_mesa, 2, ',', '.'); ?>

                    <?php } else { ?>

                        Disponível

                    <?php } ?>

                </div>

            </div>

            <div style="width: 80px;"></div> <!-- Espaçador -->

        </div>

    </header>



    <!-- Sacola Flutuante -->

    <div class="sacola-icon <?php echo (!$tem_pedido_andamento || count($itens_mesa) == 0) ? 'empty' : ''; ?>" onclick="toggleCarrinho()">

        <i class="fas fa-shopping-bag"></i>

        <?php if ($tem_pedido_andamento && count($itens_mesa) > 0) { ?>

        <span class="sacola-contador"><?php echo count($itens_mesa); ?></span>

        <?php } ?>

    </div>



    <div class="content-container">

        <!-- Barra de Pesquisa -->

        <div class="search-section">

            <div class="input-group mb-3">

                <span class="input-group-text"><i class="fas fa-search"></i></span>

                <input type="text" class="form-control" id="search-input" placeholder="Digite o nome do produto que deseja buscar..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">

                <button class="btn btn-outline-secondary" type="button" onclick="limparPesquisa()" title="Limpar pesquisa">

                    <i class="fas fa-times"></i>

                </button>

            </div>

        </div>



        <!-- Lista de Produtos -->

        <div class="section-title">Produtos Disponíveis</div>

        

        <?php if (mysqli_num_rows($produtos_query) > 0) { ?>

            <div id="produtos-lista">

                <?php while ($produto = mysqli_fetch_array($produtos_query)) { 

                    $preco_final = $produto['oferta'] == '1' ? $produto['valor_promocional'] : $produto['valor'];

                ?>

                <div class="produto-item">

                    <div class="produto-info">

                        <div class="produto-nome"><?php echo $produto['nome']; ?></div>

                        <div class="produto-preco">R$ <?php echo number_format($preco_final, 2, ',', '.'); ?></div>

                    </div>

                    <div class="produto-actions">

                        <div class="quantidade-group">

                            <button class="btn-quantidade" onclick="diminuirQuantidade(<?php echo $produto['id']; ?>)">-</button>

                            <input type="number" id="qtd-<?php echo $produto['id']; ?>" class="input-quantidade" value="1" min="1" max="99">

                            <button class="btn-quantidade" onclick="aumentarQuantidade(<?php echo $produto['id']; ?>)">+</button>

                        </div>

                        <button class="btn-adicionar" onclick="adicionarProduto(<?php echo $produto['id']; ?>, this)">

                            <i class="lni lni-plus"></i> Adicionar

                        </button>

                    </div>

                </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-message">

                <?php if ($busca) { ?>

                    <p>Nenhum produto encontrado para "<strong><?php echo htmlspecialchars($busca); ?></strong>"</p>

                    <button class="btn btn-primary" onclick="limparPesquisa()">Ver todos os produtos</button>

                <?php } else { ?>

                    <p>Nenhum produto cadastrado no estabelecimento.</p>

                <?php } ?>

            </div>

        <?php } ?>



        <!-- Carrinho da Mesa -->

        <?php if ($tem_pedido_andamento && count($itens_mesa) > 0) { ?>

        <div class="carrinho-section" id="carrinho-section">

            <div class="carrinho-header">

                <div class="section-title" style="margin-bottom: 0;">Itens da Mesa (<?php echo count($itens_mesa); ?>)</div>

                <button class="btn-toggle-carrinho" onclick="toggleCarrinho()">

                    <i class="lni lni-chevron-up" id="toggle-icon"></i>

                </button>

            </div>

            

            <div id="itens-carrinho">

                <?php foreach ($itens_mesa as $item) { 

                    $subtotal = $item['preco_unitario'] * $item['quantidade'];

                ?>

                <div class="item-carrinho">

                    <div class="item-info">

                        <div class="item-nome"><?php echo $item['nome_produto']; ?></div>

                        <div class="item-detalhes">

                            Qtd: <?php echo $item['quantidade']; ?> × R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>

                            = R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>

                        </div>

                    </div>

                    <button class="btn-remover" onclick="removerItem(<?php echo $item['id']; ?>)">

                        <i class="lni lni-trash-can"></i>

                    </button>

                </div>

                <?php } ?>

            </div>

            

            <div class="total-carrinho">

                Total: R$ <?php echo number_format($total_mesa, 2, ',', '.'); ?>

            </div>

        </div>

        <?php } ?>

    </div>



    <!-- Botão Finalizar Pedido -->

    <?php if ($tem_pedido_andamento && count($itens_mesa) > 0) { ?>

    <button class="btn-finalizar" onclick="finalizarPedido()">

        <i class="lni lni-checkmark-circle"></i> Finalizar Pedido

    </button>

    <?php } ?>



    <script src="../_cdn/jquery/jquery.min.js"></script>

    <script>

        // Fallback para jQuery do CDN caso o local falhe

        if (typeof jQuery === 'undefined') {

            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');

        }

    </script>

    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>

    

    <script>

        // Verificar se jQuery está carregado

        if (typeof $ === 'undefined') {

            console.error('jQuery não está carregado!');

            alert('Erro ao carregar recursos. Recarregue a página.');

        }

        

        // Função para mostrar/ocultar carrinho

        function toggleCarrinho() {

            const carrinho = document.getElementById('carrinho-section');

            const icon = document.getElementById('toggle-icon');

            

            if (carrinho) {

                if (carrinho.classList.contains('hidden')) {

                    carrinho.classList.remove('hidden');

                    icon.className = 'lni lni-chevron-up';

                    localStorage.setItem('carrinho-visivel', 'true');

                } else {

                    carrinho.classList.add('hidden');

                    icon.className = 'lni lni-chevron-down';

                    localStorage.setItem('carrinho-visivel', 'false');

                }

            } else {

                // Se não há carrinho, rolar para o final da página onde ficariam os itens

                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });

            }

        }

        

        // Função para adicionar produto com quantidade

        function adicionarProduto(produtoId, elemento) {

            console.log('Adicionando produto:', produtoId);

            

            const quantidade = document.getElementById('qtd-' + produtoId).value;

            

            if (quantidade <= 0) {

                alert('Quantidade deve ser maior que zero');

                return;

            }

            

            // Obter o botão correto

            const btn = elemento || event.target;

            if (!btn) {

                console.error('Botão não encontrado');

                return;

            }

            

            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="lni lni-spinner lni-spinning"></i> Adicionando...';

            btn.disabled = true;

            

            $.post('mesa.php?id=<?php echo $mesa_id; ?>', {

                action: 'adicionar_produto',

                produto_id: produtoId,

                quantidade: quantidade

            }, function(response) {

                console.log('Resposta do servidor:', response);

                if (response.success) {

                    // Resetar quantidade para 1

                    document.getElementById('qtd-' + produtoId).value = 1;

                    

                    // Mostrar feedback visual

                    btn.innerHTML = '<i class="lni lni-checkmark-circle"></i> Adicionado!';

                    btn.style.background = '#27ae60';

                    

                    setTimeout(function() {

                        // Restaurar botão ao estado original

                        btn.innerHTML = originalText;

                        btn.style.background = '';

                        btn.disabled = false;

                        

                        // Atualizar sacola e carrinho sem reload completo

                        atualizarSacola();

                    }, 1200);

                } else {

                    alert('Erro ao adicionar produto: ' + response.message);

                    btn.innerHTML = originalText;

                    btn.disabled = false;

                }

            }, 'json').fail(function(xhr, status, error) {

                console.error('Erro na requisição AJAX:', status, error);

                alert('Erro de conexão. Tente novamente.');

                btn.innerHTML = originalText;

                btn.disabled = false;

            });

        }

        

        // Debounce para atualização

        let updateTimeout;

        

        // Função para atualizar sacola e carrinho

        function atualizarSacola() {

            // Cancelar atualização anterior se ainda pendente

            clearTimeout(updateTimeout);

            

            updateTimeout = setTimeout(function() {

                $.get('mesa.php?id=<?php echo $mesa_id; ?>&ajax=1', function(data) {

                // Atualizar contador da sacola

                const sacola = document.querySelector('.sacola-icon');

                let contador = document.querySelector('.sacola-contador');

                

                if (data.itens_count > 0) {

                    sacola.classList.remove('empty');

                    if (contador) {

                        contador.textContent = data.itens_count;

                    } else {

                        // Criar contador se não existir

                        contador = document.createElement('span');

                        contador.className = 'sacola-contador';

                        contador.textContent = data.itens_count;

                        sacola.appendChild(contador);

                    }

                    

                    // Atualizar status da mesa

                    document.querySelector('.mesa-status').innerHTML = 'Ocupada - Total: R$ ' + data.total_formatado;

                    

                    // Atualizar seção de itens da mesa

                    atualizarItensCarrinho();

                    

                    // Se não existe carrinho, criar ou mostrar

                    if (!document.getElementById('carrinho-section')) {

                        setTimeout(function() {

                            location.reload();

                        }, 500);

                    } else {

                        // Atualizar botão finalizar se não existir

                        if (!document.querySelector('.btn-finalizar')) {

                            criarBotaoFinalizar();

                        }

                    }

                } else {

                    sacola.classList.add('empty');

                    if (contador) contador.remove();

                    document.querySelector('.mesa-status').innerHTML = 'Disponível';

                    

                    // Remover carrinho se vazio

                    const carrinhoSection = document.getElementById('carrinho-section');

                    if (carrinhoSection) carrinhoSection.remove();

                    

                    // Remover botão finalizar

                    const btnFinalizar = document.querySelector('.btn-finalizar');

                    if (btnFinalizar) btnFinalizar.remove();

                }

            }, 'json').fail(function() {

                // Em caso de erro, fazer reload tradicional

                console.log('Erro na atualização AJAX, fazendo reload...');

                location.reload();

            });

            }, 300); // Delay de 300ms para evitar muitas chamadas

        }

        

        // Função para atualizar itens do carrinho em tempo real

        function atualizarItensCarrinho() {

            $.get('mesa.php?id=<?php echo $mesa_id; ?>&itens_ajax=1', function(data) {

                if (data.success && data.itens.length > 0) {

                    let carrinhoSection = document.getElementById('carrinho-section');

                    

                    // Se não existe a seção do carrinho, criar

                    if (!carrinhoSection) {

                        criarSecaoCarrinho();

                        carrinhoSection = document.getElementById('carrinho-section');

                    }

                    

                    // Atualizar contador de itens no título

                    const tituloCarrinho = carrinhoSection.querySelector('.section-title');

                    if (tituloCarrinho) {

                        tituloCarrinho.textContent = `Itens da Mesa (${data.itens.length})`;

                    }

                    

                    // Atualizar lista de itens

                    const itensContainer = document.getElementById('itens-carrinho');

                    if (itensContainer) {

                        const itensExistentes = Array.from(itensContainer.querySelectorAll('.item-carrinho')).map(el => el.getAttribute('data-item-id'));

                        itensContainer.innerHTML = '';

                        

                        data.itens.forEach(function(item) {

                            const subtotal = (item.preco_unitario * item.quantidade).toFixed(2).replace('.', ',');

                            const precoUnitario = parseFloat(item.preco_unitario).toFixed(2).replace('.', ',');

                            

                            const isNovoItem = !itensExistentes.includes(item.id.toString());

                            const animacaoClass = isNovoItem ? ' novo-item' : '';

                            

                            const itemHTML = `

                                <div class="item-carrinho${animacaoClass}" data-item-id="${item.id}">

                                    <div class="item-info">

                                        <div class="item-nome">${item.nome_produto}</div>

                                        <div class="item-detalhes">

                                            Qtd: ${item.quantidade} × R$ ${precoUnitario} = R$ ${subtotal}

                                        </div>

                                    </div>

                                    <button class="btn-remover" onclick="removerItem(${item.id})">

                                        <i class="lni lni-trash-can"></i>

                                    </button>

                                </div>

                            `;

                            itensContainer.innerHTML += itemHTML;

                        });

                        

                        // Remover classe de animação após um tempo

                        setTimeout(function() {

                            document.querySelectorAll('.item-carrinho.novo-item').forEach(function(el) {

                                el.classList.remove('novo-item');

                            });

                        }, 500);

                    }

                    

                    // Atualizar total

                    const totalCarrinho = carrinhoSection.querySelector('.total-carrinho');

                    if (totalCarrinho) {

                        totalCarrinho.textContent = `Total: R$ ${data.total_formatado}`;

                    }

                } else if (data.itens && data.itens.length === 0) {

                    // Se não há itens, remover carrinho

                    const carrinhoSection = document.getElementById('carrinho-section');

                    if (carrinhoSection) carrinhoSection.remove();

                }

            }, 'json').fail(function() {

                console.log('Erro ao atualizar itens do carrinho');

            });

        }

        

        // Função para criar seção do carrinho dinamicamente

        function criarSecaoCarrinho() {

            const container = document.querySelector('.content-container');

            const carrinhoHTML = `

                <div class="carrinho-section" id="carrinho-section">

                    <div class="carrinho-header">

                        <div class="section-title" style="margin-bottom: 0;">Itens da Mesa (0)</div>

                        <button class="btn-toggle-carrinho" onclick="toggleCarrinho()">

                            <i class="lni lni-chevron-up" id="toggle-icon"></i>

                        </button>

                    </div>

                    <div id="itens-carrinho"></div>

                    <div class="total-carrinho">Total: R$ 0,00</div>

                </div>

            `;

            container.insertAdjacentHTML('beforeend', carrinhoHTML);

        }

        

        // Função para criar botão finalizar dinamicamente

        function criarBotaoFinalizar() {

            if (!document.querySelector('.btn-finalizar')) {

                const botaoHTML = `

                    <button class="btn-finalizar" onclick="finalizarPedido()">

                        <i class="lni lni-checkmark-circle"></i> Finalizar Pedido

                    </button>

                `;

                document.body.insertAdjacentHTML('beforeend', botaoHTML);

            }

        }

        

        // Funções para controlar quantidade

        function aumentarQuantidade(produtoId) {

            const input = document.getElementById('qtd-' + produtoId);

            const atual = parseInt(input.value) || 1;

            if (atual < 99) {

                input.value = atual + 1;

            }

        }

        

        function diminuirQuantidade(produtoId) {

            const input = document.getElementById('qtd-' + produtoId);

            const atual = parseInt(input.value) || 1;

            if (atual > 1) {

                input.value = atual - 1;

            }

        }

        

        // Função de pesquisa

        function pesquisarProdutos() {

            const busca = document.getElementById('search-input').value;

            const url = new URL(window.location.href);

            

            if (busca.trim()) {

                url.searchParams.set('busca', busca);

            } else {

                url.searchParams.delete('busca');

            }

            

            window.location.href = url.toString();

        }

        

        function limparPesquisa() {

            const url = new URL(window.location.href);

            url.searchParams.delete('busca');

            window.location.href = url.toString();

        }

        

        // Event listeners

        document.addEventListener('DOMContentLoaded', function() {

            // Restaurar estado do carrinho

            const carrinhoVisivel = localStorage.getItem('carrinho-visivel');

            const carrinho = document.getElementById('carrinho-section');

            const icon = document.getElementById('toggle-icon');

            

            if (carrinho && carrinhoVisivel === 'false') {

                carrinho.classList.add('hidden');

                if (icon) icon.className = 'lni lni-chevron-down';

            }

            

            // Pesquisa em tempo real (com delay)

            let searchTimeout;

            document.getElementById('search-input').addEventListener('input', function() {

                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(pesquisarProdutos, 500);

            });

            

            // Enter para pesquisar

            document.getElementById('search-input').addEventListener('keypress', function(e) {

                if (e.key === 'Enter') {

                    pesquisarProdutos();

                }

            });

            

            // Validar inputs de quantidade

            document.querySelectorAll('.input-quantidade').forEach(function(input) {

                input.addEventListener('change', function() {

                    let valor = parseInt(this.value) || 1;

                    if (valor < 1) valor = 1;

                    if (valor > 99) valor = 99;

                    this.value = valor;

                });

            });

        });

        

        function removerItem(itemId) {

            if (confirm('Deseja remover este item?')) {

                // Adicionar efeito visual de carregamento

                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);

                if (itemElement) {

                    itemElement.style.opacity = '0.5';

                    itemElement.style.pointerEvents = 'none';

                }

                

                $.post('mesa.php?id=<?php echo $mesa_id; ?>', {

                    action: 'remover_item',

                    item_id: itemId

                }, function(response) {

                    if (response.success) {

                        // Remover item da interface com animação

                        if (itemElement) {

                            itemElement.style.transition = 'all 0.3s ease';

                            itemElement.style.transform = 'translateX(-100%)';

                            itemElement.style.opacity = '0';

                            

                            setTimeout(function() {

                                itemElement.remove();

                                // Atualizar sacola e carrinho

                                atualizarSacola();

                            }, 300);

                        } else {

                            atualizarSacola();

                        }

                    } else {

                        alert('Erro ao remover item: ' + response.message);

                        // Restaurar item se erro

                        if (itemElement) {

                            itemElement.style.opacity = '1';

                            itemElement.style.pointerEvents = 'auto';

                        }

                    }

                }, 'json').fail(function() {

                    alert('Erro de conexão. Tente novamente.');

                    // Restaurar item se erro

                    if (itemElement) {

                        itemElement.style.opacity = '1';

                        itemElement.style.pointerEvents = 'auto';

                    }

                });

            }

        }

        

        function finalizarPedido() {

            if (confirm('Deseja finalizar este pedido? Ele será enviado para a cozinha.')) {

                $.post('mesa.php?id=<?php echo $mesa_id; ?>', {

                    action: 'finalizar_pedido'

                }, function(response) {

                    if (response.success) {

                        alert('Pedido finalizado com sucesso! Número do pedido: ' + response.pedido_id);

                        window.location.href = 'index.php';

                    } else {

                        alert('Erro ao finalizar pedido: ' + response.message);

                    }

                }, 'json').fail(function() {

                    alert('Erro de conexão. Tente novamente.');

                });

            }

        }

        

        // Recarregar dados a cada 30 segundos

        setInterval(function() {

            console.log('Verificando atualizações...');

        }, 30000);

    </script>

</body>

</html>

