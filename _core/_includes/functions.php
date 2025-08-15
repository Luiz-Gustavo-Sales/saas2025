<?php
// O sistema de autenticação de domínio é agora gerenciado pelo domain_auth.php
// que é carregado automaticamente no config.php

include('functions/db.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/_core/_includes/functions/phpmailer/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_core/_includes/functions/phpmailer/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/_core/_includes/functions/phpmailer/Exception.php');

// Usa o namespace correto do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('functions/general.php');
include('functions/data.php');
include('functions/upload.php');
include('functions/user.php');
include('functions/aditional.php');


// FUNÇÕES ADICIONAIS
function verifica_horario($id) {
    global $db_con;
    $aberto = "disabled";

    $fechamento_query = "SELECT * FROM `agendamentos` WHERE rel_estabelecimentos_id = $id AND acao = 2";
    $dia_atual = strtolower(substr(date("l"), 0, 3));
    date_default_timezone_set('America/Sao_Paulo'); // Corrigido timezone 

    $fechamento_data = mysqli_fetch_array(mysqli_query($db_con, $fechamento_query));
    $horario_fechamento = strtotime($fechamento_data['hora']);
    $horario_atual = time();

    $abertura_query = "SELECT * FROM `agendamentos` WHERE rel_estabelecimentos_id = $id AND acao = 1 AND `$dia_atual` = 1";
    $abertura_result = mysqli_fetch_array(mysqli_query($db_con, $abertura_query));
    $horario_abertura = strtotime($abertura_result['hora']);

    if ($abertura_result[$dia_atual] == 1 && $fechamento_data[$dia_atual] == 1) {
        if ($horario_atual < $horario_abertura || $horario_atual >= $horario_fechamento) {
            $aberto = "close";
        } else {
            $aberto = "open";
        }
    } else {
        $aberto = "disabled";
    }

    return $aberto;
}


// ✅ Função de envio de e-mail com PHPMailer
function send_email($to, $subject, $body) {
    global $smtp_user, $smtp_pass, $smtp_host, $smtp_port, $smtp_secure;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port       = $smtp_port;

        $mail->setFrom($smtp_user, 'Suporte Digitavitrine');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        return $mail->send();
    } catch (Exception $e) {
        if (function_exists('log_data_ajax')) {
            log_data_ajax("Erro ao enviar email: " . $mail->ErrorInfo);
        }
        return false;
    }
}

// Proteção adicional - verifica domínio periodicamente
if (!function_exists('verify_domain_periodically')) {
    function verify_domain_periodically() {
        // Verifica se a sessão ainda é válida para o domínio
        if (session_status() == PHP_SESSION_ACTIVE) {
            $current_domain = $_SERVER['HTTP_HOST'] ?? '';
            $current_domain = preg_replace('/^www\./', '', strtolower($current_domain));
            
            // Se o domínio mudou, força nova verificação
            if (isset($_SESSION['verified_domain']) && $_SESSION['verified_domain'] !== $current_domain) {
                session_destroy();
                DomainAuth::checkDomainAuth();
            }
            
            $_SESSION['verified_domain'] = $current_domain;
        }
    }
}

// Executa verificação periódica
verify_domain_periodically();

// } else {
//     // Domínio não autorizado
//     echo "<div style='display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; font-size: 24px;'>
//           Acesso não autorizado. Entre em contato com o administrador.<br><br>
//           <a href='https://api.whatsapp.com/send?&text=Comprei%20um%20sistema%20de%20cat%C3%A1logos%20mais%20nao%20est%C3%A1%20ativando%20pode%20me%20ajudar?'>Clique aqui</a> para entrar em contato.</div>";
//     exit;
// }
?>
