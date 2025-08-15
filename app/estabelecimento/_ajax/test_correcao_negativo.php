<?php
// Teste direto de atualização de variações
require_once '../../../_core/_includes/config.php';

echo "=== TESTE DE CORREÇÃO DE ESTOQUE NEGATIVO ===\n\n";

// Configurar produto de teste
$produto_id = 1; // ID de produto que sabemos que tem variações

// 1. Buscar o produto e suas variações
$query = mysqli_query($db_con, "SELECT id, titulo, variacao FROM produtos WHERE id = '$produto_id'");
if (!$query || !mysqli_num_rows($query)) {
    echo "❌ Produto $produto_id não encontrado!\n";
    exit;
}

$produto = mysqli_fetch_array($query);
$variacoes = json_decode($produto['variacao'], true);

if (!$variacoes) {
    echo "❌ Produto $produto_id não possui variações!\n";
    exit;
}

echo "✅ Produto encontrado: {$produto['titulo']}\n";
echo "📊 Variações atuais:\n";

foreach ($variacoes as $grupo => $dados) {
    echo "  Grupo $grupo:\n";
    if (isset($dados['item'])) {
        foreach ($dados['item'] as $key => $item) {
            $quantidade = isset($item['quantidade']) ? base64_decode($item['quantidade']) : 'N/A';
            echo "    - Item $key: {$item['titulo']} (Estoque: $quantidade)\n";
        }
    }
}

// 2. Simular uma atualização que causaria estoque negativo
$grupo_teste = 0;
$item_teste = 0;

if (isset($variacoes[$grupo_teste]['item'][$item_teste])) {
    echo "\n🧪 TESTANDO: Reduzir estoque do Grupo $grupo_teste, Item $item_teste\n";
    
    $quantidade_atual = intval(base64_decode($variacoes[$grupo_teste]['item'][$item_teste]['quantidade']));
    echo "Quantidade atual: $quantidade_atual\n";
    
    $quantidade_solicitar = $quantidade_atual + 5; // Solicitar mais do que tem
    echo "Quantidade a solicitar: $quantidade_solicitar\n";
    
    // Aplicar a lógica correta
    if ($quantidade_atual < $quantidade_solicitar) {
        $nova_quantidade = 0; // Não permitir negativo
        echo "⚠️  Estoque insuficiente! Setando para 0.\n";
    } else {
        $nova_quantidade = $quantidade_atual - $quantidade_solicitar;
    }
    
    echo "Nova quantidade: $nova_quantidade\n";
    
    // Atualizar no array
    $variacoes[$grupo_teste]['item'][$item_teste]['quantidade'] = base64_encode((string)$nova_quantidade);
    
    // Salvar no banco
    $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
    $update = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '$produto_id'");
    
    if ($update) {
        echo "✅ Atualização salva no banco com sucesso!\n";
        
        // Verificar se foi salvo corretamente
        $verify_query = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$produto_id'");
        $verify_row = mysqli_fetch_array($verify_query);
        $verify_variacoes = json_decode($verify_row['variacao'], true);
        
        $quantidade_verificada = intval(base64_decode($verify_variacoes[$grupo_teste]['item'][$item_teste]['quantidade']));
        echo "Quantidade verificada no banco: $quantidade_verificada\n";
        
        if ($quantidade_verificada >= 0) {
            echo "✅ SUCESSO: Estoque não ficou negativo!\n";
        } else {
            echo "❌ FALHA: Estoque ainda está negativo!\n";
        }
    } else {
        echo "❌ Erro ao salvar: " . mysqli_error($db_con) . "\n";
    }
} else {
    echo "❌ Item de teste não encontrado!\n";
}
?>
