<?php
include('../../../_core/_includes/config.php');
header('Content-Type: application/json');

// Simular uma atualização de variação
$produto_id = 1356; // Substitua pelo ID que você está testando

echo "=== TESTE DE ATUALIZAÇÃO DIRETA ===\n\n";

// Buscar variações atuais
$query = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$produto_id'");
if ($query && mysqli_num_rows($query)) {
    $row = mysqli_fetch_array($query);
    $variacoes = json_decode($row['variacao'], true);
    
    echo "ANTES da modificação:\n";
    if (isset($variacoes[0]['item'][0]['quantidade'])) {
        $qtd_atual = htmljson($variacoes[0]['item'][0]['quantidade']);
        echo "Grupo 0, Item 0: $qtd_atual\n";
        
        // Modificar a quantidade
        $nova_qtd = intval($qtd_atual) - 1;
        $variacoes[0]['item'][0]['quantidade'] = base64_encode((string)$nova_qtd);
        
        // Salvar no banco
        $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
        $update = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '$produto_id'");
        
        if ($update) {
            echo "UPDATE executado com sucesso\n";
            echo "Linhas afetadas: " . mysqli_affected_rows($db_con) . "\n";
            
            // Verificar se foi salvo
            $verify_query = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$produto_id'");
            $verify_row = mysqli_fetch_array($verify_query);
            $verify_variacoes = json_decode($verify_row['variacao'], true);
            
            if (isset($verify_variacoes[0]['item'][0]['quantidade'])) {
                $qtd_verificada = htmljson($verify_variacoes[0]['item'][0]['quantidade']);
                echo "DEPOIS da modificação:\n";
                echo "Grupo 0, Item 0: $qtd_verificada\n";
                
                if ($qtd_verificada == $nova_qtd) {
                    echo "✅ SUCESSO: Quantidade foi atualizada corretamente!\n";
                } else {
                    echo "❌ ERRO: Quantidade não foi atualizada corretamente!\n";
                }
            }
        } else {
            echo "❌ ERRO no UPDATE: " . mysqli_error($db_con) . "\n";
        }
    } else {
        echo "❌ Produto não tem variações com quantidade\n";
    }
} else {
    echo "❌ Produto não encontrado\n";
}
?>
