<?php
// CORE
include('../../_core/_includes/config.php');
// RESTRICT
restrict_estabelecimento();
restrict_expirado();
// SEO
$seo_subtitle = "Produtos";
$seo_description = "";
$seo_keywords = "";
// HEADER
$system_header .= "";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');
?>

<?php

global $db_con;
$eid = $_SESSION['estabelecimento']['id'];
    
    $user_id_insta = data_info( "estabelecimentos",$eid, "user_id_insta" );
    $token_insta = data_info( "estabelecimentos",$eid, "token_insta" );
    

$subdominio = $_SESSION['estabelecimento']['subdominio'];

// Variables

$estabelecimento = mysqli_real_escape_string( $db_con, $_GET['estabelecimento_id'] );
$categoria = mysqli_real_escape_string( $db_con, $_GET['categoria'] );
$nome = mysqli_real_escape_string( $db_con, $_GET['nome'] );
$visible = mysqli_real_escape_string( $db_con, $_GET['visible'] );
$status = mysqli_real_escape_string( $db_con, $_GET['status'] );
$baixo_estoque = mysqli_real_escape_string( $db_con, $_GET['baixo_estoque'] );

$getdata = "";

foreach($_GET as $query_string_variable => $value) {
  if( $query_string_variable != "pagina" ) {
    $getdata .= "&$query_string_variable=".htmlclean($value);
  }
}

// Config

$limite = 12;
$pagina = $_GET["pagina"] == "" ? 1 : $_GET["pagina"];
$inicio = ($pagina * $limite) - $limite;

// Query

$query .= "SELECT * FROM produtos ";

$query .= "WHERE 1=1 ";

$query .= "AND rel_estabelecimentos_id = '$eid' ";

if( $categoria ) {
  $query .= "AND rel_categorias_id = '$categoria' ";
}

if( $nome ) {
  $query .= "AND nome LIKE '%$nome%' ";
}

if( $visible ) {
  $query .= "AND visible = '$visible' ";
}

if( $status ) {
  $query .= "AND status = '$status' ";
}

if( $baixo_estoque == 1 ) {
  $query .= "AND posicao <= baixo_estoque AND estoque != '1' ";
}

$query_full = $query;

$query .= "ORDER BY last_modified DESC LIMIT $inicio,$limite";

// Run

$sql = mysqli_query( $db_con, $query );

$total_results = mysqli_num_rows( $sql );

$sql_full = mysqli_query( $db_con, $query_full );

$total_results_full = mysqli_num_rows( $sql_full );

$total_paginas = Ceil($total_results_full / $limite) + ($limite / $limite);

if( !$pagina OR $pagina > $total_paginas OR !is_numeric($pagina) ) {

    $pagina = 1;

}

?>

<?php if( $_GET['msg'] == "erro" ) { ?>

<?php modal_alerta("Erro, tente novamente!","erro"); ?>

<?php } ?>

<?php if( $_GET['msg'] == "sucesso" ) { ?>

<?php modal_alerta("Ação efetuada com sucesso!","sucesso"); ?>

<?php } ?>







<div class="middle minfit bg-gray">

	<div class="container">
	    
<div class="row">
    <div class="lista-menus" style="display: flex !important; margin: auto !important; justify-content: center !important;">
        <!-- <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="lista-menus-menu lista-menus-nocounter">
                <a href="#" style="background: rgba(0,0,0,.03) !important;" class="bt" id="conectar" data-toggle="modal" data-target="#instagramModal">
                    <i class="lni lni-instagram"></i>
                    <span style="color: #333 !important;" id="txt_conectar">Conectar Instagram</span>
                    <div class="clear"></div>
                </a>
            </div>
        </div> -->
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="lista-menus-menu lista-menus-nocounter">
                <a href="#" style="background: rgba(0,0,0,.03) !important;" class="bt" id="conectar" data-toggle="modal" data-target="#mercadoModal">
                    <i class="fas fa-handshake"></i>
                    <span style="color: #333 !important;" id="txt_conectar">Adicionar do Mercado Livre</span>
                    <div class="clear"></div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para inserir Access Token e User ID -->
<!-- <div id="instagramModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Conectar Instagram</h4>
      </div>
      <div class="modal-body" style="padding: 20px 20px 40px 20px;">
        <form method="GET" action="">
          <div class="form-group">
            <label for="user_id_insta">User ID do Instagram:</label>
            <input type="text" value="<?php echo $user_id_insta; ?>" class="form-control" id="user_id_insta" name="user_id_insta" placeholder="Insira o User ID" required>
          </div>
          <div class="form-group">
            <label for="token_insta">Access Token:</label>
            <input type="text" value="<?php echo $token_insta; ?>" class="form-control" id="token_insta" name="token_insta" placeholder="Insira o Access Token" required>
          </div>
          <input type="hidden" name="msg" value="salvar_insta">
          <button type="submit" class="btn btn-success col-md-12">Salvar</button>
        </form>
      </div>
    </div>
  </div>
</div> -->



<!-- Modal para inserir Access Token e User ID -->
<div id="mercadoModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg" style=" width: 40%; ">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Adicionar Produto do Mercado Livre</h4>
      </div>
      <div class="modal-body" style="padding: 20px 20px 40px 20px;">
        <form method="POST" action="" onsubmit="return loadingswal();">
          <div class="row">

            <div class="col-md-12">
              <div class="form-field-default">
                  <div class="form-group">
                    <label for="link_mercado">Link do Produto no Mercado Livre:</label>
                    <input type="text" value="<?php echo $user_id_insta; ?>" class="form-control" id="link_mercado" name="link_mercado" placeholder="Link do Produto" required>
                  </div>
                  
               <label>Categoria:</label>
                  <div class="fake-select">
                    <i class="lni lni-chevron-down"></i>
                    <select id="categoria-mercado" name="categoria-mercado" required>
                      <option value="">Selecione uma Categoria</option>
                      <?php 
                      $quicksql = mysqli_query( $db_con, "SELECT * FROM categorias WHERE rel_estabelecimentos_id = '$eid' ORDER BY nome ASC LIMIT 999" );
                      while( $quickdata = mysqli_fetch_array( $quicksql ) ) {
                      ?>
                      <option <?php if( $_POST['categoria'] == $quickdata['id'] ) { echo "SELECTED"; }; ?> value="<?php echo $quickdata['nome']; ?>"><?php echo $quickdata['nome']; ?></option>
                      <?php } ?>
                    </select>
                    <div class="clear"></div>
                  </div>
                  
                  
                  
                   
                    <div class="row" style=" margin: 25px 0 0; ">

            <div class="col-md-12">

              <div class="panel-group panel-filters panel-variacoes">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title">
                      <a data-toggle="collapse" href="#collapse-variacao">
                        <span class="desc">Variações</span>
                        <i class="lni lni-funnel"></i>
                        <div class="clear"></div>
                      </a>
                    </h4>
                  </div>
                  <div id="collapse-variacao" class="panel-collapse collapse <?php if( $_SESSION['estabelecimento']['funcionalidade_variacao'] == "1" ) { echo "in"; } ?>">
                    <div class="panel-body">

                                           <?php if( $_SESSION['estabelecimento']['funcionalidade_variacao'] == "1" ) { ?>
                      
                        <!-- Variações -->

                        <div class="variacoes">
                          <div class="row">
                            <div class="col-md-12">
                              <div class="render-variacoes"></div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="adicionar adicionar-variacao">
                                <i class="lni lni-plus"></i>
                                <span>Adicionar variação</span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- / Variações -->

                      <?php } else { ?>

                        <div class="expiration-info variacao-hire">
                          <div class="row">
                            <div class="col-md-9">
                              <span class="msg">O seu plano não possuí suporte para variações de produto, visite a nossa seção de planos e adquira um com a funcionalidade.</span>
                            </div>
                            <div class="col-md-3">
                              <div class="add-new add-center text-center">
                                <a href="<?php panel_url(); ?>/plano/listar">
                                  <span>Ver planos</span>
                                  <i class="lni lni-plus"></i>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>

                      <?php } ?>

                    </div>
                  </div>
                </div>
              </div> 

            </div>

          </div>
                    
                        
                        
              </div>
              
                    
            </div>
          </div>
          <input type="hidden" name="msg" value="salvar_mercado">
          <button type="submit" class="btn btn-success col-md-12">Salvar</button>
        </form>
      </div>
    </div>
  </div>
</div>


        
        

		<div class="row">

			<div class="col-md-12">

				<div class="title-icon pull-left">
					<i class="lni lni-shopping-basket"></i>
					<span>Produtos</span>
				</div>

				<div class="bread-box pull-right">
					<div class="bread">
						<a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
						<span>/</span>
						<a href="<?php panel_url(); ?>/produtos">Produtos</a>
					</div>
				</div>

			</div>

		</div>

		<!-- Filters -->

		<div class="row">

			<div class="col-md-12">

				<div class="panel-group panel-filters">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#collapse-filtros">
									<span class="desc">Filtrar</span>
									<i class="lni lni-funnel"></i>
									<div class="clear"></div>
								</a>
							</h4>
						</div>
						<div id="collapse-filtros" class="panel-collapse collapse <?php if( $_GET['filtered'] ) { echo 'in'; }; ?>">
							<div class="panel-body">

								<form class="form-filters form-100" method="GET">

									<div class="row">
										<div class="col-md-4">
											<div class="form-field-default">
												<label>Nome:</label>
												<input type="text" name="nome" placeholder="Nome" value="<?php echo htmlclean( $nome ); ?>"/>
											</div>
										</div>
										<div class="col-md-2">
							              <div class="form-field-default">
							               <label>Categoria:</label>
					                        <div class="fake-select">
					                          <i class="lni lni-chevron-down"></i>
					                          <select id="input-categoria" name="categoria">

							                      <option value=""></option>
							                      <?php 
							                      $quicksql = mysqli_query( $db_con, "SELECT * FROM categorias WHERE rel_estabelecimentos_id = '$eid' ORDER BY nome ASC" );
							                      while( $quickdata = mysqli_fetch_array( $quicksql ) ) {
							                      ?>

							                      <option <?php if( $_GET['categoria'] == $quickdata['id'] ) { echo "SELECTED"; }; ?> value="<?php echo $quickdata['id']; ?>"><?php echo $quickdata['nome']; ?></option>

							                      <?php } ?>

					                          </select>
					                          <div class="clear"></div>
					                        </div>
							              </div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-6 half-left">
							              <div class="form-field-default">
							               <label>Visibilidade:</label>
											<div class="fake-select">
												<i class="lni lni-chevron-down"></i>
												<select name="visible">
													<option></option>
		                                            <?php for( $x = 0; $x < count( $numeric_data['status'] ); $x++ ) { ?>
		                                            <option value="<?php echo $numeric_data['status'][$x]['value']; ?>" <?php if( $_GET['visible'] == $numeric_data['status'][$x]['value'] ) { echo 'SELECTED'; }; ?>><?php echo $numeric_data['status'][$x]['name']; ?></option>
		                                            <?php } ?>
												</select>
												<div class="clear"></div>
											</div>
							              </div>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-6 half-right">
							              <div class="form-field-default">
							               <label>Status:</label>
											<div class="fake-select">
												<i class="lni lni-chevron-down"></i>
												<select name="status">
													<option></option>
		                                            <?php for( $x = 0; $x < count( $numeric_data['status'] ); $x++ ) { ?>
		                                            <option value="<?php echo $numeric_data['status'][$x]['value']; ?>" <?php if( $_GET['status'] == $numeric_data['status'][$x]['value'] ) { echo 'SELECTED'; }; ?>><?php echo $numeric_data['status'][$x]['name']; ?></option>
		                                            <?php } ?>
												</select>
												<div class="clear"></div>
											</div>
							              </div>
										</div>
										
										<div class="clear visible-xs visible-sm"></div>
									

            <div class="col-md-6 col-sm-6 col-xs-6 half-right">

              <div class="form-field-default">

                  <label>Somente baixo estoque?</label>
                  <div class="form-field-radio">
                    <input type="radio" name="baixo_estoque" value="1" element-show=".elemento-promocional" <?php if( $_POST['baixo_estoque'] == 1 ){ echo 'CHECKED'; }; ?>> Sim
                  </div>
                  <div class="form-field-radio">
                    <input type="radio" name="baixo_estoque" value="2" element-hide=".elemento-promocional" <?php if( $_POST['baixo_estoque'] == 2 OR !$_POST['oferta']  ){ echo 'CHECKED'; }; ?>> Não
                  </div>
                  <div class="clear"></div>

              </div>

            </div>

      
										<div class="clear visible-xs visible-sm"></div>
										<div class="col-md-2">
											<div class="form-field-default">
												<label class="hidden-xs hidden-sm"></label>
												<input type="hidden" name="filtered" value="1"/>
												<button>
													<span>Buscar</span>
													<i class="lni lni-search-alt"></i>
												</button>
											</div>
										</div>
									</div>
									<?php if( $_GET['filtered'] ) { ?>
									<div class="row">
										<div class="col-md-12">
										    <a href="<?php panel_url(); ?>/produtos" class="limpafiltros"><i class="lni lni-close"></i> Limpar filtros</a>
										</div>
									</div>
									<?php } ?>
								</form>

							</div>
						</div>
					</div>
				</div> 

			</div>

		</div>

		<!-- / Filters -->

		<!-- Content -->

		<div class="listing">

			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-6">
					<span class="listing-title"><strong class="counter"><?php echo $total_results_full; ?></strong> Registros:</span>
				</div>
				<div class="col-md-6 col-sm-6 col-xs-6">
					<div class="add-new pull-right">

						<a href="<?php panel_url(); ?>/produtos/adicionar">
							<span>Adicionar</span>
							<i class="lni lni-plus"></i>
						</a>

					</div>
				</div>
			</div>
			
			
			
			
			
			
			
			
			
			
			
			
			
					<!--<table class="listing-table fake-table clean-table">-->
					<!--	<thead>-->
					<!--		<th></th>-->
					<!--		<th>Estoque</th>-->
					<!--		<th>Nome</th>-->
					<!--		<th>Categoria</th>-->
					<!--		<th>Visibilidade</th>-->
					<!--		<th>Status</th>-->
					<!--		<th></th>-->
					<!--	</thead>-->
					<!--	<tbody>-->

						<?php
                         //while ( $data = mysqli_fetch_array( $sql ) ) {
                          ?>

					<!--		<tr <?php // if($data['posicao'] <= $data['baixo_estoque'] && $data['estoque'] == '2' && $data['aviso_baixo_estoque'] == '1'){ echo 'style="background: #d6000038;"'; }?> >-->
					<!--			<td>-->
     <!--                               <div class="fake-table-data">-->
     <!--                               	<div class="rounded-thumb-holder">-->
	    <!--                                	<div class="rounded-thumb">-->
	    <!--                                		<img src="<?php echo thumber( $data['destaque'], "120" ); ?>"/>-->
	    <!--                                		<img class="blurred" src="<?php echo thumber( $data['destaque'], "120" ); ?>"/>-->
	    <!--                                	</div>-->
     <!--                               	</div>-->
     <!--                               </div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--			<td>-->
					<!--				<span class="fake-table-title hidden-xs hidden-sm">Estoque</span>-->
					<!--				<div class="fake-table-data"><?php echo $data['posicao'] == "0" ? "Não controla estoque" : htmlclean( $data['posicao'] ); ?></div>-->
					<!--				<div class="fake-table-break"></div>-->
					<!--			</td> -->
					<!--			<td>-->
     <!--                               <span class="fake-table-title hidden-xs hidden-sm">Nome</span>-->
     <!--                               <div class="fake-table-data"><?php echo htmlclean( $data['nome'] ); ?></div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--			<td class="hidden-xs hidden-sm">-->
     <!--                               <span class="fake-table-title">Categoria</span>-->
     <!--                               <div class="fake-table-data"><?php echo data_info( "categorias",$data['rel_categorias_id'], "nome" ); ?></div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--			<td class="hidden-xs hidden-sm">-->
     <!--                               <span class="fake-table-title">Visiblidade</span>-->
     <!--                               <div class="fake-table-data"><?php echo numeric_data( "status", $data['visible'] ); ?></div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--			<td class="hidden-xs hidden-sm">-->
     <!--                               <span class="fake-table-title">Status</span>-->
     <!--                               <div class="fake-table-data"><?php echo numeric_data( "status", $data['status'] ); ?></div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--			<td>-->
					<!--				<span class="fake-table-title hidden-xs hidden-sm">Ações</span>-->
     <!--                               <div class="fake-table-data">-->
					<!--					<div class="form-actions pull-right">-->
										    
					<!--					    <?php if($data['status'] == 1 ) { ?>-->
					<!--					    <a class="color-red" href="<?php panel_url(); ?>/produtos/desativar/?id=<?php echo $data['id']; ?>" title="Desativar"><i class="lni lni-cross-circle"></i></a>-->
					<!--					    <?php } ?>-->
										    
					<!--					    <?php if($data['status'] == 2 ) { ?>-->
					<!--					    <a class="color-green" href="<?php panel_url(); ?>/produtos/ativar/?id=<?php echo $data['id']; ?>" title="Ativar"><i class="lni lni-checkmark-circle"></i></a>-->
					<!--					    <?php } ?>-->
										    
										    
										    
					<!--						<a class="color-white" href="<?php panel_url(); ?>/produtos/copiar?id=<?php echo $data['id']; ?>" title="Copiar"><i class="lni lni-files"></i></a>-->
					<!--						<a class="color-yellow" href="<?php panel_url(); ?>/produtos/editar?id=<?php echo $data['id']; ?>" title="Editar"><i class="lni lni-pencil"></i></a>-->
					<!--						<a class="color-red" onclick="if(confirm('Tem certeza que deseja remover este produto?')) document.location = '<?php panel_url(); ?>/produtos/deletar/?id=<?php echo $data['id']; ?>'" href="#" title="Excluir"><i class="lni lni-trash"></i></a>-->
					<!--						<a class="color-green" href="<?php panel_url(); ?>/produtos/?id=<?php echo $data['id']; ?>&msg=promover" title="Promover"><i class="lni lni-bullhorn"></i></a>-->
					<!--					</div>-->
     <!--                               </div>-->
     <!--                               <div class="fake-table-break"></div>-->
					<!--			</td>-->
					<!--		</tr>-->
			
			
			
			
			
			
			

			<div class="row">

				<div class="col-md-12">

			<table class="listing-table fake-table clean-table">
				<thead>
                    <th><input type="checkbox" id="select_all" /></th> <!-- Checkbox para selecionar todos -->
                    		<th>Estoque</th>
							<th>Imagem</th>
							<th>Nome</th>
							<th>Categoria</th>
							<th>Visibilidade</th>
							<th>Status</th>
                    <th>
                        <div class="form-actions">
                            <button type="button" id="delete_selected" class="btn btn-danger">Excluir Selecionados</button>
                        </div>
                    </th>
                    <th>
                    </th>
                </thead>
                <tbody>
                    <?php
                    while ($data = mysqli_fetch_array($sql)) {
                    ?>
                    <tr <?php if($data['posicao'] <= $data['baixo_estoque'] && $data['estoque'] == '2' && $data['aviso_baixo_estoque'] == '1'){ echo 'style="background: #d6000038;"'; }?> >
                        <td><input type="checkbox" class="product_checkbox" value="<?php echo $data['id']; ?>" /></td> <!-- Checkbox para cada produto -->
                        		<td>
									<span class="fake-table-title hidden-xs hidden-sm">Estoque</span>
									<div class="fake-table-data"><?php echo $data['posicao'] == "0" ? "Não controla estoque" : htmlclean( $data['posicao'] ); ?></div>
									<div class="fake-table-break"></div>
								</td> 
                        
                        	<td>
                                    <div class="fake-table-data">
                                    	<div class="rounded-thumb-holder">
	                                    	<div class="rounded-thumb">
	                                    		<img src="<?php echo thumber( $data['destaque'], "120" ); ?>"/>
	                                    		<img class="blurred" src="<?php echo thumber( $data['destaque'], "120" ); ?>"/>
	                                    	</div>
                                    	</div>
                                    </div>
                                    <div class="fake-table-break"></div>
								</td>
								<td>
                                    <span class="fake-table-title hidden-xs hidden-sm">Nome</span>
                                    <div class="fake-table-data"><?php echo htmlclean( $data['nome'] ); ?></div>
                                    <div class="fake-table-break"></div>
								</td>
								<td class="hidden-xs hidden-sm">
                                    <span class="fake-table-title">Categoria</span>
                                    <div class="fake-table-data"><?php echo data_info( "categorias",$data['rel_categorias_id'], "nome" ); ?></div>
                                    <div class="fake-table-break"></div>
								</td>
								<td class="hidden-xs hidden-sm">
                                    <span class="fake-table-title">Visiblidade</span>
                                    <div class="fake-table-data"><?php echo numeric_data( "status", $data['visible'] ); ?></div>
                                    <div class="fake-table-break"></div>
								</td>
								
								<td class="hidden-xs hidden-sm">
                                    <span class="fake-table-title">Status</span>
                                    <div class="fake-table-data"><?php echo numeric_data( "status", $data['status'] ); ?></div>
                                    <div class="fake-table-break"></div>
								</td>
                        <td>
                           					<span class="fake-table-title hidden-xs hidden-sm">Ações</span>
                                    <div class="fake-table-data">
										<div class="form-actions pull-right">
										    
										    <?php if($data['status'] == 1 ) { ?>
										    <a class="color-red" href="<?php panel_url(); ?>/produtos/desativar/?id=<?php echo $data['id']; ?>" title="Desativar"><i class="lni lni-cross-circle"></i></a>
										    <?php } ?>
										    
										    <?php if($data['status'] == 2 ) { ?>
										    <a class="color-green" href="<?php panel_url(); ?>/produtos/ativar/?id=<?php echo $data['id']; ?>" title="Ativar"><i class="lni lni-checkmark-circle"></i></a>
										    <?php } ?>
										    
										    
										    
											<a class="color-white" href="<?php panel_url(); ?>/produtos/copiar?id=<?php echo $data['id']; ?>" title="Copiar"><i class="lni lni-files"></i></a>
											<a class="color-yellow" href="<?php panel_url(); ?>/produtos/editar?id=<?php echo $data['id']; ?>" title="Editar"><i class="lni lni-pencil"></i></a>
											<a class="color-red" onclick="if(confirm('Tem certeza que deseja remover este produto?')) document.location = '<?php panel_url(); ?>/produtos/deletar/?id=<?php echo $data['id']; ?>'" href="#" title="Excluir"><i class="lni lni-trash"></i></a>
											<a class="color-green" href="<?php panel_url(); ?>/produtos/?id=<?php echo $data['id']; ?>&msg=promover" title="Promover"><i class="lni lni-bullhorn"></i></a>
										</div>
                                    </div>
                        </td>
                    </tr>

                            <?php } ?>

                            <?php if( $total_results == 0 ) { ?>

                               <tr class="fullwidth">
                                <td colspan="6">
                                  <div class="fake-table-data">
                                    <span class="nulled">Nenhum registro cadastrado ou compatível com a sua filtragem!</span>
                                  </div>
                                  <div class="fake-table-break"></div>
                                </td>
                               </tr>

                            <?php } ?>

						</tbody>
					</table>

				</div>

			</div>

		</div>

		<!-- / Content -->

		<!-- Pagination -->

        <div class="paginacao">

          <ul class="pagination">

            <?php
            $paginationpath = "produtos";
            if($pagina > 1) {
              $back = $pagina-1;
              echo '<li class="page-item pagination-back"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$back.$getdata.' "><i class="lni lni-chevron-left"></i></a></li>';
            }
     
              for($i=$pagina-1; $i <= $pagina-1; $i++) {

                  if($i > 0) {
                  
                      echo '<li class="page-item pages-before"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.' ">'.$i.'</a></li>';
                  }

              }

              if( $pagina >= 1 ) {

                echo '<li class="page-item active"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.'" class="page-link">'.$i.'</a></li>';

              }

              for($i=$pagina+1; $i <= $pagina+1; $i++) {

                  if($i >= $total_paginas) {
                    break;
                  }  else {
                      echo '<li class="page-item pages-after"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.' ">'.$i.'</a></li> ';
                  }
              
              }

            if($pagina < $total_paginas-1) {
              $next = $pagina+1;
              echo '<li class="page-item pagination-next"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$next.$getdata.' "><i class="lni lni-chevron-right"></i></a></li>';
            }

            ?>

          </ul>

        </div>

		<!-- / Pagination -->

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>

<script>

$( document ).ready(function() {

	$( "input[name=estabelecimento]" ).change(function() {
		$( "input[name=estabelecimento_id]" ).trigger("change");
	});

	$( "input[name=estabelecimento_id]" ).change(function() {
	    var estabelecimento = $(this).val();
	    $("#input-categoria").html("<option>-- Carregando categorias --</option>");
	    $("#input-categoria").load("<?php just_url(); ?>/_core/_ajax/categorias.php?estabelecimento="+estabelecimento);
	});

});


</script>


<?php if( $_GET['msg'] == "promover" ) { 

    $id = $_GET['id'];
	$meudominio = $httprotocol.data_info("estabelecimentos",$_SESSION['estabelecimento']['id'],"subdominio").".".$simple_url."/produto/".$id;
	

    $link_img = $httprotocol.data_info("estabelecimentos",$_SESSION['estabelecimento']['id'],"subdominio").".".$simple_url."/_core/_uploads".data_info( "produtos",$id, "destaque" );
    
    
    echo '<script>';
    echo '$( document ).ready(function() {';
    echo '    $("#modalpromove").modal("show");';
    echo '    $("#whatsappButton").on("click", function() {';
    echo '        var userMessage = $("#userMessage").val();';
    echo '        window.open("https://api.whatsapp.com/send?text=" + encodeURIComponent(userMessage), "_blank");';
    echo '    });';
    echo '});';
    echo '</script>';
	
} ?>



<?php
if ($_POST['msg'] == "salvar_mercado") {
    
    
    
    $url = $_POST['link_mercado'];
    $categoriam = $_POST['categoria-mercado'];
    
    $aid = $_SESSION['user']['id'];
    $email =  data_info("users", $aid, "email");
    $senha =  data_info("users", $aid, "password");       
    
    // Certifique-se de que o ID do estabelecimento está definido
    $eid = $_SESSION['estabelecimento']['id'];
    
    
    $subd =  data_info("estabelecimentos", $eid, "subdominio");  
    $validezurl = (strpos($url, 'https://www.mercadolivre.com.br/') !== 0 && strpos($url, 'https://produto.mercadolivre.com.br/') !== 0);
    
        if ($validezurl) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'URL inválida',
                    text: 'O link do produto deve começar com https://www.mercadolivre.com.br/'
                });
            </script>";
            exit; // Impede que o restante do código seja executado
        }
  
  
   $variacao =  $_POST['variacao'];
    for ( $x=0; $x < count( $variacao ); $x++ ){
      $variacao[$x]['nome'] = jsonsave( $variacao[$x]['nome'] );
      $variacao[$x]['escolha_minima'] = jsonsave( $variacao[$x]['escolha_minima'] );
      $variacao[$x]['escolha_maxima'] = jsonsave( $variacao[$x]['escolha_maxima'] );
      for( $y=0; $y < count( $variacao[$x]['item'] ); $y++ ){
        $variacao[$x]['item'][$y]['nome'] =  jsonsave( $variacao[$x]['item'][$y]['nome'] );
        $variacao[$x]['item'][$y]['descricao'] =  jsonsave( $variacao[$x]['item'][$y]['descricao'] );
        $variacao[$x]['item'][$y]['valor'] = jsonsave( dinheiro( mysqli_real_escape_string( $db_con,$variacao[$x]['item'][$y]['valor'] ) ) );
        $variacao[$x]['item'][$y]['quantidade'] =  jsonsave($variacao[$x]['item'][$y]['quantidade']);
        if(is_numeric($variacao[$x]['item'][$y]['estoque'])) {
          $variacao[$x]['item'][$y]['estoque'] = jsonsave($variacao[$x]['item'][$y]['estoque']);
        } else {
            $variacao[$x]['item'][$y]['estoque'] = jsonsave(999);
        }
        $variacao[$x]['item'][$y]['baixo_estoque'] =  jsonsave($variacao[$x]['item'][$y]['baixo_estoque']);
      }
    }
    $variacao = json_encode( $variacao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

    
     // Defina a URL do seu Webhook
            $webhookUrl = 'https://n8n.prefornece.vip/webhook/mercadolivre';

            // Defina os dados a serem enviados ao webhook (em formato JSON)
            $data = array(
                    'url' => $url, 
                    'subdominio' => $subdominio,
                    'categoria' => $categoriam,
                    'email' => $email,
                    'senha' => $senha,
                    'dominio' => $dominio,
                    'variacao' => $variacao
            );

            // Converta os dados para JSON
            $jsonData = json_encode($data);

            // Inicia a requisição cURL
            $ch = curl_init();

            // Defina a URL do Webhook
            curl_setopt($ch, CURLOPT_URL, $webhookUrl);

            // Defina o método para POST
            curl_setopt($ch, CURLOPT_POST, true);

            // Anexe o JSON ao corpo da requisição
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            // Defina as opções para retornar a resposta como string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Defina os cabeçalhos necessários
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'apikey: mf93nmf01ikfd9aplKd892HG238f'
            ));
            
            // vzwmbxadf3ntufgedal76f3argnhtmwctffgdykovapn

            // Execute a requisição e capture a resposta
            $response = curl_exec($ch);

            // Verifica se houve erro na requisição
            if(curl_errno($ch)) {
                // Se ocorreu erro, redireciona para uma página de erro
                header("Location: index.php?msg=erro");
            } else {
                // Caso contrário, decodifica a resposta JSON do Webhook
                $decodedResponse = json_decode($response, true);
            
                // Verifique se a resposta foi decodificada corretamente
                if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE || $validezurl) {
                    // Se houver um erro na decodificação, pode exibir a resposta como string
                    // echo "Erro na resposta JSON: " . json_last_error_msg();
                    header("Location: index.php?msg=erro");
                } else{
                    header("Location: index.php?msg=sucesso");
                }
            }

            // Feche a sessão cURL
            curl_close($ch);

}
?>





<div id="modalpromove" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">Mensagem para WhatsApp ou Descrição do Instagram.</h4>
      </div>
      <div class="modal-body">
        <textarea id="userMessage" rows="4" cols="50"><?php echo $meudominio; ?></textarea>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn btn-default" style="background-color: black; color: white;" data-dismiss="modal">Fechar</a>
        <a href="#" id="whatsappButton" class="btn btn-success">Enviar <i class="lni lni-whatsapp"></i></a>
        <!-- <a href="#" id="instagramButton" class="btn" style="background-color: #fb00bb; color: white;" 
           onclick="sendToInstagram('<?php echo $link_img; ?>')">
           Divulgar <i class="lni lni-instagram"></i>
        </a> -->
      </div>
    </div>
  </div>
</div>

<script>
function sendToInstagram(link_img) {
    var userMessage = document.getElementById('userMessage').value; // Captura a mensagem do usuário
    var encodedMessage = encodeURIComponent(userMessage); // Codifica a mensagem

    // Constrói a URL com os parâmetros
    var url = '<?php panel_url(); ?>/produtos/?link_img=' + encodeURIComponent(link_img) + '&msg=divulgar&userMessage=' + encodedMessage;
    
    window.location.href = url; // Redireciona para a URL
}










$(document).ready(function() {
    // Seleciona ou desmarca todos os checkboxes
    $('#delete_selected').hide;
    $('#select_all').change(function() {
        var isChecked = $(this).prop('checked');
        $('.product_checkbox').prop('checked', isChecked);
        $('#delete_selected').show;
    });

    // Excluir os produtos selecionados
    $('#delete_selected').click(function() {
        var selected = [];
        $('.product_checkbox:checked').each(function() {
            selected.push($(this).val());
        });

        // Se não houver nenhum produto selecionado
        if (selected.length == 0) {
            alert('Por favor, selecione ao menos um produto.');
            return;
        }

        // Confirmar antes de deletar
        if (confirm('Tem certeza que deseja remover os produtos selecionados?')) {
            // Redirecionar para a página de deletar com os IDs selecionados
            var url = '<?php echo panel_url(); ?>/produtos/deletar_selecionados?ids=' + selected.join(',');
            window.location.href = url;
        }
    });
});



</script>


<script>
function loadingswal() {
    
    
    $('#mercadoModal').hide;
    // Configuração do SweetAlert com o campo textarea e input
    Swal.fire({
        title: 'Aguarde...',
        text: 'Processando sua solicitação. Por favor, aguarde.',
        allowEscapeKey: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading(); // Mostra a animação de carregamento
        }
    });
     return true; // Permite o envio do formulário
} 
        
        
        
      
</script>



<script>

  function masks() {

    $(".maskdate").mask("99/99/9999",{placeholder:""});
    $(".maskrg").mask("99999999-99",{placeholder:""});
    $(".maskcpf").mask("999.999.999-99",{placeholder:""});
    $(".maskcnpj").mask("99.999.999/9999-99",{placeholder:""});
    $(".maskcel").mask("(99) 99999-9999");
    $(".maskcep").mask("99999-999");
    $(".dater").mask("99/99/9999");
    $(".masktime").mask("99:99:99");
    $(".masknumber").mask("99");
    $(".maskmoney").maskMoney({
        prefix: "R$ ",
        decimal: ",",
        thousands: "."
    });

  }

  function reordena() {

    var variacao = 0;

    $( ".variacao" ).each(function() {
      
      $( this ).closest(".panel-subvariacao").find(".subvariacao-link").attr("href","#collapse-subvariacao-"+variacao);
      $( this ).closest(".panel-subvariacao").find(".subvariacao-body").attr("id","collapse-subvariacao-"+variacao);
      $( this ).closest(".panel-subvariacao").find(".adicionar-item").attr("variacao-id",variacao);
      $( this ).attr("variacao-id",variacao);
      $( this ).find(".col-item").attr("variacao-id",variacao);
      $( this ).find(".variacao-nome").attr("name","variacao["+variacao+"][nome]");
      $( this ).find(".variacao-escolha-minima").attr("name","variacao["+variacao+"][escolha_minima]");
      $( this ).find(".variacao-escolha-maxima").attr("name","variacao["+variacao+"][escolha_maxima]");
      variacao++;
    
      var item = 0;

      $( this ).find(".item").each(function() {

        var variacao_pai = $( this ).parent().attr('variacao-id');
        $( this ).attr("item-id",item);
        $( this ).find(".item-nome").attr("name","variacao["+variacao_pai+"][item]["+item+"][nome]");
        $( this ).find(".item-descricao").attr("name","variacao["+variacao_pai+"][item]["+item+"][descricao]");
        $( this ).find(".item-valor").attr("name","variacao["+variacao_pai+"][item]["+item+"][valor]");
        $(this).find(".item-quantidade").attr("name", "variacao[" + variacao_pai + "][item][" + item + "][quantidade]");
        $(this).find(".item-estoque").attr("name", "variacao[" + variacao_pai + "][item][" + item + "][estoque]");
        $(this).find(".item-baixo-estoque").attr("name", "variacao[" + variacao_pai + "][item][" + item + "][baixo_estoque]");
        item++;

      });

    });

  }
$(document).on('click', '.adicionar-variacao', function() {
    Swal.fire({
        title: 'Importar Itens?',
        text: 'Cole o código HTML da div com classe "andes-card__content" do Mercado Livre para adicionar os itens rapidamente. Ou clique em cancelar',
        input: 'textarea',
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancelar',
        inputPlaceholder: 'Cole o código HTML aqui...',
        showLoaderOnConfirm: true,
        preConfirm: (html) => {
            if (html && html.includes('andes-card__content')) {
                return $.ajax({
                    url: "<?php echo get_just_url(); ?>/_core/_ajax/variacoes.php?modo=html_items",
                    type: "POST",
                    data: { html: html },
                    dataType: "json"
                });
            }
            return false; // Retorna false se não houver HTML válido
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        // Primeiro, executa a função original de adicionar variação
        $.get("<?php echo get_just_url(); ?>/_core/_ajax/variacoes.php?modo=variacao", function(data){
            var render = data;
            $(".render-variacoes").append(render);
            $(" .panel-subvariacao ").fadeIn(400);
            
            // Se tiver dados válidos do Mercado Livre, adiciona os itens
            if (result.value && result.value.success) {
                $('.render-itens').find('.col-md-4:last-child').before(result.value.html);
                $(".col-item").fadeIn(400);
            }
            
            reordena();
            masks();
        });
    });
});


  $(document).on('click', '.adicionar-item', function() {

      var render;
      var parent = $( this ).closest(".variacao").attr("variacao-id");
      $.get("<?php echo get_just_url(); ?>/_core/_ajax/variacoes.php?modo=item", function(data){
          render = data;
          $('.variacao[variacao-id="'+parent+'"] .render-itens').find('.col-md-4:last-child').before(render);
          $(" .col-item ").fadeIn(400);
          reordena();
          masks();
      });

  });

  $(document).on('keyup', '.variacao-nome', function() {

    $( this ).closest(".panel-subvariacao").find(".variacao-desc").html( $( this ).val() );

  });

  $(document).on('click', '.deletar-variacao', function() {

    if( confirm("Tem certeza que deseja remover essa variação?") ) {
      $( this ).closest(".panel-subvariacao").remove();
      reordena();
    }

  });

  $(document).on('click', '.deletar-item', function() {

    if( confirm("Tem certeza que deseja remover esse item?") ) {
      $( this ).closest(".col-item").remove();
      reordena();
    }

  });

  $(document).on('keyup keydown', '.variacao-escolha-minima', function(o) {
    var re = /[^0-9\-]/;
    var strreplacer = $(this).val();
    strreplacer = strreplacer.replace(re, '');
    if( strreplacer == '' || strreplacer == '0' ) {
      strreplacer = '0';
    }
    $(this).val( strreplacer );
  });

  $(document).on('keyup keydown', '.variacao-escolha-maxima', function(o) {
    var re = /[^0-9\-]/;
    var strreplacer = $(this).val();
    strreplacer = strreplacer.replace(re, '');
    if( strreplacer == '' || strreplacer == '0' ) {
      strreplacer = '1';
    }
    $(this).val( strreplacer );
  });

</script>


