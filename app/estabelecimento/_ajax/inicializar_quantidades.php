<?php
include('../../../_core/_includes/config.php');

echo "=== SCRIPT DE INICIALIZAÇÃO DE QUANTIDADES ===\n\n";

// Buscar produtos com variações
$query_produtos = mysqli_query($db_con, "SELECT id, nome, variacao FROM produtos WHERE variacao IS NOT NULL AND variacao != '' AND variacao != '[]'");

$produtos_atualizados = 0;
$itens_atualizados = 0;

if ($query_produtos && mysqli_num_rows($query_produtos)) {
    while ($produto = mysqli_fetch_array($query_produtos)) {
        echo "📦 Produto {$produto['id']}: {$produto['nome']}\n";
        $variacoes = json_decode($produto['variacao'], true);
        $produto_modificado = false;
        
        if ($variacoes && is_array($variacoes)) {
            foreach ($variacoes as $grupo_index => &$grupo) {
                if (isset($grupo['item']) && is_array($grupo['item'])) {
                    foreach ($grupo['item'] as $item_index => &$item) {
                        // Verificar se tem quantidade definida
                        if (!isset($item['quantidade']) || empty($item['quantidade'])) {
                            echo "  ⚠️ Grupo $grupo_index, Item $item_index ({$item['nome']}): SEM QUANTIDADE - Definindo 10\n";
                            $item['quantidade'] = base64_encode("10");
                            $produto_modificado = true;
                            $itens_atualizados++;
                        } else {
                            // Verificar se a quantidade é válida
                            $qtd_atual = intval(htmljson($item['quantidade']));
                            if ($qtd_atual <= 0) {
                                echo "  ⚠️ Grupo $grupo_index, Item $item_index ({$item['nome']}): Quantidade $qtd_atual - Definindo 10\n";
                                $item['quantidade'] = base64_encode("10");
                                $produto_modificado = true;
                                $itens_atualizados++;
                            } else {
                                echo "  ✅ Grupo $grupo_index, Item $item_index ({$item['nome']}): Quantidade OK ($qtd_atual)\n";
                            }
                        }
                    }
                }
            }
            
            // Salvar se houve modificações
            if ($produto_modificado) {
                $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
                $update = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '{$produto['id']}'");
                
                if ($update) {
                    echo "  ✅ Produto atualizado no banco\n";
                    $produtos_atualizados++;
                } else {
                    echo "  ❌ Erro ao atualizar produto: " . mysqli_error($db_con) . "\n";
                }
            }
        }
        echo "\n";
    }
} else {
    echo "Nenhum produto com variações encontrado\n";
}

echo "=== RESUMO ===\n";
echo "Produtos atualizados: $produtos_atualizados\n";
echo "Itens de variação atualizados: $itens_atualizados\n";
?>
