<?php
/**
 * API para Newsletter - Sistema de Marketplace
 * Arquivo: newsletter_api.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuração de erro
ini_set('display_errors', 0);
error_reporting(0);

// Função para resposta JSON
function jsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

try {
    // Conexão com o banco
    include_once '../../../_core/_includes/config_database.php';
    
    // Fallback se o include não funcionar
    if (!isset($db)) {
        $db_host = "localhost";
        $db_user = "tecautov_sistema";
        $db_pass = "hevRQ6#Ur]6R";
        $db_name = "tecautov_sistema";
        
        $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($db->connect_error) {
            throw new Exception("Erro de conexão: " . $db->connect_error);
        }
        
        $db->set_charset("utf8mb4");
    }
    
    // Verificar método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Receber dados JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Se não veio JSON, tentar $_POST
        if (!$input) {
            $input = $_POST;
        }
        
        // Validar dados obrigatórios
        if (empty($input['nome']) || empty($input['email'])) {
            jsonResponse(false, 'Nome e email são obrigatórios');
        }
        
        $nome = sanitize($input['nome']);
        $email = sanitize($input['email']);
        $cidade = sanitize($input['cidade'] ?? '');
        $promocoes = isset($input['promocoes']) ? (bool)$input['promocoes'] : true;
        $novas_lojas = isset($input['novas_lojas']) ? (bool)$input['novas_lojas'] : true;
        
        // Validar email
        if (!validarEmail($email)) {
            jsonResponse(false, 'Email inválido');
        }
        
        // Verificar se já existe
        $check = $db->prepare("SELECT id, ativo FROM newsletter WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            
            if ($existing['ativo']) {
                jsonResponse(false, 'Este email já está cadastrado em nossa newsletter');
            } else {
                // Reativar cadastro
                $update = $db->prepare("UPDATE newsletter SET nome = ?, cidade = ?, promocoes = ?, novas_lojas = ?, ativo = 1 WHERE email = ?");
                $update->bind_param("sssis", $nome, $cidade, $promocoes, $novas_lojas, $email);
                
                if ($update->execute()) {
                    jsonResponse(true, 'Inscrição reativada com sucesso! Bem-vindo de volta!');
                } else {
                    throw new Exception("Erro ao reativar inscrição");
                }
            }
        } else {
            // Novo cadastro
            $insert = $db->prepare("INSERT INTO newsletter (nome, email, cidade, promocoes, novas_lojas) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssii", $nome, $email, $cidade, $promocoes, $novas_lojas);
            
            if ($insert->execute()) {
                // Log da ação
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $log = $db->prepare("INSERT INTO sistema_logs (acao, tabela_afetada, id_registro, dados_novos, usuario_ip) VALUES (?, ?, ?, ?, ?)");
                $acao = 'newsletter_inscricao';
                $tabela = 'newsletter';
                $id_registro = $db->insert_id;
                $dados = json_encode($input);
                $log->bind_param("ssiss", $acao, $tabela, $id_registro, $dados, $ip);
                $log->execute();
                
                jsonResponse(true, 'Inscrição realizada com sucesso! Você receberá nossas novidades em breve.');
            } else {
                throw new Exception("Erro ao realizar inscrição");
            }
        }
        
    } elseif ($method === 'GET') {
        // Estatísticas básicas (apenas para admins)
        $stats = [
            'total_inscritos' => 0,
            'inscritos_ativos' => 0,
            'cidades_top' => []
        ];
        
        // Total de inscritos
        $total = $db->query("SELECT COUNT(*) as total FROM newsletter WHERE ativo = 1");
        if ($total) {
            $stats['total_inscritos'] = $total->fetch_assoc()['total'];
        }
        
        // Cidades com mais inscritos
        $cidades = $db->query("SELECT cidade, COUNT(*) as total FROM newsletter WHERE ativo = 1 AND cidade != '' GROUP BY cidade ORDER BY total DESC LIMIT 5");
        if ($cidades) {
            while ($cidade = $cidades->fetch_assoc()) {
                $stats['cidades_top'][] = $cidade;
            }
        }
        
        jsonResponse(true, 'Estatísticas obtidas com sucesso', $stats);
        
    } else {
        jsonResponse(false, 'Método não permitido');
    }
    
} catch (Exception $e) {
    error_log("Newsletter API Error: " . $e->getMessage());
    jsonResponse(false, 'Erro interno do servidor. Tente novamente mais tarde.');
}
?>
