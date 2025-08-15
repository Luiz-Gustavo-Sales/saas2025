<?php
include('../../../_core/_includes/config.php');

echo "=== TESTE DETALHADO DE VARIAÇÕES ===\n\n";

// Testar se a função htmljson funciona
echo "1. Testando função htmljson:\n";
$teste_base64 = base64_encode("5"); // Simular quantidade 5
echo "Base64 de '5': $teste_base64\n";

if (function_exists('htmljson')) {
    $decodificado = htmljson($teste_base64);
    echo "Decodificado com htmljson: '$decodificado'\n";
    echo "Tipo: " . gettype($decodificado) . "\n";
} else {
    echo "Função htmljson NÃO encontrada!\n";
    // Criar função de teste
    function htmljson($str) {
        return htmlentities(base64_decode($str));
    }
    $decodificado = htmljson($teste_base64);
    echo "Decodificado com função criada: '$decodificado'\n";
}

echo "\n2. Testando produtos com variações:\n";

// Buscar produtos que têm variações
$query_produtos = mysqli_query($db_con, "SELECT id, nome, variacao FROM produtos WHERE variacao IS NOT NULL AND variacao != '' AND variacao != '[]' LIMIT 3");

if ($query_produtos && mysqli_num_rows($query_produtos)) {
    while ($produto = mysqli_fetch_array($query_produtos)) {
        echo "\n--- PRODUTO {$produto['id']}: {$produto['nome']} ---\n";
        $variacoes = json_decode($produto['variacao'], true);
        
        if ($variacoes && is_array($variacoes)) {
            foreach ($variacoes as $grupo_index => $grupo) {
                echo "Grupo $grupo_index: {$grupo['nome']}\n";
                if (isset($grupo['item']) && is_array($grupo['item'])) {
                    foreach ($grupo['item'] as $item_index => $item) {
                        echo "  Item $item_index: {$item['nome']}";
                        if (isset($item['quantidade'])) {
                            $qtd_raw = $item['quantidade'];
                            $qtd_decoded = htmljson($qtd_raw);
                            echo " | Qtd Raw: '$qtd_raw' | Qtd Decoded: '$qtd_decoded'";
                        } else {
                            echo " | SEM QUANTIDADE DEFINIDA";
                        }
                        echo "\n";
                    }
                }
            }
        } else {
            echo "Variações inválidas ou vazias\n";
        }
    }
} else {
    echo "Nenhum produto com variações encontrado\n";
}

echo "\n3. Testando um produto específico (digite o ID):\n";
// Você pode modificar este ID para testar um produto específico
$produto_teste = 1356; // Substitua pelo ID do produto que você está testando
echo "Testando produto ID: $produto_teste\n";

$query_teste = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$produto_teste'");
if ($query_teste && mysqli_num_rows($query_teste)) {
    $row_teste = mysqli_fetch_array($query_teste);
    $variacoes_teste = json_decode($row_teste['variacao'], true);
    echo "Variações decodificadas:\n";
    print_r($variacoes_teste);
} else {
    echo "Produto $produto_teste não encontrado\n";
}
?>
