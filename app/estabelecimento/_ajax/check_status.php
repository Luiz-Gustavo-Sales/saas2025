<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar ao banco
require_once '../../../_core/_includes/config.php';

echo "<h2>Status das Variações</h2>";

// Buscar produtos com variações
$query = mysqli_query($db_con, "SELECT id, titulo, variacao FROM produtos WHERE variacao IS NOT NULL AND variacao != '' AND variacao != '[]' LIMIT 5");

if (mysqli_num_rows($query) > 0) {
    while ($produto = mysqli_fetch_array($query)) {
        echo "<h3>Produto: {$produto['titulo']} (ID: {$produto['id']})</h3>";
        
        $variacoes = json_decode($produto['variacao'], true);
        
        if (is_array($variacoes)) {
            foreach ($variacoes as $grupo => $items) {
                echo "<h4>Grupo: $grupo</h4>";
                foreach ($items as $key => $item) {
                    $quantidade = isset($item['quantidade']) ? htmlspecialchars(base64_decode($item['quantidade'])) : 'N/A';
                    echo "<p>- {$item['titulo']}: <strong>$quantidade</strong> unidades</p>";
                }
            }
        } else {
            echo "<p>Erro ao decodificar variações</p>";
        }
        echo "<hr>";
    }
} else {
    echo "<p>Nenhum produto com variações encontrado</p>";
}
?>
