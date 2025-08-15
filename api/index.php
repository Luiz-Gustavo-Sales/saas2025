<?php

// CORE
include('../_core/_includes/config.php');

// APP
global $app;

$token = mysqli_real_escape_string( $db_con, $_GET['token'] );

$contatoken = strlen($token);

if($contatoken <= 10) {
    // Token inválido - retorna código específico, não 400
    echo '300';
    exit;
}

if (preg_match("/^([a-zA-Z0-9\.]+)$/", $token)) {

$vertoken = mysqli_query( $db_con, "SELECT ide FROM impressao WHERE token = '$token'");
$hastoken = mysqli_num_rows( $vertoken );

if($hastoken >= 1) {

$datatoken = mysqli_fetch_array( $vertoken );
$iduser = $datatoken['ide'];

// Log apenas se debug estiver ativo (evitar spam no log)
if(isset($_GET['debug'])) {
    error_log("Auto-Printer DEBUG: Consultando estabelecimento ID: " . $iduser);
}

// BUSCAR PEDIDOS VÁLIDOS PARA IMPRESSÃO
// Critérios: status=1 (pendente), não impresso (statusp NULL ou 0), com comprovante válido
$verpedido = mysqli_query( $db_con, "
    SELECT id, comprovante, nome, status, statusp, data_hora 
    FROM pedidos 
    WHERE rel_estabelecimentos_id = '$iduser' 
    AND status = '1' 
    AND (statusp IS NULL OR statusp = '0') 
    AND comprovante IS NOT NULL 
    AND comprovante != '' 
    AND comprovante != '*'
    AND CHAR_LENGTH(comprovante) > 50
    ORDER BY id ASC 
    LIMIT 1
");

$haspedido = mysqli_num_rows( $verpedido );

// Se não encontrou pedidos prontos, verificar se há pedidos que precisam de comprovante
if($haspedido == 0) {
    $verpedido_sem_comprovante = mysqli_query( $db_con, "
        SELECT id, nome, status, statusp, data_hora 
        FROM pedidos 
        WHERE rel_estabelecimentos_id = '$iduser' 
        AND status = '1' 
        AND (statusp IS NULL OR statusp = '0') 
        AND (comprovante IS NULL OR comprovante = '' OR comprovante = '*' OR CHAR_LENGTH(comprovante) < 50)
        AND data_hora >= DATE_SUB(NOW(), INTERVAL 2 HOUR) 
        ORDER BY id ASC 
        LIMIT 1
    ");
    
    if(mysqli_num_rows($verpedido_sem_comprovante) > 0) {
        $pedido_sem_comprovante = mysqli_fetch_array($verpedido_sem_comprovante);
        $pedido_id = $pedido_sem_comprovante['id'];
        
        if(isset($_GET['debug'])) {
            error_log("Auto-Printer DEBUG: Encontrado pedido ID $pedido_id sem comprovante, gerando...");
        }
        
        // Incluir a função de gerar comprovante
        include('../_core/_includes/functions/user.php');
        
        // Gerar comprovante para o pedido
        $comprovante_gerado = gera_comprovante($iduser, "texto", "1", $pedido_id);
        
        if($comprovante_gerado && trim($comprovante_gerado) != '' && strlen($comprovante_gerado) > 50) {
            // Salvar comprovante no banco
            $comprovante_escapado = mysqli_real_escape_string($db_con, $comprovante_gerado);
            mysqli_query($db_con, "UPDATE pedidos SET comprovante = '$comprovante_escapado' WHERE id = '$pedido_id'");
            
            if(isset($_GET['debug'])) {
                error_log("Auto-Printer DEBUG: Comprovante gerado para pedido ID $pedido_id");
            }
            
            // Buscar novamente o pedido com o comprovante gerado
            $verpedido = mysqli_query( $db_con, "SELECT id, comprovante, nome, status, statusp, data_hora FROM pedidos WHERE id = '$pedido_id'");
            $haspedido = mysqli_num_rows( $verpedido );
        }
    }
}

if($haspedido >= 1) {

    $datacomprovante = mysqli_fetch_array( $verpedido );
    
    if(isset($_GET['debug'])) {
        error_log("Auto-Printer DEBUG: Processando pedido ID: " . $datacomprovante['id']);
    }

    // Verificar se o comprovante é válido
    if(!empty($datacomprovante['comprovante']) && 
       trim($datacomprovante['comprovante']) != '' && 
       trim($datacomprovante['comprovante']) != '*' &&
       strlen($datacomprovante['comprovante']) > 50) {
        
        // Comprovante válido - enviar para impressão
        $comprovante_limpo = str_replace("*", "", $datacomprovante['comprovante']);
        echo $comprovante_limpo;
        
        $idx = $datacomprovante['id'];
        
        // Marcar pedido como impresso (statusp = 2)
        mysqli_query( $db_con, "UPDATE pedidos SET statusp = '2' WHERE id = '$idx'");
        
        if(isset($_GET['debug'])) {
            error_log("Auto-Printer DEBUG: Pedido ID $idx impresso com sucesso");
        }
        
    } else {
        // Comprovante inválido - tentar regenerar uma vez
        include('../_core/_includes/functions/user.php');
        $comprovante_novo = gera_comprovante($iduser, "texto", "1", $datacomprovante['id']);
        
        if($comprovante_novo && trim($comprovante_novo) != '' && strlen($comprovante_novo) > 50) {
            $comprovante_escapado = mysqli_real_escape_string($db_con, $comprovante_novo);
            mysqli_query($db_con, "UPDATE pedidos SET comprovante = '$comprovante_escapado' WHERE id = '" . $datacomprovante['id'] . "'");
            
            // Usar o comprovante regenerado
            $comprovante_limpo = str_replace("*", "", $comprovante_novo);
            echo $comprovante_limpo;
            
            $idx = $datacomprovante['id'];
            mysqli_query( $db_con, "UPDATE pedidos SET statusp = '2' WHERE id = '$idx'");
            
            if(isset($_GET['debug'])) {
                error_log("Auto-Printer DEBUG: Pedido ID $idx impresso com comprovante regenerado");
            }
        } else {
            // Falha ao gerar comprovante - marcar como erro
            $idx = $datacomprovante['id'];
            mysqli_query($db_con, "UPDATE pedidos SET statusp = '9' WHERE id = '$idx'");
            
            if(isset($_GET['debug'])) {
                error_log("Auto-Printer ERROR: Pedido ID $idx marcado com erro - falha ao gerar comprovante");
            }
            
            // IMPORTANTE: Não retornar nada quando há erro - deixar Auto-Printer aguardar
            echo '';
        }
    }

} else {
    
    // NENHUM PEDIDO ENCONTRADO - IMPORTANTE: NÃO RETORNAR 400!
    // O Auto-Printer deve aguardar silenciosamente
    
    // Log apenas em modo debug para não encher o log
    if(isset($_GET['debug'])) {
        error_log("Auto-Printer DEBUG: Estabelecimento $iduser - Nenhum pedido para impressão em " . date('Y-m-d H:i:s'));
    }
    
    // Retornar string vazia ao invés de 400
    // Isso fará o Auto-Printer continuar aguardando sem tentar imprimir
    echo '';
    
}

} else {
    // Token não encontrado
    echo '300';
}

} else {
    // Token com formato inválido
    echo '300';
}

?>