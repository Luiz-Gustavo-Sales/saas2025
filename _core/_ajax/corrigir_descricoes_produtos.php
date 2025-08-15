<?php
/**
 * Script para corrigir descrições de produtos que foram armazenadas com quebras de linha escapadas
 * Este script remove as sequências \\r\\n e substitui por quebras de linha normais
 */

// Definir que é um script de linha de comando
if (php_sapi_name() !== 'cli') {
    // Se não está rodando no CLI, incluir o config para rodar via navegador (temporariamente)
    session_start();
    include('../_includes/config.php');
    
    // Verificar se o usuário está logado como administrador
    if (!isset($_SESSION['estabelecimento']['id'])) {
        die("Acesso negado. Faça login como estabelecimento.");
    }
    
    echo "<h2>Correção de Descrições de Produtos</h2>";
    echo "<p><strong>IMPORTANTE:</strong> Este script irá corrigir todas as descrições de produtos do seu estabelecimento que contenham quebras de linha escapadas.</p>";
    echo "<pre>";
}

// Conectar ao banco de dados
// Se não tiver $db_con definido, definir aqui
if (!isset($db_con)) {
    include('../_includes/config.php');
}

// Função para normalizar quebras de linha
function normalizar_quebras_linha($texto) {
    // Remove os escapes duplos \\r\\n e \\n
    $texto = str_replace(array('\\\\r\\\\n', '\\\\n', '\\\\r'), "\n", $texto);
    // Remove escapes simples \r\n e \n  
    $texto = str_replace(array('\\r\\n', '\\n', '\\r'), "\n", $texto);
    // Normaliza quebras de linha para \n
    $texto = str_replace(array("\r\n", "\r"), "\n", $texto);
    return $texto;
}

echo "=== INICIANDO CORREÇÃO DE DESCRIÇÕES ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "Estabelecimento ID: " . $_SESSION['estabelecimento']['id'] . "\n\n";

// Buscar todos os produtos que têm descrições com quebras de linha problemáticas
$eid = $_SESSION['estabelecimento']['id'];
$query = "SELECT id, nome, descricao FROM produtos WHERE rel_estabelecimentos_id = '$eid' AND (descricao LIKE '%\\\\\\\\r\\\\\\\\n%' OR descricao LIKE '%\\\\\\\\n%' OR descricao LIKE '%\\\\r\\\\n%' OR descricao LIKE '%\\\\n%')";

$resultado = mysqli_query($db_con, $query);

if (!$resultado) {
    echo "ERRO na consulta: " . mysqli_error($db_con) . "\n";
    exit;
}

$total_produtos = mysqli_num_rows($resultado);
echo "Produtos encontrados com descrições problemáticas: $total_produtos\n\n";

if ($total_produtos == 0) {
    echo "✅ Nenhum produto com descrição problemática encontrado.\n";
    exit;
}

$produtos_corrigidos = 0;
$produtos_com_erro = 0;

while ($produto = mysqli_fetch_assoc($resultado)) {
    echo "Processando produto ID: {$produto['id']} - {$produto['nome']}\n";
    
    $descricao_original = $produto['descricao'];
    $descricao_corrigida = normalizar_quebras_linha($descricao_original);
    
    // Verificar se realmente houve mudança
    if ($descricao_original !== $descricao_corrigida) {
        echo "  Descrição original: " . substr(str_replace(array("\n", "\r"), "\\n", $descricao_original), 0, 100) . "...\n";
        echo "  Descrição corrigida: " . substr(str_replace(array("\n", "\r"), "\\n", $descricao_corrigida), 0, 100) . "...\n";
        
        // Escapar para o banco de dados
        $descricao_corrigida_escaped = mysqli_real_escape_string($db_con, $descricao_corrigida);
        
        // Atualizar no banco
        $update_query = "UPDATE produtos SET descricao = '$descricao_corrigida_escaped' WHERE id = '{$produto['id']}'";
        $update_result = mysqli_query($db_con, $update_query);
        
        if ($update_result) {
            echo "  ✅ Produto corrigido com sucesso\n";
            $produtos_corrigidos++;
        } else {
            echo "  ❌ Erro ao corrigir produto: " . mysqli_error($db_con) . "\n";
            $produtos_com_erro++;
        }
    } else {
        echo "  ℹ️  Nenhuma correção necessária\n";
    }
    
    echo "\n";
}

echo "=== RESUMO DA CORREÇÃO ===\n";
echo "Total de produtos processados: $total_produtos\n";
echo "Produtos corrigidos: $produtos_corrigidos\n";
echo "Produtos com erro: $produtos_com_erro\n";
echo "Finalizado em: " . date('Y-m-d H:i:s') . "\n";

if (php_sapi_name() !== 'cli') {
    echo "</pre>";
}
?>
