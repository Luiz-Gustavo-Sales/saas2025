<?php
// CORE
include('../../_core/_includes/config.php');
// RESTRICT
restrict(2);
atualiza_estabelecimento( $_SESSION['estabelecimento']['id'], "online" );
// SEO
$seo_subtitle = "Impressão";
$seo_description = "";
$seo_keywords = "";
// HEADER
$system_header .= "";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');

// Debug: verificar se a sessão está funcionando
if(!isset($_SESSION['estabelecimento']['id'])) {
    echo '<script>console.log("ERRO: Sessão do estabelecimento não encontrada!");</script>';
} else {
    echo '<script>console.log("ID do estabelecimento: ' . $_SESSION['estabelecimento']['id'] . '");</script>';
}

global $db_con;
global $whatsapp;
$eid = $_SESSION['estabelecimento']['id'];
$meudominio = $httprotocol.$simple_url."/api/?token=";

$sql = mysqli_query( $db_con, "SELECT * FROM impressao WHERE ide = '$eid'" );
$total_results = mysqli_num_rows( $sql );

// Se existe um parâmetro para regenerar, deletar registro existente
if(isset($_GET['regenerar']) && $_GET['regenerar'] == '1') {
    mysqli_query( $db_con, "DELETE FROM impressao WHERE ide = '$eid'" );
    // Deletar arquivo login.json se existir
    if(file_exists('login.json')) {
        unlink('login.json');
    }
    header("Location: index.php");
    exit;
}

if($total_results <=0 ){

for ($i = 0; $i < 20; $i++)
{ $token = uniqid(rand(), true); }

$token = "MDT".$eid.$token;

$sql = mysqli_query( $db_con, "INSERT INTO impressao (ide,status,token) VALUES ('$eid','2','$token');" );

$data = array(
    "Url" => $httprotocol.$simple_url."/api/?token=",
    "Token" => $token
);

// Debug: mostrar a URL que está sendo gerada
echo '<script>console.log("URL gerada: ' . $httprotocol.$simple_url.'/api/?token=");</script>';
echo '<script>console.log("Token gerado: ' . $token . '");</script>';

$arquivo = 'login.json';
$json = json_encode($data);
$file = fopen($arquivo,'w');
if($file) {
    fwrite($file, $json);
    fclose($file);
    header("Location: index.php?msg=sucesso");
} else {
    header("Location: index.php?msg=erro");
}

} else { 

$datatoken = mysqli_fetch_array( $sql );

}

// Garantir que temos o token para mostrar no debug
if(!isset($datatoken)) {
    $sql_token = mysqli_query( $db_con, "SELECT * FROM impressao WHERE ide = '$eid'" );
    if(mysqli_num_rows($sql_token) > 0) {
        $datatoken = mysqli_fetch_array($sql_token);
    }
}

// Verificar se há mensagem de sucesso
if(isset($_GET['msg'])) {
    if($_GET['msg'] == 'sucesso') {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Arquivo de configuração gerado com sucesso!",
                    icon: "success",
                    confirmButtonText: "Ok"
                });
            });
        </script>';
    } elseif($_GET['msg'] == 'erro') {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Erro!",
                    text: "Não foi possível criar o arquivo de configuração. Verifique as permissões da pasta.",
                    icon: "error",
                    confirmButtonText: "Ok"
                });
            });
        </script>';
    }
}

?>

<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

				<div class="title-icon pull-left">
					<i class="lni lni-database"></i>
					<span>Impressão Automática</span>
				</div>

				<div class="bread-box pull-right">
					<div class="bread">
						<a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
						<span>/</span>
						<a href="<?php panel_url(); ?>/impressao">Impressão Automática</a>
					</div>
				</div>

			</div>

		</div>

		<div class="integracao">

			<div class="data box-white mt-16">

	            <div class="row">

	              <div class="col-md-12">

	                <div class="title-line pd-0">
	                  <i class="lni lni-printer"></i>
	                  <span>Impressão Automática</span>
	                  <div class="clear"></div>
	                </div>

	              </div>

	            </div>

			<!-- Informações de Debug -->
			<div class="row">
				<div class="col-md-12">
					<div class="form-field-default" style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px;">
						<label><strong>🔍 Informações de Debug:</strong></label><br/>
						<strong>Estabelecimento ID:</strong> <?php echo $eid; ?><br/>
						<?php 
						// Buscar nome do estabelecimento
						$sql_est = mysqli_query($db_con, "SELECT nome FROM estabelecimentos WHERE id = '$eid'");
						$estabelecimento = mysqli_fetch_array($sql_est);
						?>
						<strong>Nome:</strong> <?php echo $estabelecimento['nome']; ?><br/>
						<strong>Token Atual:</strong> <?php echo isset($datatoken['token']) ? substr($datatoken['token'], 0, 20) . '...' : 'Não gerado'; ?><br/>
						<?php 
						// Contar pedidos pendentes
						$sql_pendentes = mysqli_query($db_con, "SELECT COUNT(*) as total FROM pedidos WHERE rel_estabelecimentos_id = '$eid' AND status = '1' AND (statusp IS NULL OR statusp = '0' OR statusp = '1')");
						$pendentes = mysqli_fetch_array($sql_pendentes);
						?>
						<strong>Pedidos Pendentes:</strong> <?php echo $pendentes['total']; ?><br/>
						<strong>URL da API:</strong> <?php echo $httprotocol.$simple_url; ?>/api/<br/>
						<?php if(isset($datatoken['token'])): ?>
						<br/>
						<strong>🧪 URLs de Teste:</strong><br/>
						<a href="../../api/debug_impressao.php?token=<?php echo $datatoken['token']; ?>" target="_blank" style="color: #007bff; text-decoration: none;">🔍 Debug Completo (JSON)</a> | 
						<a href="../../api/teste_auto_printer.php?token=<?php echo $datatoken['token']; ?>" target="_blank" style="color: #28a745; text-decoration: none;">🧪 Teste Auto-Printer</a> | 
						<a href="../../api/index.php?token=<?php echo $datatoken['token']; ?>" target="_blank" style="color: #dc3545; text-decoration: none;">🔧 API Original</a> |
						<a href="../../api/teste_token_especifico.php" target="_blank" style="color: #6f42c1; text-decoration: none;">🎯 Teste Específico</a> |
						<a href="../../api/gerar_login_json.php" target="_blank" style="color: #17a2b8; text-decoration: none;">📄 Gerar Login.json</a> |
						<a href="../../api/resetar_pedidos.php" target="_blank" style="color: #dc3545; text-decoration: none;">� Resetar Pedidos</a> |
						<a href="../../api/explicacao_sistema.php" target="_blank" style="color: #28a745; text-decoration: none;">� Como Funciona</a>
						<br/><br/>
						<strong>🔗 Token para copiar:</strong><br/>
						<input type="text" value="<?php echo $datatoken['token']; ?>" onclick="this.select(); document.execCommand('copy');" style="width: 100%; padding: 5px; font-family: monospace;" readonly />
						<small style="color: #666;">Clique no campo acima para copiar o token</small>
						<?php endif; ?>
					</div>
				</div>
			</div>

 	            <div class="row">

	              <div class="col-md-12">

		              <div class="form-field-default">

		                  <label>Tutorial (Passo a passo):</label>
		                  
						  1 - Solicite o arquivo de instalação do sistema com o administrador.<br/>
						  
						  2 - Após instalar o sistema clique no botão abaixo para baixar o arquivo de liberação e coloque dentro da pasta do sistema em:<br/>
						  <br/>
						  <b>C:\Program Files (x86)\impressao</b> ou <b>C:\Program Files\impressao</b><br/>
						  <br/>
						  3 - Após isso execute o sistema como administrador.
		                

		              </div>

	              </div>

	            </div> 
<div class="row">
 
	            <div class="col-md-3">
	            	<label></label>
					<a href="login.json" download>
	              	 <button>
	              		<span>
	              			<i class="lni lni-clipboard"></i> Baixar Arquivo
	              		</span>
						</button>
	              	 </a>
	              </div>
	              
	              <div class="col-md-3">
	            	<label></label>
					<a href="index.php?regenerar=1">
	              	 <button style="background-color: #ff6b6b; border-color: #ff6b6b;">
	              		<span>
	              			<i class="lni lni-reload"></i> Regenerar Arquivo
	              		</span>
						</button>
	              	 </a>
	              </div>
	              
	              <div class="col-md-3">
	            	<label></label>
					<a href="../../api/criar_pedido_teste.php" target="_blank">
	              	 <button style="background-color: #28a745; border-color: #28a745;">
	              		<span>
	              			<i class="lni lni-plus"></i> Criar Pedido Teste
	              		</span>
						</button>
	              	 </a>
	              </div>
	              
	              <div class="col-md-3">
	            	<label></label>
					<a href="../../api/monitor_pedidos.php" target="_blank">
	              	 <button style="background-color: #ffc107; border-color: #ffc107;">
	              		<span>
	              			<i class="lni lni-eye"></i> Monitor Pedidos
	              		</span>
						</button>
	              	 </a>
	              </div>

	          </div>
			
			<div class="row">
 
	            <div class="col-md-3">
	            	<label></label>
					<a href="https://wa.me/<?php echo $whatsapp ?>" target="_blank">
	              	 <button>
	              		<span>
	              			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
  <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
</svg> Chamar o suporte
	              		</span>
						</button>
	              	 </a>
	              </div>

	          </div>

			</div>

		 </div>

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>
