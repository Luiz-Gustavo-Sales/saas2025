<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../_core/_includes/config.php';

echo "=== VERIFICAÇÃO E CORREÇÃO DE ESTOQUE NEGATIVO ===\n\n";

$produtos_corrigidos = 0;
$variacoes_corrigidas = 0;

// Buscar todos os produtos com variações
$query = mysqli_query($db_con, "SELECT id, titulo, variacao FROM produtos WHERE variacao IS NOT NULL AND variacao != '' AND variacao != '[]'");

if (mysqli_num_rows($query) > 0) {
    while ($produto = mysqli_fetch_array($query)) {
        $variacoes = json_decode($produto['variacao'], true);
        
        if (!is_array($variacoes)) continue;
        
        $produto_modificado = false;
        $detalhes_produto = [];
        
        foreach ($variacoes as $grupo_idx => &$grupo) {
            if (!isset($grupo['item']) || !is_array($grupo['item'])) continue;
            
            foreach ($grupo['item'] as $item_idx => &$item) {
                if (!isset($item['quantidade'])) {
                    // Adicionar quantidade padrão
                    $item['quantidade'] = base64_encode("10");
                    $produto_modificado = true;
                    $variacoes_corrigidas++;
                    $detalhes_produto[] = "Grupo $grupo_idx, Item $item_idx: Adicionada quantidade padrão (10)";
                    continue;
                }
                
                $quantidade_atual = intval(base64_decode($item['quantidade']));
                
                if ($quantidade_atual < 0) {
                    // Corrigir estoque negativo
                    $item['quantidade'] = base64_encode("0");
                    $produto_modificado = true;
                    $variacoes_corrigidas++;
                    $detalhes_produto[] = "Grupo $grupo_idx, Item $item_idx: Corrigido de $quantidade_atual para 0";
                }
            }
        }
        
        if ($produto_modificado) {
            $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
            $update = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '{$produto['id']}'");
            
            if ($update) {
                $produtos_corrigidos++;
                echo "✅ Produto corrigido: {$produto['titulo']} (ID: {$produto['id']})\n";
                foreach ($detalhes_produto as $detalhe) {
                    echo "   - $detalhe\n";
                }
                echo "\n";
            } else {
                echo "❌ Erro ao corrigir produto {$produto['id']}: " . mysqli_error($db_con) . "\n";
            }
        }
    }
} else {
    echo "ℹ️  Nenhum produto com variações encontrado.\n";
}

echo "=== RESUMO ===\n";
echo "Produtos corrigidos: $produtos_corrigidos\n";
echo "Variações corrigidas: $variacoes_corrigidas\n";

if ($produtos_corrigidos > 0) {
    echo "\n✅ Correções aplicadas com sucesso!\n";
} else {
    echo "\n✅ Nenhuma correção necessária. Sistema está funcionando corretamente!\n";
}
?>
