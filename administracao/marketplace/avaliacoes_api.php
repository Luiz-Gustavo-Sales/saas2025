<?php
/**
 * API para Avaliações - Sistema de Marketplace
 * Arquivo: avaliacoes_api.php
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

// Função para sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Função para validar rating
function validarRating($rating) {
    return is_numeric($rating) && $rating >= 1 && $rating <= 5;
}

// Função para verificar rate limiting (máximo 3 avaliações por IP por dia)
function verificarRateLimit($db, $ip) {
    $hoje = date('Y-m-d');
    $check = $db->prepare("SELECT COUNT(*) as total FROM avaliacoes WHERE ip_avaliador = ? AND DATE(data_avaliacao) = ?");
    $check->bind_param("ss", $ip, $hoje);
    $check->execute();
    $result = $check->get_result();
    $count = $result->fetch_assoc()['total'];
    
    return $count < 3; // máximo 3 avaliações por dia
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
    
    $method = $_SERVER['REQUEST_METHOD'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if ($method === 'POST') {
        // Nova avaliação
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        // Validar dados obrigatórios
        if (empty($input['estabelecimento_id']) || empty($input['nome_avaliador']) || empty($input['rating'])) {
            jsonResponse(false, 'Estabelecimento, nome e avaliação são obrigatórios');
        }
        
        $estabelecimento_id = (int)$input['estabelecimento_id'];
        $nome_avaliador = sanitize($input['nome_avaliador']);
        $rating = (int)$input['rating'];
        $comentario = sanitize($input['comentario'] ?? '');
        
        // Validações
        if (!validarRating($rating)) {
            jsonResponse(false, 'Avaliação deve ser entre 1 e 5 estrelas');
        }
        
        if (strlen($nome_avaliador) < 2) {
            jsonResponse(false, 'Nome deve ter pelo menos 2 caracteres');
        }
        
        // Verificar rate limiting
        if (!verificarRateLimit($db, $ip)) {
            jsonResponse(false, 'Limite de avaliações por dia atingido. Tente novamente amanhã.');
        }
        
        // Verificar se o estabelecimento existe
        $check_estab = $db->prepare("SELECT id, nome FROM estabelecimentos WHERE id = ? AND status = '1' AND excluded != '1'");
        $check_estab->bind_param("i", $estabelecimento_id);
        $check_estab->execute();
        $estab_result = $check_estab->get_result();
        
        if ($estab_result->num_rows === 0) {
            jsonResponse(false, 'Estabelecimento não encontrado');
        }
        
        $estabelecimento = $estab_result->fetch_assoc();
        
        // Inserir avaliação
        $insert = $db->prepare("INSERT INTO avaliacoes (estabelecimento_id, nome_avaliador, rating, comentario, ip_avaliador) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("isiss", $estabelecimento_id, $nome_avaliador, $rating, $comentario, $ip);
        
        if ($insert->execute()) {
            $avaliacao_id = $db->insert_id;
            
            // Log da ação
            $log = $db->prepare("INSERT INTO sistema_logs (acao, tabela_afetada, id_registro, dados_novos, usuario_ip) VALUES (?, ?, ?, ?, ?)");
            $acao = 'nova_avaliacao';
            $tabela = 'avaliacoes';
            $dados = json_encode([
                'estabelecimento_id' => $estabelecimento_id,
                'nome_estabelecimento' => $estabelecimento['nome'],
                'rating' => $rating,
                'nome_avaliador' => $nome_avaliador
            ]);
            $log->bind_param("ssiss", $acao, $tabela, $avaliacao_id, $dados, $ip);
            $log->execute();
            
            // Registrar estatística
            $stat = $db->prepare("INSERT INTO marketplace_stats (estabelecimento_id, tipo_acao, ip_usuario) VALUES (?, 'avaliacao', ?)");
            $stat->bind_param("is", $estabelecimento_id, $ip);
            $stat->execute();
            
            jsonResponse(true, 'Avaliação enviada com sucesso! Obrigado pelo seu feedback.');
            
        } else {
            throw new Exception("Erro ao salvar avaliação");
        }
        
    } elseif ($method === 'GET') {
        // Buscar avaliações
        $estabelecimento_id = $_GET['estabelecimento_id'] ?? null;
        
        if ($estabelecimento_id) {
            // Avaliações de um estabelecimento específico
            $estabelecimento_id = (int)$estabelecimento_id;
            
            // Estatísticas
            $stats_query = $db->prepare("
                SELECT 
                    COUNT(*) as total_avaliacoes,
                    AVG(rating) as media_rating,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as cinco_estrelas,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as quatro_estrelas,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as tres_estrelas,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as duas_estrelas,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as uma_estrela
                FROM avaliacoes 
                WHERE estabelecimento_id = ? AND ativo = 1
            ");
            $stats_query->bind_param("i", $estabelecimento_id);
            $stats_query->execute();
            $stats = $stats_query->get_result()->fetch_assoc();
            
            // Avaliações recentes (com comentários)
            $avaliacoes_query = $db->prepare("
                SELECT nome_avaliador, rating, comentario, data_avaliacao 
                FROM avaliacoes 
                WHERE estabelecimento_id = ? AND ativo = 1 AND comentario != ''
                ORDER BY data_avaliacao DESC 
                LIMIT 10
            ");
            $avaliacoes_query->bind_param("i", $estabelecimento_id);
            $avaliacoes_query->execute();
            $avaliacoes_result = $avaliacoes_query->get_result();
            
            $avaliacoes = [];
            while ($avaliacao = $avaliacoes_result->fetch_assoc()) {
                $avaliacoes[] = [
                    'nome' => $avaliacao['nome_avaliador'],
                    'rating' => (int)$avaliacao['rating'],
                    'comentario' => $avaliacao['comentario'],
                    'data' => date('d/m/Y', strtotime($avaliacao['data_avaliacao']))
                ];
            }
            
            $data = [
                'estatisticas' => [
                    'total' => (int)$stats['total_avaliacoes'],
                    'media' => round($stats['media_rating'], 1),
                    'distribuicao' => [
                        5 => (int)$stats['cinco_estrelas'],
                        4 => (int)$stats['quatro_estrelas'],
                        3 => (int)$stats['tres_estrelas'],
                        2 => (int)$stats['duas_estrelas'],
                        1 => (int)$stats['uma_estrela']
                    ]
                ],
                'avaliacoes_recentes' => $avaliacoes
            ];
            
            jsonResponse(true, 'Avaliações obtidas com sucesso', $data);
            
        } else {
            // Estatísticas gerais do marketplace
            $stats_gerais = $db->query("
                SELECT 
                    COUNT(*) as total_avaliacoes,
                    AVG(rating) as media_geral,
                    COUNT(DISTINCT estabelecimento_id) as estabelecimentos_avaliados
                FROM avaliacoes 
                WHERE ativo = 1
            ");
            
            $stats = $stats_gerais->fetch_assoc();
            
            // Top estabelecimentos mais bem avaliados
            $top_estabelecimentos = $db->query("
                SELECT 
                    e.id, e.nome, 
                    AVG(a.rating) as media_rating,
                    COUNT(a.id) as total_avaliacoes
                FROM estabelecimentos e
                INNER JOIN avaliacoes a ON e.id = a.estabelecimento_id
                WHERE e.status = '1' AND e.excluded != '1' AND a.ativo = 1
                GROUP BY e.id, e.nome
                HAVING COUNT(a.id) >= 3
                ORDER BY media_rating DESC, total_avaliacoes DESC
                LIMIT 10
            ");
            
            $top_estab = [];
            while ($top = $top_estabelecimentos->fetch_assoc()) {
                $top_estab[] = [
                    'id' => $top['id'],
                    'nome' => $top['nome'],
                    'media' => round($top['media_rating'], 1),
                    'total_avaliacoes' => (int)$top['total_avaliacoes']
                ];
            }
            
            $data = [
                'estatisticas_gerais' => [
                    'total_avaliacoes' => (int)$stats['total_avaliacoes'],
                    'media_geral' => round($stats['media_geral'], 1),
                    'estabelecimentos_avaliados' => (int)$stats['estabelecimentos_avaliados']
                ],
                'top_estabelecimentos' => $top_estab
            ];
            
            jsonResponse(true, 'Estatísticas gerais obtidas', $data);
        }
        
    } else {
        jsonResponse(false, 'Método não permitido');
    }
    
} catch (Exception $e) {
    error_log("Avaliacoes API Error: " . $e->getMessage());
    jsonResponse(false, 'Erro interno do servidor. Tente novamente mais tarde.');
}
?>
