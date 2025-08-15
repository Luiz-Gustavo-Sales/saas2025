<?php
// Teste da função htmljson
function htmljson($str) {
    $str = htmlentities( base64_decode( $str ) );
    return $str;
}

// Testar com diferentes valores
$teste1 = base64_encode("5");
echo "Teste 1: '" . $teste1 . "' -> '" . htmljson($teste1) . "' -> " . intval(htmljson($teste1)) . "\n";

$teste2 = base64_encode("0");
echo "Teste 2: '" . $teste2 . "' -> '" . htmljson($teste2) . "' -> " . intval(htmljson($teste2)) . "\n";

$teste3 = base64_encode("10");
echo "Teste 3: '" . $teste3 . "' -> '" . htmljson($teste3) . "' -> " . intval(htmljson($teste3)) . "\n";

// Testar decodificação direta
$teste4 = base64_encode("5");
echo "Teste 4 (direto): '" . $teste4 . "' -> '" . base64_decode($teste4) . "' -> " . intval(base64_decode($teste4)) . "\n";
?>
