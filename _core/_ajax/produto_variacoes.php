<?php
include('../_includes/config.php');

header('Content-Type: application/json');

if (isset($_GET['produto_id'])) {
    $produto_id = mysqli_real_escape_string($db_con, $_GET['produto_id']);
    
    $query = "SELECT * FROM produto_variacoes WHERE rel_produtos_id = '$produto_id' AND status = '1' ORDER BY nome ASC";
    $result = mysqli_query($db_con, $query);
    
    $variacoes = array();
    while ($row = mysqli_fetch_array($result)) {
        $variacoes[] = array(
            'id' => $row['id'],
            'nome' => $row['nome'],
            'valor' => $row['valor'],
            'valor_promocional' => $row['valor_promocional'],
            'oferta' => $row['oferta']
        );
    }
    
    echo json_encode($variacoes);
} else {
    echo json_encode(array());
}
?>
