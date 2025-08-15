<?php
ob_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/reembolso_error.log');
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    error_log("[REEMBOLSO DEBUG] Sessão não iniciada. Tentando iniciar session_start().");
    session_start();
}

error_log("[REEMBOLSO DEBUG] Session status: " . session_status());
error_log("[REEMBOLSO DEBUG] Conteúdo de \$_SESSION no início de reembolsar: " . print_r($_SESSION, true));

include(dirname(__DIR__, 3) . '/_core/_includes/config.php');
error_log("[REEMBOLSO DEBUG] Conteúdo de \$_SESSION APÓS include config: " . print_r($_SESSION, true));

restrict_estabelecimento();
restrict_expirado();

$id = $_GET['id'];
$eid = isset($_SESSION['estabelecimento']['id']) ? $_SESSION['estabelecimento']['id'] : null;

error_log("[REEMBOLSO DEBUG] Valor de \$eid (Estabelecimento ID) obtido da sessão: " . ($eid ? $eid : 'VAZIO OU NULL'));

$query_pedido_original = mysqli_query($db_con, "SELECT comprovante FROM pedidos WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'");

if (!$query_pedido_original) {
    error_log("[REEMBOLSO DEBUG] Erro na query SQL para buscar comprovante: " . mysqli_error($db_con));
}

$dados_pedido_original = mysqli_fetch_assoc($query_pedido_original);

$edit = mysqli_query($db_con, "UPDATE pedidos SET status = '7' WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'");

if (!$edit) {
    error_log("[REEMBOLSO DEBUG] Erro ao atualizar status do pedido para 7: " . mysqli_error($db_con));
    header("Location: ../index.php?msg=erro");
    exit;
}

if ($dados_pedido_original && !empty($dados_pedido_original['comprovante'])) {
    $texto_comprovante = $dados_pedido_original['comprovante'];
    error_log("[REEMBOLSO DEBUG] Texto do Comprovante: " . $texto_comprovante);

    $padrao_completo = '/\*(\d+)\s*x\*\s*#REF-([a-zA-Z0-9-]+)(.*?)(?=\*(\d+)\s*x\*|$)/s';

    if (preg_match_all($padrao_completo, $texto_comprovante, $matches_produtos, PREG_SET_ORDER)) {
        foreach ($matches_produtos as $match_produto) {
            $quantidade_item = intval($match_produto[1]);
            $ref_extraido = mysqli_real_escape_string($db_con, $match_produto[2]);
            $ref_para_db = "REF-" . $ref_extraido;
            $texto_variantes = trim($match_produto[3] ?? '');

            $sql_produto = "SELECT id, estoque, posicao, variacao FROM produtos WHERE ref = '$ref_para_db' AND rel_estabelecimentos_id = '$eid'";
            $produto_query = mysqli_query($db_con, $sql_produto);

            if (!$produto_query || mysqli_num_rows($produto_query) == 0) {
                error_log("[REEMBOLSO DEBUG] ❌ Produto não encontrado: Ref {$ref_para_db}");
                continue;
            }

            $produto_info = mysqli_fetch_assoc($produto_query);
            $produto_id = $produto_info['id'];

            // Sempre devolver estoque do produto principal
            if (in_array($produto_info['estoque'], ['1', '2'])) {
                $sql_update_posicao = "UPDATE produtos SET posicao = posicao + {$quantidade_item} WHERE id = '{$produto_id}'";
                mysqli_query($db_con, $sql_update_posicao);
                error_log("[REEMBOLSO DEBUG] ✅ Estoque geral atualizado (+{$quantidade_item}) para produto ID {$produto_id}");
            }

            // Se houver variantes, devolver também o estoque das variantes selecionadas
            if (!empty($produto_info['variacao'])) {
                $variacoes = json_decode($produto_info['variacao'], true);
                $variacoes_alteradas = false;

                if (is_array($variacoes)) {
                    // Novo parser robusto para variantes
                    $linhas = preg_split('/\r?\n/', $texto_variantes);
                    foreach ($linhas as $linha) {
                        $linha = trim($linha);
                        if ($linha === '' || strpos($linha, '*Valor:*') === 0) continue;
                        if (preg_match('/^\*(.*?)\*:\s*(.*)$/', $linha, $m)) {
                            $variacao_nome = trim(preg_replace('/[\s\t\.]+$/', '', $m[1]));
                            $itens_raw = trim($m[2]);
                            $itens = array_map(function($v) { return trim(preg_replace('/[\s\t\.]+$/', '', $v)); }, explode(',', $itens_raw));
                            foreach ($itens as $item_texto) {
                                if (preg_match('/^(.*?)\s*\(\+/', $item_texto, $m2)) {
                                    $item_nome = trim(preg_replace('/[\s\t\.]+$/', '', $m2[1]));
                                } else {
                                    $item_nome = trim(preg_replace('/[\s\t\.]+$/', '', $item_texto));
                                }
                                $encontrou = false;
                                foreach ($variacoes as $x => $variacao) {
                                    $nome_variacao = trim(preg_replace('/[\s\t\.]+$/', '', base64_decode($variacao['nome'])));
                                    if (strcasecmp($nome_variacao, $variacao_nome) === 0) {
                                        foreach ($variacao['item'] as $y => $item) {
                                            $nome_item = trim(preg_replace('/[\s\t\.]+$/', '', base64_decode($item['nome'])));
                                            if (strcasecmp($nome_item, $item_nome) === 0) {
                                                $quantidade_atual = intval(base64_decode($item['quantidade']));
                                                $nova_quantidade = $quantidade_atual + $quantidade_item;
                                                $variacoes[$x]['item'][$y]['quantidade'] = base64_encode((string) $nova_quantidade);
                                                $variacoes_alteradas = true;
                                                error_log("[REEMBOLSO DEBUG] ✅ Variante atualizada: {$variacao_nome}/{$item_nome} (+{$quantidade_item})");
                                                $encontrou = true;
                                                break;
                                            }
                                        }
                                    }
                                    if ($encontrou) break;
                                }
                                if (!$encontrou) {
                                    error_log("[REEMBOLSO DEBUG] ❌ Variante não encontrada: {$variacao_nome}/{$item_nome}");
                                }
                            }
                        }
                    }

                    if ($variacoes_alteradas) {
                        $novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes));
                        $update_variacao_sql = "UPDATE produtos SET variacao = '$novo_json' WHERE id = '$produto_id'";
                        if (mysqli_query($db_con, $update_variacao_sql)) {
                            error_log("[REEMBOLSO DEBUG] ✅ Variantes atualizadas em lote para produto ID $produto_id");
                        } else {
                            error_log("[REEMBOLSO DEBUG] ❌ Erro ao atualizar variantes para produto ID $produto_id: " . mysqli_error($db_con));
                        }
                    }
                }
            }
        }
    } else {
        error_log("[REEMBOLSO DEBUG] ⚠️ Nenhum produto encontrado no comprovante.");
    }

    $nome = $whats = '';
    if (preg_match('/\\*Nome:\\*\\s*([^\\n]+)/m', $texto_comprovante, $nome_cliente_match)) {
        $nome = trim($nome_cliente_match[1]);
    }
    if (preg_match('/\\*Whatsapp:\\*\\s*([^\\n]+)/m', $texto_comprovante, $whats_cliente_match)) {
        $whats = trim($whats_cliente_match[1]);
    }

    header("Location: ../index.php?msg=reembolsado&n=" . urlencode($nome) . "&whats=" . urlencode($whats) . "&pedido=$id");
    exit;
}

header("Location: ../index.php?msg=erro");
exit;
