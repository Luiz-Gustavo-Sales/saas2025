<?php
include('../../../_core/_includes/config.php');
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Função para log simplificado (opcional, pode ser removida em produção)
function log_variantes($msg) {
    // Habilitar logs para depuração
    file_put_contents(__DIR__ . '/variantes.log', "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

try {
    $input = json_decode(file_get_contents("php://input"), true);
    
    log_variantes("📥 Dados brutos recebidos: " . file_get_contents("php://input"));
    log_variantes("📊 Dados decodificados: " . json_encode($input));

    if (!isset($input['produto_id']) || !isset($input['variacoes']) || !isset($input['quantidade'])) {
        log_variantes("❌ Dados incompletos - produto_id: " . (isset($input['produto_id']) ? 'OK' : 'MISSING') . 
                     ", variacoes: " . (isset($input['variacoes']) ? 'OK' : 'MISSING') . 
                     ", quantidade: " . (isset($input['quantidade']) ? 'OK' : 'MISSING'));
        throw new Exception("Dados incompletos.");
    }

    $produto_id = mysqli_real_escape_string($db_con, $input['produto_id']);
    $quantidade = intval($input['quantidade']);
    $variacoesSelecionadas = $input['variacoes'];

    // Não validar se quantidade é <= 0 aqui, pois quantidade negativa = devolução de estoque
    if ($quantidade == 0) throw new Exception("Quantidade não pode ser zero");

    $operacao = $quantidade < 0 ? "Devolvendo" : "Descontando";
    log_variantes("$operacao estoque - Produto: $produto_id, Quantidade: " . abs($quantidade));

    $query = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$produto_id'");
    if (!$query || !mysqli_num_rows($query)) throw new Exception("Produto não encontrado: $produto_id");

    $row = mysqli_fetch_array($query);
    $variacoes = json_decode($row['variacao'], true);

    $atualizacoes_realizadas = 0;
    
    log_variantes("🎯 Variações selecionadas: " . json_encode($variacoesSelecionadas));

    foreach ($variacoesSelecionadas as $grupo => $itens) {
        log_variantes("🔄 Processando grupo $grupo");
        
        if (!isset($variacoes[$grupo]['item'])) {
            log_variantes("⚠️ Grupo $grupo não existe nas variações do produto");
            continue;
        }

        foreach ($itens as $item) {
            $itemIndex = $item['item'];
            log_variantes("🔍 Procurando item $itemIndex no grupo $grupo");
            
            foreach ($variacoes[$grupo]['item'] as $key => &$varItem) {
                if ((string)$key === (string)$itemIndex) {
                    log_variantes("🔍 Item encontrado - Key: $key, ItemIndex: $itemIndex");
                    
                    // Verificar se quantidade existe, se não criar com valor padrão
                    if (!isset($varItem['quantidade']) || empty($varItem['quantidade'])) {
                        $varItem['quantidade'] = base64_encode("10");
                        log_variantes("⚠️ Quantidade não definida, definindo padrão: 10");
                    }
                    
                    // Decodificar quantidade atual
                    $qtd_atual = intval(base64_decode($varItem['quantidade']));
                    log_variantes("📊 Quantidade atual decodificada: $qtd_atual");
                    
                    if ($quantidade < 0) {
                        // Devolução de estoque (quantidade negativa)
                        $qtd_adicionar = abs($quantidade); // Se quantidade = -2, adiciona 2
                        $qtd_nova = $qtd_atual + $qtd_adicionar;
                        log_variantes("Devolvendo estoque: Grupo $grupo, Item $key, $qtd_atual ➝ $qtd_nova (+$qtd_adicionar)");
                    } else {
                        // Desconto de estoque (quantidade positiva)
                        if ($qtd_atual < $quantidade) {
                            // Não permitir estoque negativo  
                            $qtd_nova = 0;
                            log_variantes("Estoque insuficiente: Grupo $grupo, Item $key ($qtd_atual < $quantidade)");
                        } else {
                            $qtd_nova = $qtd_atual - $quantidade; // Desconta a quantidade exata
                            log_variantes("Descontando estoque: Grupo $grupo, Item $key, $qtd_atual ➝ $qtd_nova (-$quantidade)");
                        }
                    }
                    
                    // Salvar nova quantidade
                    $varItem['quantidade'] = base64_encode((string)$qtd_nova);
                    $atualizacoes_realizadas++;
                    
                    log_variantes("Atualizado: Grupo $grupo, Item $key, $qtd_atual ➝ $qtd_nova");
                    break;
                }
            }
        }
    }

    // Salvar as alterações no banco de dados
    $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
    $update = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '$produto_id'");

    if (!$update) {
        throw new Exception("Falha ao atualizar banco de dados: " . mysqli_error($db_con));
    }
    
    log_variantes("Sucesso: $atualizacoes_realizadas variações atualizadas");
    echo json_encode(['status' => 'success', 'atualizacoes' => $atualizacoes_realizadas]);
    
} catch (Throwable $e) {
    log_variantes("Erro: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
