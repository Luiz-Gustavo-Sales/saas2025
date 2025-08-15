<?php
// filepath: c:\\Users\\Leand\\Downloads\\Versão teste\\_core\\_ajax\\importar_produtos_xml.php
// CORE
include_once('../../_core/_includes/config.php');

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// ===== DEBUG INÍCIO =====
// Vamos verificar o conteúdo de $_POST e $_REQUEST
// Isso será enviado na resposta da requisição AJAX.
// Você poderá ver no console do navegador, na aba "Network", selecionando a requisição.
$debug_output = array(
    'post_data' => $_POST,
    'request_data' => $_REQUEST,
    'server_request_method' => $_SERVER['REQUEST_METHOD']
);
// Temporariamente, vamos enviar apenas isso para depuração.
// Comente a linha abaixo depois de depurar para o script funcionar normalmente.
// echo json_encode($debug_output);
// exit;
// ===== DEBUG FIM =====

// Inicializa a resposta
$response = [
    'success' => false,
    'message' => '',
    'imported_count' => 0,
    'skipped_count' => 0,
    'errors' => []
];

// Verifica se o usuário está logado como estabelecimento
session_start();
if (!isset($_SESSION['estabelecimento']['id'])) {
    $response['message'] = "Acesso não autorizado. Faça login como estabelecimento.";
    echo json_encode($response);
    exit;
}
$eid = $_SESSION['estabelecimento']['id'];

// Verifica se a URL do XML foi enviada
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['import_xml_url']) || empty(trim($_POST['import_xml_url']))) {
        $response['message'] = 'URL do XML não fornecida ou está vazia.';
        // Adicionar o que foi recebido para ajudar na depuração
        $response['received_post'] = $_POST;
        echo json_encode($response);
        exit;
    }
    $xml_url = trim($_POST['import_xml_url']);
} else {
    $response['message'] = "Método de requisição inválido. Apenas POST é aceito.";
    echo json_encode($response);
    exit;
}

$xml_url = filter_var($xml_url, FILTER_SANITIZE_URL);

if (!filter_var($xml_url, FILTER_VALIDATE_URL)) {
    $response['message'] = "URL do XML inválida.";
    echo json_encode($response);
    exit;
}

// Tenta carregar o conteúdo do XML usando cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $xml_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecionamentos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verificar certificado SSL (recomendado para produção)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verificar nome do host no certificado
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout da requisição em segundos

$xml_content = curl_exec($ch);
$curl_error_number = curl_errno($ch);
$curl_error_message = curl_error($ch);
curl_close($ch);

if ($xml_content === false || $curl_error_number !== 0) {
    $response['message'] = "Não foi possível carregar o XML da URL fornecida usando cURL. Verifique a URL e as permissões do servidor. Erro cURL ({$curl_error_number}): " . $curl_error_message;
    echo json_encode($response);
    exit;
}

// Tenta analisar o XML
libxml_use_internal_errors(true); // Habilita o manuseio de erros do libxml
$xml = simplexml_load_string($xml_content);

if ($xml === false) {
    $xml_errors = [];
    foreach (libxml_get_errors() as $error) {
        $xml_errors[] = "Erro XML: " . trim($error->message) . " na linha " . $error->line;
    }
    libxml_clear_errors();
    $response['message'] = "Erro ao analisar o XML. Verifique o formato do arquivo.";
    $response['errors'] = $xml_errors;
    echo json_encode($response);
    exit;
}

// Registra o namespace 'g' (comum em feeds do Google Shopping)
$xml->registerXPathNamespace('g', 'http://base.google.com/ns/1.0');

$imported_count = 0;
$skipped_count = 0;
$processing_errors = [];

// Itera sobre cada 'item' ou 'entry' (produto) no XML
// Alguns feeds usam <item>, outros <entry>. Vamos tentar detectar qual é.
$products_nodes = null;
if (isset($xml->channel->item)) {
    $products_nodes = $xml->channel->item;
} elseif (isset($xml->entry)) {
    $products_nodes = $xml->entry;
} else {
    $response['message'] = "Não foi possível encontrar os nós dos produtos no XML (nem <item> nem <entry>).";
    echo json_encode($response);
    exit;
}


foreach ($products_nodes as $entry) {
    $g = $entry->children('g', true); // Acessa os elementos dentro do namespace 'g'

    // Se não houver namespace 'g', tenta acessar os elementos diretamente (padrão RSS)
    $id_externo = isset($g->id) ? (string)$g->id : (isset($entry->guid) ? (string)$entry->guid : (isset($entry->id) ? (string)$entry->id : uniqid('prod_')));
    $nome = isset($g->title) ? (string)$g->title : (isset($entry->title) ? (string)$entry->title : 'Produto sem nome');
    $descricao = isset($g->description) ? trim((string)$g->description) : (isset($entry->description) ? trim((string)$entry->description) : 'Descrição não fornecida');
    
    // Lógica expandida para extração do link da imagem
    $link_imagem = null; // Inicializa como null

    // 1. Tenta g:image_link
    if (isset($g->image_link)) {
        $link_imagem = (string)$g->image_link;
    }
    // 2. Se não encontrou, tenta image_link (sem namespace)
    elseif (isset($entry->image_link)) {
        $link_imagem = (string)$entry->image_link;
    }
    // 3. Se não encontrou, tenta <enclosure>
    elseif (isset($entry->enclosure)) {
        $enclosure_attrs = $entry->enclosure->attributes();
        if (isset($enclosure_attrs->url) && isset($enclosure_attrs->type) && strpos((string)$enclosure_attrs->type, 'image') !== false) {
            $link_imagem = (string)$enclosure_attrs->url;
        }
    }

    // 4. Se ainda não encontrou após as tentativas acima, tenta <media:content>
    if ($link_imagem === null) {
        $namespaces = $entry->getNamespaces(true); // Obtém namespaces no escopo do <entry>
        if (isset($namespaces['media'])) {
            $media_ns_uri = $namespaces['media']; // URI do namespace 'media'
            // Verifica se o elemento 'content' existe dentro do namespace 'media'
            if (isset($entry->children($media_ns_uri)->content)) {
                $media_content_elements = $entry->children($media_ns_uri)->content;

                if ($media_content_elements) {
                    foreach ($media_content_elements as $media_element) {
                        $attrs = $media_element->attributes(); // Atributos do <media:content>
                        if (isset($attrs->url)) { // Acessa o atributo 'url'
                            $url_candidate = (string)$attrs->url;
                            $medium_attr = isset($attrs->medium) ? (string)$attrs->medium : '';
                            $type_attr = isset($attrs->type) ? (string)$attrs->type : '';

                            if (!empty($url_candidate) && ($medium_attr == 'image' || strpos($type_attr, 'image') !== false)) {
                                $link_imagem = $url_candidate;
                                break; // Encontrou uma imagem em media:content, sai do loop
                            }
                        }
                    }
                }
            }
        }
    }
    // Fim da lógica expandida de extração de imagem

    // DEBUG: Adicionar informação sobre a imagem extraída para cada produto
    $debug_image_url_found = "Nenhuma URL de imagem encontrada no XML.";
    $is_valid_url_filter_var = false;
    if ($link_imagem !== null) {
        $debug_image_url_found = "URL da imagem extraída do XML: '" . htmlspecialchars($link_imagem) . "'";
        $is_valid_url_filter_var = (filter_var($link_imagem, FILTER_VALIDATE_URL) !== false);
    }
    // Modificado o nome do log para clareza
    $processing_errors[] = "[DEBUG Imagem Original] Produto ID Externo '{$id_externo}' (Nome: '" . htmlspecialchars($nome) . "'): " . $debug_image_url_found . ". Resultado filter_var: " . ($is_valid_url_filter_var ? 'Válida' : 'Inválida ou Vazia');

    // Para o preço, pode haver variações. Google Shopping usa <g:price>. Outros podem usar <price> ou <sale_price>
    $preco_str = isset($g->price) ? (string)$g->price : (isset($entry->price) ? (string)$entry->price : '0.00 BRL');
    $marca = isset($g->brand) ? (string)$g->brand : (isset($entry->brand) ? (string)$entry->brand : '');

    // Limpeza e formatação básica dos dados
    $preco_numerico = preg_replace('/[^0-9,.]/', '', $preco_str);
    $preco_numerico = str_replace('.', '', $preco_numerico);
    $preco_numerico = str_replace(',', '.', $preco_numerico);
    $valor = number_format((float)$preco_numerico, 2, '.', '');

    // Validação mínima
    if (empty($nome) || $link_imagem === null || !filter_var($link_imagem, FILTER_VALIDATE_URL)) {
        // Adiciona o link da imagem (ou sua ausência/invalidade) à mensagem de erro para depuração
        $image_debug_info = "ausente ou vazio";
        if (!empty($link_imagem)) {
            $image_debug_info = "inválido ('" . htmlspecialchars($link_imagem) . "')";
        }
        $processing_errors[] = "Produto com ID Externo '{$id_externo}' (Nome: '" . htmlspecialchars($nome) . "') ignorado: Nome inválido/ausente ou link da imagem {$image_debug_info}.";
        $skipped_count++;
        continue;
    }

    // ===== INÍCIO: Processamento do link da imagem para o formato do banco de dados =====
    $imagem_original_xml = $link_imagem; 
    $imagem_para_db = $imagem_original_xml; // Default: usa a URL completa se não for interna
    $path_prefix_to_check = '_core/_uploads/';
    $pos_prefix = strpos($imagem_original_xml, $path_prefix_to_check);

    if ($pos_prefix !== false) {
        // Encontrou '_core/_uploads/' na URL
        // Extrai a parte da string APÓS o prefixo
        $caminho_relativo_apos_prefixo = substr($imagem_original_xml, $pos_prefix + strlen($path_prefix_to_check));
        // Remove quaisquer barras '/' no início do caminho relativo (ex: se a URL original tinha '//' após o prefixo)
        $imagem_para_db = ltrim($caminho_relativo_apos_prefixo, '/');
        
        $processing_errors[] = "[DEBUG Imagem Processada] URL Original: '" . htmlspecialchars($imagem_original_xml) . "', Caminho para DB: '" . htmlspecialchars($imagem_para_db) . "' (Detectado _core/_uploads/, EID não prefixado intencionalmente)";
    } else {
        // Não encontrou '_core/_uploads/', a imagem é considerada externa.
        // Mantém $imagem_para_db como a URL original completa.
        $processing_errors[] = "[DEBUG Imagem Processada] URL Original: '" . htmlspecialchars($imagem_original_xml) . "' mantida como está. (Não detectado _core/_uploads/). Esta imagem é externa e não foi baixada/processada localmente.";
    }
    // ===== FIM: Processamento do link da imagem =====

    // Verificar se o produto já existe
    if ($id_externo) {
        $check_sql = "SELECT id FROM produtos WHERE rel_estabelecimentos_id = ? AND id_externo = ?";
        $stmt_check = mysqli_prepare($db_con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "is", $eid, $id_externo);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $skipped_count++;
            mysqli_stmt_close($stmt_check);
            continue;
        }
        mysqli_stmt_close($stmt_check);
    }

    // Definir valores padrão
    $categoria_id = null;
    $q_cat = mysqli_query($db_con, "SELECT id FROM categorias WHERE rel_estabelecimentos_id = '$eid' ORDER BY id ASC LIMIT 1");
    if(mysqli_num_rows($q_cat) > 0) {
        $d_cat = mysqli_fetch_array($q_cat);
        $categoria_id = $d_cat['id'];
    } else {
        $processing_errors[] = "Produto '{$nome}' não importado: Nenhuma categoria encontrada. Cadastre categorias primeiro.";
        $skipped_count++;
        continue;
    }

    $estoque = "1";
    $posicao = "0";
    $ref = $id_externo;
    $video_link = "";
    $oferta = "2";
    $valor_promocional = "0.00";
    $variacao_json = "[]";
    $status_produto = "1";
    $visible = "1";
    $integrado = "2";
    $medidas = ['altura' => '', 'largura' => '', 'comprimento' => '', 'peso' => ''];
    

    // Corrigir a descrição do produto para evitar que fique como '0'
    $descricao = isset($g->description) ? trim((string)$g->description) : (isset($entry->description) ? trim((string)$entry->description) : 'Descrição não fornecida');

    // Garantir que a descrição não seja '0' ou vazia
    if ($descricao === '0' || $descricao === '' || $descricao === null) {
        $descricao = 'Descrição não fornecida';
    }

    // Adicionar log de depuração para verificar o valor da descrição
    $processing_errors[] = "[DEBUG Descrição] Produto ID Externo '{$id_externo}' (Nome: '{$nome}'): Descrição extraída: '{$descricao}'";

    // Garantir que a descrição seja tratada corretamente antes da inserção
    $descricao = mysqli_real_escape_string($db_con, $descricao);

    // Adicionar log de depuração para verificar o valor da descrição após o tratamento
    $processing_errors[] = "[DEBUG Descrição Tratada] Produto ID Externo '{$id_externo}' (Nome: '{$nome}'): Descrição tratada: '{$descricao}'";

    // Decodificar e processar variantes do produto
    if (isset($g->variantes)) {
        $variantes_base64 = (string)$g->variantes;
        $processing_errors[] = "[DEBUG Variantes] Base64 recebido: '{$variantes_base64}'";

        $variantes_json = base64_decode($variantes_base64);
        if ($variantes_json !== false) {
            $processing_errors[] = "[DEBUG Variantes] JSON decodificado: '{$variantes_json}'";

            $variantes_array = json_decode($variantes_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($variantes_array)) {
                $processing_errors[] = "[DEBUG Variantes] Array de variantes decodificado com sucesso.";

                $variacoes_processadas = [];
                foreach ($variantes_array as $variacao) {
                    if (isset($variacao['nome'], $variacao['descricao'], $variacao['valor'])) {
                        $variacoes_processadas[] = [
                            'nome' => $variacao['nome'],
                            'descricao' => $variacao['descricao'],
                            'valor' => number_format((float)$variacao['valor'], 2, '.', '')
                        ];
                    } else {
                        $processing_errors[] = "[ERRO Variantes] Variante inválida ou incompleta: " . json_encode($variacao);
                    }
                }

                $variacao_json = mysqli_real_escape_string($db_con, json_encode($variacoes_processadas));
                $processing_errors[] = "[DEBUG Variantes] JSON final para salvar no banco: '{$variacao_json}'";
            } else {
                $processing_errors[] = "[ERRO Variantes] JSON de variantes inválido. Erro: " . json_last_error_msg();
            }
        } else {
            $processing_errors[] = "[ERRO Variantes] Base64 de variantes inválido.";
        }
    } else {
        $variacao_json = "[]"; // Sem variantes
        $processing_errors[] = "[DEBUG Variantes] Nenhuma variante encontrada no XML.";
    }

    // Ajustar o processamento de variantes para remover o prefixo "Acompanhamento -" do nome dos itens
    if (isset($g->variants)) {
        $variacoes_processadas = [];

        // Inicializar a definição "Acompanhamento"
        $nome_definicao = isset($g->definition_name) ? base64_encode((string)$g->definition_name) : base64_encode('Acompanhamento');
        $definicao = [
            'nome' => $nome_definicao,
            'escolha_minima' => base64_encode('1'), // Valor padrão codificado
            'escolha_maxima' => base64_encode('1'), // Valor padrão codificado
            'item' => []
        ];

        foreach ($g->variants->variant as $variant) {
            $nome_variante = isset($variant->variant_name) ? (string)$variant->variant_name : '';
            $descricao_variante = isset($variant->variant_description) ? base64_encode((string)$variant->variant_description) : '';
            $preco_variante = isset($variant->variant_price) ? (string)$variant->variant_price : '0.00';

            if (!empty($nome_variante)) {
                // Remover o prefixo "Acompanhamento -" do nome do item, se existir
                $nome_variante = preg_replace('/^Acompanhamento - /', '', $nome_variante);

                // Codificar o nome e o preço em Base64
                $nome_variante = base64_encode($nome_variante);
                $preco_variante = preg_replace('/[^0-9,.]/', '', $preco_variante);
                $preco_variante = str_replace('.', '', $preco_variante);
                $preco_variante = str_replace(',', '.', $preco_variante);
                $preco_variante = number_format((float)$preco_variante, 2, '.', '');
                $preco_variante = base64_encode($preco_variante);

                // Adicionar o item à definição
                $definicao['item'][] = [
                    'nome' => $nome_variante,
                    'descricao' => $descricao_variante,
                    'valor' => $preco_variante
                ];
            } else {
                $processing_errors[] = "[ERRO Variantes] Variante inválida ou incompleta: Nome='{$nome_variante}', Descrição='{$descricao_variante}', Preço='{$preco_variante}'";
            }
        }

        // Adicionar a definição processada ao array de variações
        $variacoes_processadas[] = $definicao;

        $variacao_json = json_encode($variacoes_processadas, JSON_UNESCAPED_UNICODE);
        $processing_errors[] = "[DEBUG Variantes] JSON final para salvar no banco: '{$variacao_json}'";
    } else {
        $variacao_json = "[]"; // Sem variantes
        $processing_errors[] = "[DEBUG Variantes] Nenhuma variante encontrada no XML.";
    }

    // Adicionar logs detalhados para depuração antes da execução da query
    $processing_errors[] = "[DEBUG Query] Dados para inserção: " . json_encode([
        'rel_estabelecimentos_id' => $eid,
        'rel_categorias_id' => $categoria_id,
        'destaque' => $imagem_para_db,
        'nome' => $nome,
        'descricao' => $descricao,
        'valor' => $valor,
        'ref' => $ref,
        'estoque' => $estoque,
        'posicao' => $posicao,
        'oferta' => $oferta,
        'valor_promocional' => $valor_promocional,
        'variacao' => $variacao_json,
        'status' => $status_produto,
        'visible' => $visible,
        'integrado' => $integrado,
        'id_externo' => $id_externo
    ]);

    // Atualiza a inserção do produto para incluir a descrição corrigida e as variantes
    $stmt_insert = mysqli_prepare($db_con, "INSERT INTO produtos (rel_estabelecimentos_id, rel_categorias_id, destaque, nome, descricao, valor, ref, estoque, posicao, oferta, valor_promocional, variacao, status, visible, integrado, id_externo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt_insert) {
        // Ajustar os tipos de dados para os parâmetros vinculados
        mysqli_stmt_bind_param($stmt_insert, "iisssdsiissssiss",
            $eid, $categoria_id, $imagem_para_db, $nome, $descricao, $valor, $ref,
            $estoque, $posicao, $oferta, $valor_promocional, $variacao_json,
            $status_produto, $visible, $integrado, $id_externo
        );

        if (mysqli_stmt_execute($stmt_insert)) {
            $imported_count++;
            $processing_errors[] = "[DEBUG Inserção] Produto '{$nome}' inserido com sucesso no banco de dados.";
        } else {
            $processing_errors[] = "[ERRO Inserção] Erro ao inserir produto '{$nome}' no banco de dados: " . mysqli_stmt_error($stmt_insert);
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        $processing_errors[] = "[ERRO Query] Erro ao preparar a query de inserção para o produto '{$nome}': " . mysqli_error($db_con);
    }

    // Adicionar log para verificar se as variações foram corretamente processadas
    if (!empty($variacao_json) && $variacao_json !== "[]") {
        $processing_errors[] = "[DEBUG Variantes] Variações processadas e prontas para inserção: {$variacao_json}";
    } else {
        $processing_errors[] = "[DEBUG Variantes] Nenhuma variação processada para o produto '{$nome}'.";
    }
}

$response['success'] = true;
$response['message'] = "Processamento do XML concluído.";
$response['imported_count'] = $imported_count;
$response['skipped_count'] = $skipped_count;
$response['errors'] = $processing_errors;

echo json_encode($response);
exit;

?>