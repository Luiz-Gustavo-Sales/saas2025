<?php
// AJAX para obter dados da sessão atual
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Iniciar sessão se necessário
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Verificar se existe sessão do estabelecimento
    if (isset($_SESSION['estabelecimento']['id'])) {
        echo json_encode([
            'success' => true,
            'estabelecimento_id' => (int)$_SESSION['estabelecimento']['id'],
            'estabelecimento_nome' => $_SESSION['estabelecimento']['nome'] ?? 'Não informado',
            'timestamp' => time()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Sessão não encontrada',
            'timestamp' => time()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>
