<?php
// Funções auxiliares para o sistema de mesas

// Função para verificar se estabelecimento tem sistema de mesas ativo
function tem_sistema_mesas($estabelecimento_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT COUNT(*) as total FROM mesas WHERE rel_estabelecimentos_id = '$estabelecimento_id' AND status = '1'");
    $result = mysqli_fetch_array($query);
    
    return $result['total'] > 0;
}

// Função para contar mesas em atendimento
function contar_mesas_ocupadas($estabelecimento_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT COUNT(DISTINCT rel_mesas_id) as total FROM mesa_pedidos WHERE rel_estabelecimentos_id = '$estabelecimento_id' AND status = 'andamento'");
    $result = mysqli_fetch_array($query);
    
    return $result['total'];
}

// Função para buscar dados de uma mesa
function dados_mesa($mesa_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT * FROM mesas WHERE id = '$mesa_id' LIMIT 1");
    return mysqli_fetch_array($query);
}

// Função para buscar dados de um garçom
function dados_garcom($garcom_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT * FROM garcons WHERE id = '$garcom_id' LIMIT 1");
    return mysqli_fetch_array($query);
}

// Função para verificar se mesa tem pedido em andamento
function mesa_tem_pedido_andamento($mesa_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT id FROM mesa_pedidos WHERE rel_mesas_id = '$mesa_id' AND status = 'andamento' LIMIT 1");
    return mysqli_num_rows($query) > 0;
}

// Função para buscar pedido em andamento de uma mesa
function buscar_pedido_mesa_andamento($mesa_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT * FROM mesa_pedidos WHERE rel_mesas_id = '$mesa_id' AND status = 'andamento' ORDER BY id DESC LIMIT 1");
    return mysqli_fetch_array($query);
}

// Função para buscar itens de um pedido de mesa
function buscar_itens_pedido_mesa($mesa_pedido_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT * FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$mesa_pedido_id' ORDER BY id DESC");
    
    $itens = array();
    while ($item = mysqli_fetch_array($query)) {
        $itens[] = $item;
    }
    
    return $itens;
}

// Função para calcular total de um pedido de mesa
function calcular_total_pedido_mesa($mesa_pedido_id) {
    global $db_con;
    
    $query = mysqli_query($db_con, "SELECT SUM(preco_unitario * quantidade) as total FROM mesa_pedido_itens WHERE rel_mesa_pedidos_id = '$mesa_pedido_id'");
    $result = mysqli_fetch_array($query);
    
    return $result['total'] ? $result['total'] : 0;
}

// Função para formatar preço
function formatar_preco($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para gerar nome de produto com variação
function nome_produto_completo($produto_id, $variacao_id = null) {
    global $db_con;
    
    $produto_query = mysqli_query($db_con, "SELECT nome FROM produtos WHERE id = '$produto_id' LIMIT 1");
    $produto = mysqli_fetch_array($produto_query);
    
    $nome = $produto['nome'];
    
    if ($variacao_id) {
        $variacao_query = mysqli_query($db_con, "SELECT nome FROM produto_variacoes WHERE id = '$variacao_id' LIMIT 1");
        $variacao = mysqli_fetch_array($variacao_query);
        
        if ($variacao) {
            $nome .= ' - ' . $variacao['nome'];
        }
    }
    
    return $nome;
}

// Função para buscar preço de produto/variação
function buscar_preco_produto($produto_id, $variacao_id = null) {
    global $db_con;
    
    if ($variacao_id) {
        $query = mysqli_query($db_con, "SELECT valor, valor_promocional, oferta FROM produto_variacoes WHERE id = '$variacao_id' LIMIT 1");
    } else {
        $query = mysqli_query($db_con, "SELECT valor, valor_promocional, oferta FROM produtos WHERE id = '$produto_id' LIMIT 1");
    }
    
    $item = mysqli_fetch_array($query);
    
    if ($item) {
        return $item['oferta'] == '1' ? $item['valor_promocional'] : $item['valor'];
    }
    
    return 0;
}

// Função para contar pedidos finalizados hoje de um garçom
function contar_pedidos_garcom_hoje($garcom_id) {
    global $db_con;
    
    $hoje = date('Y-m-d');
    $query = mysqli_query($db_con, "SELECT COUNT(*) as total FROM pedidos WHERE rel_garcons_id = '$garcom_id' AND DATE(data_hora) = '$hoje'");
    $result = mysqli_fetch_array($query);
    
    return $result['total'];
}

// Função para calcular vendas do garçom hoje
function calcular_vendas_garcom_hoje($garcom_id) {
    global $db_con;
    
    $hoje = date('Y-m-d');
    $query = mysqli_query($db_con, "SELECT SUM(v_pedido) as total FROM pedidos WHERE rel_garcons_id = '$garcom_id' AND DATE(data_hora) = '$hoje' AND status IN ('1', '2', '4', '5', '6')");
    $result = mysqli_fetch_array($query);
    
    return $result['total'] ? $result['total'] : 0;
}
?>
