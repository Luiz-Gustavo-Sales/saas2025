<?php



include('../../../_core/_includes/config.php'); 



$token = mysqli_real_escape_string( $db_con, $_POST['token'] );



$modo = $_POST['modo'];

// Log de debug para início da requisição
error_log("[SACOLA DEBUG] ===== NOVA REQUISIÇÃO =====");
error_log("[SACOLA DEBUG] Token: $token");
error_log("[SACOLA DEBUG] Modo: $modo");
error_log("[SACOLA DEBUG] POST completo: " . json_encode($_POST));



// Log de inicio para verificar se requisição chegou
error_log("[SACOLA ENDPOINT] ===== REQUISIÇÃO RECEBIDA =====");
error_log("[SACOLA ENDPOINT] POST recebido: " . json_encode($_POST));
error_log("[SACOLA ENDPOINT] Timestamp: " . date('Y-m-d H:i:s'));


// Iniciar sessão com o token correto
if (session_status() == PHP_SESSION_NONE) {
    session_id( $token );
    session_start();
} else {
    session_write_close(); // Fechar sessão atual
    session_id( $token );
    session_start();
}

// Log da sessão para debug
error_log("[SACOLA DEBUG] Sessão iniciada. Status: " . session_status());
error_log("[SACOLA DEBUG] Session ID atual: " . session_id());
error_log("[SACOLA DEBUG] Token recebido: " . $token);
error_log("[SACOLA DEBUG] Conteúdo da sessão: " . json_encode($_SESSION));

// Verificar se a sessão tem dados da sacola
if (isset($_SESSION['sacola'])) {
    error_log("[SACOLA DEBUG] Sacola encontrada na sessão");
} else {
    error_log("[SACOLA DEBUG] ⚠️ Sacola NÃO encontrada na sessão!");
}



$eid = mysqli_real_escape_string( $db_con, $_POST['eid'] );



$pid = mysqli_real_escape_string( $db_con, $_POST['produto'] );



$produto = mysqli_real_escape_string( $db_con, $_POST['produto'] );



$parsedata = parse_str( $_POST['data'], $data );



//print_r($_POST['data']);



//print_r($data);



print_r($data['variacoes']);



$quantidade = $data['quantidade'];



$observacoes = $data['observacoes'];



$variacoes = $data['variacao'];



//print_r($variacoes);



// CHECKOUT







$nome = mysqli_real_escape_string( $db_con, $_POST['nome'] );



$cpf   = !empty($_POST['cpf']) ? "'" . mysqli_real_escape_string($db_con, $_POST['cpf']) . "'" : "NULL";



$email = !empty($_POST['email']) ? "'" . mysqli_real_escape_string($db_con, $_POST['email']) . "'" : "NULL";



$whatsapp = mysqli_real_escape_string( $db_con, clean_str( $_POST['whatsapp'] ) );



$forma_entrega = mysqli_real_escape_string( $db_con, $_POST['forma_entrega'] );



$estado = mysqli_real_escape_string( $db_con, $_POST['estado'] );



$cidade = mysqli_real_escape_string( $db_con, $_POST['cidade'] );



$endereco_cep = mysqli_real_escape_string( $db_con, $_POST['endereco_cep'] );



$endereco_numero = mysqli_real_escape_string( $db_con, $_POST['endereco_numero'] );



$endereco_bairro = mysqli_real_escape_string( $db_con, $_POST['endereco_bairro'] );



$endereco_rua = mysqli_real_escape_string( $db_con, $_POST['endereco_rua'] );



$endereco_complemento = mysqli_real_escape_string( $db_con, $_POST['endereco_complemento'] );



$endereco_referencia = mysqli_real_escape_string( $db_con, $_POST['endereco_referencia'] );



$forma_pagamento = mysqli_real_escape_string( $db_con, $_POST['forma_pagamento'] );



$forma_pagamento_informacao = mysqli_real_escape_string( $db_con, $_POST['forma_pagamento_informacao'] );



$cupom = mysqli_real_escape_string( $db_con, $_POST['cupom'] );



$mesa = mysqli_real_escape_string( $db_con, $_POST['numero_mesa'] );







if( $token ) {







	if( $modo == "adicionar" ) {







		$query_content = mysqli_query( $db_con, "SELECT * FROM produtos WHERE id = '$pid' AND status = '1' ORDER BY id ASC LIMIT 1" );



		$has_content = mysqli_num_rows( $query_content );



		$data_content = mysqli_fetch_array( $query_content );



		



		if( $has_content ) {



		$eid = $data_content['rel_estabelecimentos_id'];



		$pid = $data_content['id'];



		



		



		



		$estoque = $data_content['estoque'];



		$posicao = $data_content['posicao'];



		



		



		if($estoque == 2 && $quantidade > $posicao ) { 



		



		?>



		    



		 



		



		<div class="row">







				<div class="col-md-12">







					<div class="adicionado">







						<i class="checkicon lni lni-checkmark-circle"></i>



						<span class="title">Erro no Pedido</span>



						<span class="text">No momento a quantidade selecionada para este produto é maior do que temos em estoque.<br/>Por favor selecione uma quantidade menor.</span>







					</div>







			  	</div>







		</div>



		



		<div class="row lowpadd">







				<div class="col-md-12">



					    



				<a href="#"  data-dismiss="modal" class="botao-acao botao-acao-gray"><i class="icone icone-sacola"></i> <span>Continuar</span></a>



				</div>







		</div>



		



		



		



		<?php  



		exit;



		} else { 







		sacola_adicionar( $eid,$pid,$quantidade,$observacoes,$variacoes );



		



		}



		



		?>







			<div class="row">







				<div class="col-md-12">







					<div class="adicionado">







						<i class="checkicon lni lni-checkmark-circle"></i>



						<span class="title"><?php echo htmlclean( $data_content['nome'] ); ?></span>



						<span class="text">Adicionado a sacola com sucesso!</span>







					</div>







			  	</div>







			</div>







			<div class="row lowpadd">







				<div class="col-md-6">



					    <a href="<?php echo $app['url']; ?>/categoria" class="botao-acao botao-acao-gray"><i class="icone icone-sacola"></i> <span>Continuar comprando</span></a>



				</div>







				<div class="col-md-6">



					    <a href="<?php echo $app['url']; ?>/sacola" class="botao-acao"><i class="lni lni-checkmark-circle"></i> <span>Ver sacola</span></a>



				</div>







			</div>







		<?php } else { ?>







			<div class="row">







				<div class="col-md-12">







					<div class="adicionado">







						<i class="erroricon lni lni-close"></i>



						<span class="title">Erro!</span>



						<span class="text">Solicitação inválida!</span>







					</div>







			  	</div>







			</div>







			<div class="row lowpadd">







				<div class="col-md-12">



					    <a href="#"  data-dismiss="modal" class="botao-acao botao-acao-gray"><i class="icone icone-sacola"></i> <span>Fechar</span></a>



				</div>







			</div>







		<?php







		}







	} 







	if( $modo == "alterar" ) {







		sacola_alterar( $eid,$pid,$quantidade );



		echo "eid: ".$eid."\n";



		echo "pid: ".$pid."\n";



		echo "quantidade: ".$quantidade."\n";







	} 







    if( $modo == "remover" ) {
	
	// Echo simples para confirmar que chegou aqui
	echo "REMOCAO_INICIADA";
	
	error_log("[SACOLA REMOVE] ===== INICIANDO REMOÇÃO =====");
	error_log("[SACOLA REMOVE] EID: $eid, PID: $pid, MODO: $modo");
	error_log("[SACOLA REMOVE] Session ID atual: " . session_id());
	error_log("[SACOLA REMOVE] Token recebido: " . $token);

	// Verificar se a sessão tem a sacola
	if (!isset($_SESSION['sacola'])) {
		error_log("[SACOLA REMOVE] ❌ ERRO: Sessão não contém sacola!");
		echo " - ERRO_SESSAO_SEM_SACOLA";
		exit;
	}
	
	if (!isset($_SESSION['sacola'][$eid])) {
		error_log("[SACOLA REMOVE] ❌ ERRO: Estabelecimento $eid não encontrado na sacola!");
		echo " - ERRO_ESTABELECIMENTO_NAO_ENCONTRADO";
		exit;
	}
	
	if (!isset($_SESSION['sacola'][$eid][$pid])) {
		error_log("[SACOLA REMOVE] ❌ ERRO: Produto $pid não encontrado na sacola do estabelecimento $eid!");
		echo " - ERRO_PRODUTO_NAO_ENCONTRADO";
		exit;
	}

	// Obter informações do item na sacola antes de remover
	$item_sacola = $_SESSION['sacola'][$eid][$pid];
	error_log("[SACOLA REMOVE] ✅ Item encontrado na sacola: " . json_encode($item_sacola));
	
	$quantidade_removida = $item_sacola['quantidade'] ?? 1;
	$variacoes_sacola = $item_sacola['variacoes'] ?? [];
	
	// Log para debug
	error_log("[SACOLA REMOVE] Dados extraídos - Produto: $pid, Quantidade: $quantidade_removida, Variações: " . json_encode($variacoes_sacola));

	// Devolver estoque do produto principal
	error_log("[SACOLA REMOVE] Iniciando devolução do estoque principal...");
	$query_qntpro = mysqli_query( $db_con, "SELECT estoque,posicao FROM produtos WHERE id = '$pid'");
	
	if (!$query_qntpro) {
		error_log("[SACOLA REMOVE] ❌ ERRO na query do produto: " . mysqli_error($db_con));
	} else {
		$data_contentp = mysqli_fetch_array( $query_qntpro );
		$posicaop = $data_contentp['posicao'];
		$controlaestoque = $data_contentp['estoque'];
		
		error_log("[SACOLA REMOVE] Produto - Estoque atual: $posicaop, Controla estoque: $controlaestoque");
		
		if($controlaestoque == 2 ) {
			$novoestoque = $posicaop + $quantidade_removida;
			$query_atualizapro = mysqli_query( $db_con, "UPDATE produtos SET posicao = '$novoestoque' WHERE id = '$pid'");
			
			if ($query_atualizapro) {
				error_log("[SACOLA REMOVE] ✅ Estoque produto principal devolvido: $posicaop -> $novoestoque (+$quantidade_removida)");
			} else {
				error_log("[SACOLA REMOVE] ❌ ERRO ao atualizar estoque principal: " . mysqli_error($db_con));
			}
		} else {
			error_log("[SACOLA REMOVE] ℹ️ Produto não controla estoque (estoque=$controlaestoque)");
		}
	}

	// Devolver estoque das variantes (se existirem)
	if (!empty($variacoes_sacola)) {
		error_log("[SACOLA REMOVE] Iniciando devolução do estoque das variantes...");
		
		// Obter variações do produto do banco
		$query_var = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = '$pid'");
		if (!$query_var) {
			error_log("[SACOLA REMOVE] ❌ ERRO na query das variações: " . mysqli_error($db_con));
		} else if (mysqli_num_rows($query_var)) {
			$row_var = mysqli_fetch_array($query_var);
			$variacoes_produto = json_decode($row_var['variacao'], true);
			
			if (is_array($variacoes_produto)) {
				error_log("[SACOLA REMOVE] Variações do produto carregadas: " . json_encode($variacoes_produto));
				
				// Processar cada variação selecionada na sacola
				foreach ($variacoes_sacola as $grupo_index => $variacao_selecionada) {
					error_log("[SACOLA REMOVE] Processando variação - Grupo: $grupo_index, Seleção: $variacao_selecionada");
					
					// Verificar se o grupo existe
					if (!isset($variacoes_produto[$grupo_index])) {
						error_log("[SACOLA REMOVE] ⚠️ Grupo $grupo_index não encontrado no produto");
						continue;
					}
					
					// Verificar se tem itens no grupo
					if (!isset($variacoes_produto[$grupo_index]['item'])) {
						error_log("[SACOLA REMOVE] ⚠️ Grupo $grupo_index não tem itens");
						continue;
					}
					
					// Encontrar e atualizar a variação específica
					foreach ($variacoes_produto[$grupo_index]['item'] as $item_key => &$item_data) {
						if ((string)$item_key === (string)$variacao_selecionada) {
							// Verificar se quantidade existe
							if (!isset($item_data['quantidade']) || empty($item_data['quantidade'])) {
								$item_data['quantidade'] = base64_encode("10");
								error_log("[SACOLA REMOVE] ⚠️ Quantidade não definida, definindo padrão: 10");
							}
							
							// Decodificar quantidade atual
							$qtd_atual = intval(base64_decode($item_data['quantidade']));
							$qtd_nova = $qtd_atual + $quantidade_removida;
							
							// Salvar nova quantidade
							$item_data['quantidade'] = base64_encode((string)$qtd_nova);
							
							error_log("[SACOLA REMOVE] ✅ Variação devolvida - Grupo: $grupo_index, Item: $item_key, $qtd_atual -> $qtd_nova (+$quantidade_removida)");
							break;
						}
					}
				}
				
				// Salvar as alterações no banco
				$novo_json = mysqli_real_escape_string($db_con, json_encode($variacoes_produto));
				$update_var = mysqli_query($db_con, "UPDATE produtos SET variacao = '$novo_json' WHERE id = '$pid'");
				
				if ($update_var) {
					error_log("[SACOLA REMOVE] ✅ Variações atualizadas com sucesso no banco");
				} else {
					error_log("[SACOLA REMOVE] ❌ ERRO ao atualizar variações no banco: " . mysqli_error($db_con));
				}
			} else {
				error_log("[SACOLA REMOVE] ❌ ERRO: Variações do produto não são um array válido");
			}
		} else {
			error_log("[SACOLA REMOVE] ⚠️ Produto não tem variações no banco");
		}
	} else {
		error_log("[SACOLA REMOVE] ℹ️ Item não tem variações para devolver");
	}

	// Remover da sacola
	error_log("[SACOLA REMOVE] Removendo item da sacola...");
	sacola_remover( $eid,$pid);
	error_log("[SACOLA REMOVE] ✅ Item removido da sacola");
	
	// Echo para confirmar que terminou
	echo " - REMOCAO_CONCLUIDA";
	error_log("[SACOLA REMOVE] ===== REMOÇÃO FINALIZADA =====");

	}









	





	







	if( $modo == "contagem" ) {







		$contagem = count( $_SESSION['sacola'][$eid] );



		echo $contagem;







	}







	if( $modo == "subtotal" ) {







		$subtotal = array();







		foreach( $_SESSION['sacola'][$eid] AS $key => $value ) {



			$query_content = mysqli_query( $db_con, "SELECT * FROM produtos WHERE id = '$key' AND status = '1' ORDER BY id ASC LIMIT 1" );



			$data_content = mysqli_fetch_array( $query_content );



			if( $data_content['oferta'] == "1" ) {



				$valor_final = $data_content['valor_promocional'];



			} else {



				$valor_final = $data_content['valor'];



			}



			$valor_adicional = $_SESSION['sacola'][$eid][$key]['valor_adicional'];



			$subtotal[] = ( ( $valor_final + $valor_adicional ) * $_SESSION['sacola'][$eid][$key]['quantidade'] );



		}







		$subtotal = array_sum( $subtotal );



		echo "R$ ".dinheiro( $subtotal, "BR");







	}







	if( $modo == "subtotal_clean" ) {







		$subtotal = array();







		foreach( $_SESSION['sacola'][$eid] AS $key => $value ) {



			$query_content = mysqli_query( $db_con, "SELECT * FROM produtos WHERE id = '$key' AND status = '1' ORDER BY id ASC LIMIT 1" );



			$data_content = mysqli_fetch_array( $query_content );



			if( $data_content['oferta'] == "1" ) {



				$valor_final = $data_content['valor_promocional'];



			} else {



				$valor_final = $data_content['valor'];



			}



			$subtotal[] = ( ( $valor_final + $valor_adicional ) * $_SESSION['sacola'][$eid][$key]['quantidade'] );



		}







		$subtotal = array_sum( $subtotal );



		echo $subtotal;







	}







	if ($modo == "checkout") {

		// Debug: Verifique o valor recebido

		error_log("Dados recebidos no POST: " . print_r($_POST, true));

		

		// Verifique especificamente o frete

		if (isset($_POST['frete_correios'])) {

			error_log("Frete recebido: " . $_POST['frete_correios']);

		} else {

			error_log("Nenhum frete recebido no POST");

		}

		

		checkout_salvar(

			$_POST['nome'],$_POST['cpf'],$_POST['email'], $_POST['whatsapp'], $_POST['forma_entrega'],

			$_POST['estado'], $_POST['cidade'], $_POST['endereco_cep'], 

			$_POST['endereco_numero'], $_POST['endereco_bairro'], 

			$_POST['endereco_rua'], $_POST['endereco_complemento'], 

			$_POST['endereco_referencia'], $_POST['forma_pagamento'], 

			$_POST['forma_pagamento_informacao'], $_POST['cupom'], 

			$mesa, $_POST['frete_correios'] ?? null

		);

		

		// Debug: Verifique a sessão após salvar

		error_log("Sessão completa: " . print_r($_SESSION, true));

	}







	if( $modo == "comprovante" ) {







		echo gera_comprovante($eid,"html","2","");







	}







}







?>