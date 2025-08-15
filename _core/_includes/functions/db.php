<?php

// Verificar se as variáveis de configuração estão definidas
if (!isset($db_host) || !isset($db_user) || !isset($db_pass) || !isset($db_name)) {
    error_log("ERRO: Variáveis de configuração do banco não definidas");
    $db_con = false;
} else {
    // Tentar conectar (sem logs excessivos)
    $db_con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (!$db_con) {
        error_log("ERRO: Falha na conexão com banco de dados - " . mysqli_connect_error());
    } else {
        mysqli_set_charset($db_con, "utf8mb4");
        // Log apenas uma vez por sessão
        if (!isset($_SESSION['db_connected_logged'])) {
            error_log("Conexão com banco estabelecida com sucesso");
            $_SESSION['db_connected_logged'] = true;
        }
    }
}

?>