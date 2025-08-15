<?php
// CORE
include($virtualpath.'/_layout/define.php');
include('../../_core/_includes/config.php');


// APP
global $app;
is_active( $app['id'] );
global $seo_title;
// Querys
$busca = mysqli_real_escape_string( $db_con, $_GET['busca'] );
$categoria = mysqli_real_escape_string( $db_con, $_GET['categoria'] );
// SEO
$seo_subtitle = $app['title'];
$seo_description = $app['description_clean'];
$seo_keywords = $app['title'].", ".$seo_title;
$seo_image = thumber( $app['avatar_clean'], 400 );
// HEADER
$system_header .= "";
include($virtualpath.'/_layout/head.php');
include($virtualpath.'/_layout/top.php');
include($virtualpath.'/_layout/sidebars.php');
include($virtualpath.'/_layout/modal.php');
instantrender();

//URL
$pegaurlx =  "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

?>

<?php if( $_GET['msg'] == "acabou" ) { ?>

<?php modal_alerta("Produto Sem Estoque!","erro"); ?>

<?php } ?>

<style>
								    

    @media screen and (min-width: 80rem) {
        .grid_produtos {
            grid-template-columns: 1fr 1fr 1fr 1fr !important;
            gap: 25px !important;
        }
                                
    }
</style>
									</style>

<div class="sceneElement">

	<div class="container nopadd visible-xs visible-sm">

		<div class="cover" style="background: url(<?php echo $app['cover']; ?>) no-repeat top center;">
			<?php if( data_info( "estabelecimentos", $app['id'], "capa" ) ) { ?>
				<img src="<?php echo $app['cover']; ?>"/>
			<?php } ?>
		</div>

		<div class="grudado">
		
			<div class="avatar">
				<div class="holder">
					<a href="<?php echo $app['url']; ?>">
						<img src="<?php echo $app['avatar']; ?>" alt="<?php echo $app['title']; ?>" title="<?php echo $app['title']; ?>"/>
					</a>
				</div>	
			</div>

		</div>

		<div class="app-infos">
			<div class="row">
				<div class="col-md-12">
					<span class="title"><?php echo $app['title']; ?></span>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<span class="description"><?php echo $app['description']; ?></span>
				</div>
			</div>
			

			<div class="row">
				<div class="col-md-12">
					<div align="center">
						<span>
						<?php if ( verifica_horario($app['id']) == "disabled") {  ?>
						
						    <?php if ( data_info( "estabelecimentos", $app['id'], "funcionamento" ) == "1" ) { ?>
						        <button class="btn btn-success btn-sm" style="width: 200px !important; border-radius: 30px !important;"><i class="lni lni-restaurant" style="color:#FFFFFF"></i> Aberto para Pedidos</button>
						    <?php } else { ?>
						        <button class="btn btn-danger btn-sm" style="width: 200px !important; border-radius: 30px !important;"><i class="lni lni-cross-circle" style="color:#FFFFFF"></i> Fechado para Pedidos</button>
						    <?php } ?>
						    
					    <?php } else if ( verifica_horario($app['id']) == "open") { ?>
						        <button class="btn btn-success btn-sm" style="width: 200px !important; border-radius: 30px !important;"><i class="lni lni-restaurant" style="color:#FFFFFF"></i> Aberto para Pedidos</button>
						<?php } else if ( verifica_horario($app['id']) == "close") { ?>
						        <button class="btn btn-danger btn-sm" style="width: 200px !important; border-radius: 30px !important;"><i class="lni lni-cross-circle" style="color:#FFFFFF"></i> Fechado para Pedidos</button>
						<?php } ?>
						    
						</span>
						
					</div>


		
                    </span>
				</div>
			</div>
			
			 
			
		</div>

	</div>

	<div class="middle minfit">

		<div class="container">

			<div class="row visible-xs visible-sm">
				<div class="col-md-12">
					<div class="clearline"></div>
				</div>
			</div>

				<div class="row">

				<div class="col-md-12">
		
			 		<div class="search-bar-mobile visible-xs visible-sm">

						<form class="align-middle" action="<?php echo $app['url']; ?>/categoria" method="GET">

							<input type="text" name="busca" placeholder="Digite sua busca..." value="<?php echo htmlclean( $_GET['busca'] ); ?>"/>
							<input type="hidden" name="categoria" value="<?php echo $categoria; ?>"/>
							<button>
								<i class="lni lni-search-alt"></i>
							</button>
							<div class="clear"></div>

						</form>

					</div>

				</div>
				
				<div class="row">

						<div class="col-md-12">

							<div style="overflow-y: scroll; display: flex; margin-top: 30px;">
								<?php
								if ($busca) {
									$query_busca = "&busca=" . $busca;
								}
								if ($filtro) {
									$query_busca = "&filtro=" . $filtro;
								}
								?>
								<a class="<?php if (!$categoria) {
												echo 'active';
											}; ?>" href="<?php echo $app['url']; ?>/categoria?<?php echo $query_busca; ?>">Tudo</a>
								<?php
								$query_categorias = mysqli_query($db_con, "SELECT * FROM categorias WHERE rel_estabelecimentos_id = '$app_id' AND visible = '1' AND status = '1' ORDER BY ordem ASC");
								while ($data_categoria = mysqli_fetch_array($query_categorias)) {
								?>
									<a style="white-space: nowrap; display: grid; justify-content: center; align-items: center; text-align: center; margin-left: 20px; margin-right: 20px;" class="<?php if ($data_categoria['id'] == $categoria) {
													echo 'active';
												}; ?>" href="<?php echo $app['url']; ?>/categoria/<?php echo $data_categoria['id']; ?>
												<?php if ($query_busca) {
													echo "?" . $query_busca;
    											}; ?>">
        							    <span>
        							        <?php if (empty($data_categoria['icone'])) { ?>
        							            <i class="lni lni-list"></i>
        							        <?php } else { ?>
        							            <img style="width: 80px; height: 80px; border-radius: 100%;" src="../../../_core/_uploads/<?php echo $data_categoria['icone']; ?>" /> 
        							        <?php  } ?>
                                    	    
                                    	</span>
                                    	<span style="margin-top: 5px; color: #313638;">
									        <?php echo $data_categoria['nome']; ?>
                                    	</span>
									    
									  </a>
								<?php } ?>
							</div>

						</div>

					</div>
				
				
				

			</div>
			
				<!-- CELULAR -->

			<?php if ($app['funcionalidade_banners']) { ?>

				<?php
				$eid = $app['id'];
				$query_banners = mysqli_query($db_con, "SELECT * FROM banners WHERE rel_estabelecimentos_id = '$eid' AND status = '1' ORDER BY id DESC LIMIT 8");
			    $query_banners2 = mysqli_query($db_con, "SELECT * FROM banners WHERE rel_estabelecimentos_id = '$eid' AND status = '1' ORDER BY id DESC LIMIT 8");
				$has_banners = mysqli_num_rows($query_banners);
				if ($has_banners && $app['funcionalidade_banners'] == 1) {
				?>
				
				    <div> <!--style="display: grid; grid-template-columns: 50% 50%; gap: 10px;"-->
				
				    <?php 
				    	$actual3 = 0;
							while ( $data_banners2 = mysqli_fetch_array( $query_banners2 ) ) {
							$banner_video_link = $data_banners2['video_link'];
						
						if ($banner_video_link) {
				        
				    ?>
				        <div style="margin-bottom: 10px;">
    				        <iframe class="visible-xs visible-sm" width="100%" height="240px" src="https://www.youtube.com/embed/<?php echo $banner_video_link; ?>" frameborder="0" allowfullscreen>
                            </iframe>
				        </div>
				    <?php
						}
						
							
						 $actual3++; } 
				    ?>

				


						</div>

				
				<?php } ?>

			<?php } ?>

			<?php if ($app['funcionalidade_banners']) { ?>

				<?php
				$eid = $app['id'];
				$query_banners = mysqli_query($db_con, "SELECT * FROM banners WHERE rel_estabelecimentos_id = '$eid' AND status = '1' ORDER BY id DESC LIMIT 8");
				$query_banners2 = mysqli_query($db_con, "SELECT * FROM banners WHERE rel_estabelecimentos_id = '$eid' AND status = '1' ORDER BY id DESC LIMIT 8");
				$has_banners = mysqli_num_rows($query_banners);
				if ($has_banners && $app['funcionalidade_banners'] == 1) {
				?>
				
				    <div> <!--style="display: grid; grid-template-columns: 50% 50%; gap: 10px;"-->
				
				    <?php 
				    	$actual2 = 0;
							while ( $data_banners2 = mysqli_fetch_array( $query_banners2 ) ) {
							$banner_video_link = $data_banners2['video_link'];
						
						
						if ($banner_video_link) {
				        
				    ?>
				        <div style="margin-top: -10px; display: flex; justify-content: center;" class="visible-xs hidden-sm">
    				        <iframe class="hidden-xs hidden-sm" width="640px" height="480px" src="https://www.youtube.com/embed/<?php echo $banner_video_link; ?>" frameborder="0" allowfullscreen>
                            </iframe>
				        </div>
				    <?php
						}
						
							
						 $actual2++; } 
				    ?>

					<div class="banners">

						<div id="carouselbanners" class="carousel slide" data-ride="carousel" data-interval="5000" style="height: 100%;">

							<div class="carousel-inner">
								<?php
								$actual = 0;
								while ($data_banners = mysqli_fetch_array($query_banners)) {
									$desktop = $data_banners['desktop'];
									$mobile = $data_banners['mobile'];
									if (!$mobile) {
										$mobile = $desktop;
									}
								?>

									<div class="item <?php if ($actual == 0) {
															echo 'active';
														}; ?>">

										<?php if ($data_banners['link']) { ?>
											<a href="<?php echo linker($data_banners['link']); ?>">
											<?php } ?>

											<picture>
                                                <source media="(max-width: 768px)" srcset="<?php echo imager($mobile); ?>">
                                                <source media="(min-width: 769px)" srcset="<?php echo imager($desktop); ?>">
                                                <img src="<?php echo imager($desktop); ?>" alt="Banner image">
                                            </picture>

											<?php if ($data_banners['link']) { ?>
											</a>
										<?php } ?>

									</div>

								<?php $actual++;
								} ?>

							</div>

							<?php if ($has_banners >= 1 && $actual >= 2) { ?>

								<a class="left seta seta-esquerda carousel-control" href="#carouselbanners" data-slide="prev">
									<span class="glyphicon glyphicon-chevron-left"></span>
								</a>
								<a class="right seta seta-direita carousel-control" href="#carouselbanners" data-slide="next">
									<span class="glyphicon glyphicon-chevron-right"></span>
								</a>

							<?php } ?>

						</div>

					</div>
					
				
	                </div>
				<?php } ?>

			<?php } ?>
		
		

			<div class="categorias">

				<?php
				$app_id = $app['id'];
				$query_categoria = 
				"
				SELECT *, count(*) as total, categorias.nome as categoria_nome, categorias.id as categoria_id, count(produtos.id) as produtos_total
				FROM categorias AS categorias 

				INNER JOIN produtos AS produtos 
				ON produtos.rel_categorias_id = categorias.id 

				WHERE categorias.rel_estabelecimentos_id = '$app_id' 
				AND categorias.visible = '1' 
				AND categorias.status = '1' 

				GROUP BY categorias.id 
				ORDER BY categorias.ordem ASC

				LIMIT 20
				";
				$query_categoria = mysqli_query( $db_con, $query_categoria );
				while ( $data_categoria = mysqli_fetch_array( $query_categoria ) ) {
				?>

				<div class="categoria">

					<div class="row">
						<div class="col-md-8 col-sm-8 col-xs-8">
							<span class="title"><?php echo htmlclean( $data_categoria['categoria_nome'] ); ?></span>
						</div>
						<div class="col-md-4 col-sm-4 col-xs-4">
							<a class="vertudo" href="<?php echo $app['url']; ?>/categoria/<?php echo $data_categoria['categoria_id']; ?>">Ver tudo <i class="lni lni-arrow-right"></i></a>
						</div>
					</div>

					<div class="produtos">

						<div class="row">
						
						<?php if($app['exibicao'] ==1){ ?>
						<!--<div class="tv-infinite">-->
						<div class="tv-grid grid_produtos" style="padding: 12px !important; display: grid; grid-template-columns: 50% 48%; gap: 10px 10px;">
						    
						<?php } ?>
						
						<?php if($app['exibicao'] ==2){ ?>
						<div class="novalistagem">
						<?php } ?>
						
						
							
								<?php
								$exibir = "8";
								$cat_id = $data_categoria['categoria_id'];
								// $query_produtos = mysqli_query( $db_con, "SELECT * FROM produtos ORDER BY id DESC LIMIT 8" );
								$query_produtos = mysqli_query( $db_con, "SELECT * FROM produtos WHERE rel_categorias_id = '$cat_id' AND visible = '1' AND status = '1' ORDER BY id ASC LIMIT $exibir" );
								while ( $data_produtos = mysqli_fetch_array( $query_produtos ) ) {
									// Seta valor
									if( $data_produtos['oferta'] == "1" ) {
										$valor_final = $data_produtos['valor_promocional'];
									} else {
										$valor_final = $data_produtos['valor'];
									}
									
									 if ($data_produtos['variacao_menor'] == 1 and has_variacao($data_produtos['id'])) {
                                            $valoresVariacoes = [];
                                            $variacao = json_decode($data_produtos['variacao'], TRUE);
                                            for ($x = 0; $x < count($variacao); $x++) {
                                                for ($y = 0; $y < count($variacao[$x]['item']); $y++) {
                                                    $valorVariacao = htmljson($variacao[$x]['item'][$y]['valor']);
                                                    if ($valorVariacao > 0) {
                                                        $valoresVariacoes[] = $valorVariacao;
                                                    }
                                                }
                                            }
                        
                                            if (!empty($valoresVariacoes)) {
                                                asort($valoresVariacoes);
                                                $valor_final = current($valoresVariacoes);
                                            } else {
                                               	// Seta valor
            									if( $data_produtos['oferta'] == "1" ) {
            										$valor_final = $data_produtos['valor_promocional'];
            									} else {
            										$valor_final = $data_produtos['valor'];
            									}
                                            }
                                        }
									
								?>

								<?php if($app['exibicao'] ==2){ ?>
									
									<div class="col-md-6 col-sm-12 col-xs-12">

										<div class="novoproduto">
										    
										    <?php if($data_produtos['estoque'] == 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<?php if($data_produtos['estoque'] == 2 && $data_produtos['posicao'] >= 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<a href="<?php echo $app['url']; ?>">
											
											<?php } ?>
											<?php } ?>
											
												<div class="row">

													<div class="col-md-9 col-sm-7 col-xs-7 npr">

														<span class="nome"><?php echo htmlclean( $data_produtos['nome'] ); ?></span>
														<span class="descricao"><?php echo htmlclean( $data_produtos['descricao'] ); ?></span>
														<div class="preco">
														<?php if( $valor_final > 0 ) { ?>
														<?php if( $data_produtos['oferta'] == "1"  && $data_produtos['variacao_menor'] != 1 ) { ?>
														<span class="valor_anterior" style="text-decoration: line-through;">De R$: <?php echo dinheiro( $data_produtos['valor'], "BR" ); ?></span>
														<span class="valor valor-green">Por R$ <?php echo dinheiro( $valor_final, "BR" ); ?></span>
														<?php } else { ?>
														<span class="blank_valor_anterior"></span>
														<span class="valor valor-green">R$: <?php echo dinheiro( $valor_final, "BR" ); ?></span>
														<?php if($data_produtos['estoque'] == 2 && $data_produtos['posicao'] <= 0 ) { ?>
														<span class="descricao ">VER OPÇÕES</span>
        												<?php } ?>
														<?php } ?>
														<?php } else { ?>
														<span class="blank_valor_anterior"></span>
														
														<?php if($data_produtos['estoque'] == 2 && $data_produtos['posicao'] >= 1 ) { ?>
        												<span class="valor ">Selecione os opcionais</span>
        												<?php } else { ?>
        												<span class="valor ">VER OPÇÕES</span>
        												<?php } ?>

														<?php } ?>
														<p>
														<p>
														<div style="display: flex; align-items: center;">
                											<div class="mt-2">
                                                                            <label class="label label-estatisticas" style="padding: 0em .3em !important; font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-shopping-basket"></i>
                                                                Vendas: <?php echo $data_produtos["total_vendas"];?> </label>
                                                            
                                                                                <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-eye"></i>
                                                                Visualizações: <?php echo $data_produtos['visualizacoes']; ?></label>
                                                            </div>
        												</div>
														 
														
													</div>

													</div>

													<div class="col-md-3 col-sm-5 col-xs-5">
													    

														<div class="capa">
														    
												        <?php if( $data_produtos['oferta'] == "1" ) { ?><div class="visualizacoes" style="font-weight: 600; position: absolute; top: 0; left: 0; background-color: #feeeea; color: #ee4d2d;padding: 3px; font-size: 12px;">
                                                        - R$ 
                                                            <?php echo $data_produtos['valor'] - $data_produtos['valor_promocional']; // Substitua pelo número real de visualizações ?>
                                                        </div>
												      
												      <?php }?>
														 <div class="visualizacoes" style="position: absolute; top: 10px; right: 10px; background-color: white; color: #333; padding: 5px; border-radius: 10px; font-size: 14px;">
                                                            <i class="lni lni-eye" style="margin-right: 5px;"></i>
                                                            <?php echo $data_produtos['visualizacoes']; // Substitua pelo número real de visualizações ?>
                                                        </div>
                                                        
															<img src="<?php echo thumber( $data_produtos['destaque'], 450 ); ?>"/>
														</div>

													</div>

												</div>

											</a>

										</div>

									</div>
										
									<?php } ?>
									
									
								
									
									<?php if($app['exibicao'] ==1){ ?>
									 
									<!--<div class="col-md-3 col-infinite">-->
									<div class="col-12">

										<div class="produto" style="margin: 0 0 30px 0; box-shadow: 0 10px 20px rgba(0, 0, 0, .15); /* background: rgba(0, 0, 0, .05); */ /* border: 1px solid rgba(0, 0, 0, .15); */ border-radius: 12px; overflow: hidden; transition: 0.3s;"> 
										<!--height: 250px !important; -->
										   
										    
										    
										    
										    <?php if($data_produtos['estoque'] == 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<?php if($data_produtos['estoque'] == 2 && $data_produtos['posicao'] >= 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<a href="<?php echo $app['url']; ?>">
											
											<?php } ?>
											<?php } ?>
								    <style>
									    /* Ícone Play */


                                            .produto .capa .playVideo::after {
                                                content: "";
                                                position: absolute;
                                                top: 50%;
                                                left: 50%;
                                                width: 0;
                                                height: 0;
                                                border-style: solid;
                                                border-width: 30px 0 30px 45px; /* Tamanho do trigulo */
                                                border-color: transparent transparent transparent #FFFFFF; /* Trigulo banco */
                                                transform: translate(-50%, -50%);
                                            }
                                            
                                            a .lni-play {
                                                color: transparent !important;
                                                text-decoration: none !important;
                                            }

									</style>
												
												<!-- <div class="capa" style="background: url(<?php echo thumber( $data_produtos['destaque'], 450 ); ?>) no-repeat center center;">
													<span class="nome"><?php echo htmlclean( $data_produtos['nome'] ); ?></span>
												</div> -->
												<div class="capa" style="position: relative; display: flex; align-items: center; justify-content: center; border-radius: 6px 6px 0 0 !important; background: url(<?php echo thumber( $data_produtos['destaque'], 450 ); ?>) no-repeat center center;">
												    
												    
										    <div style="position: absolute; font-weight: 600; pointer-events: none; z-index: 9; bottom: 0px !important; left: 0px !important;">
                                                        <?php
                                                        if ($data_produtos["total_vendas"] >= 50) {
                                                        ?>
                                                            <div style="color: white; background-color: orange; padding-left: 10px; padding-right: 10px;">
                                                                Mais Vendido
                                                            </div>
                                                        <?php
                                                        }
                                                        
                                                         $resultado = mysqli_query($db_con, "SELECT * FROM `produtos` WHERE `produtos`.`id` = {$data_produtos['id']}");

                                                        // Verifique se a consulta foi bem-sucedida
                                                        if ($resultado) {
                                                            // Obtenha a linha de resultado como uma matriz associativa
                                                            $linha = mysqli_fetch_assoc($resultado);
                                                        
                                                            // Agora você pode usar $linha['oferta_do_dia'] no seu if
                                                            if ($linha['oferta_do_dia'] == 1) {
                                                                ?>
                                                                    <div style="color: white; background-color: #239c1a; padding-left: 10px; padding-right: 10px;">
                                                                        Oferta Do Dia
                                                                    </div>
                                                                <?php
                                                            }
                                                        } else {
                                                            echo "Erro na consulta SQL: " . mysqli_error($db_con);
                                                        }
                                                        
                                                        ?>
                                
                                                    </div>
												      
												        <?php if( $data_produtos['oferta'] == "1" ) { ?>
												        <div class="visualizacoes" style="font-weight: 600; position: absolute; top: 0; left: 0; background-color: #feeeea; color: #ee4d2d;padding: 3px; font-size: 12px;">
                                                        - R$ 
                                                            <?php echo $data_produtos['valor'] - $data_produtos['valor_promocional']; // Substitua pelo número real de visualizações ?>
                                                        </div>
												      
												      <?php }?>
												      <div class="visualizacoes" style="position: absolute; top: 10px; right: 10px; background-color: white; color: #333; padding: 5px; border-radius: 10px; font-size: 14px;">
                                                        <i class="lni lni-eye" style="margin-right: 5px;"></i>
                                                            <?php echo $data_produtos['visualizacoes']; // Substitua pelo número real de visualizações ?>
                                                        </div>
                                                        
												    <div style="position: absolute; font-weight: 600; pointer-events: none; z-index: 9;">
                    										        <div class="capa" style="background: transparent;">
                                                                    <?php
                                                                    if ($data_produtos['video_link']) {
                                                                        echo "<div class='playVideo'><i class='lni lni-play'></i></div>";
                                                                    }
                                                                    ?>
                                                                </div>
                                                    </div>
														<span class="nome"></span>
												</div>
												
												
												<?php if(($data_produtos['estoque'] == 1) || ($data_produtos['estoque'] == 2 && $data_produtos['posicao'] >= 1)) { ?>
												<?php if( $valor_final > 0 ) { ?>
												<div style="padding: 10px;">
													<div style="display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; display: block; margin: 0 0 8px 0; font-weight: 600; color: rgba(0, 0, 0, .5); font-size: 14px; line-height: 16px;">
    													<?php echo htmlclean( $data_produtos['nome'] ); ?>
    												</div>
    												
    												
    												
    												
    												<div style="display: flex; align-items: center;">
    												<div style="float: left; width: 75%;">
        												
        												<?php if( $data_produtos['oferta'] == "1"  && $data_produtos['variacao_menor'] != 1 ) { ?>
        													<span class="valor_anterior"  style="text-align: left !important; margin: 0 !important; color:#FF0000">De: <?php echo dinheiro( $data_produtos['valor'], "BR" ); ?></span>
        												<?php } ?>
        												
        												<span class="apenas <?php if( $data_produtos['oferta'] != "1" ) { echo 'apenas-single'; }; ?>">
        													<?php if( has_variacao( $data_produtos['id'] ) ) { echo "Por apenas"; } else { echo "Por apenas"; }; ?>
        												</span>
        												
        												<span class="valor" style="text-align: left !important; margin: 0 !important; color: #67b017;">R$ <?php echo dinheiro( $valor_final, "BR" ); ?></span>
    												</div>
    											
    												<div style="float: right; width: 25%;">
                                                        <i class="lni lni-cart" style="float: right; display: block; font-size: 30px; color: #67b017;"></i>
                                                    </div>
    												</div>
    													<div style="display: flex; align-items: center;">
                											<div class="mt-2">
                                                                            <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-shopping-basket"></i>
                                                                Vendas: <?php echo $data_produtos["total_vendas"];?> </label>
                                                            
                                                                                <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-eye"></i>
                                                                Visualizações: <?php echo $data_produtos['visualizacoes']; ?></label>
                                                            </div>
        												</div>
												
												</div>
											
												
												
												
												
												<?php } else { ?>
												<div style="padding: 0px 8px 5px 8px;">
													<div style="display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; display: block; margin: 0 0 8px 0; font-weight: 600; color: rgba(0, 0, 0, .5); font-size: 14px; line-height: 16px;">
    													<?php echo htmlclean( $data_produtos['nome'] ); ?>
    												</div>
    												<div style="display: flex; align-items: center;">
        												<div style="float: left; width: 75%;">
            												<span class="valor" style="text-align: left !important; margin: 0 !important; color: #67b017;">Consulte oferta!</span>
            												
            												    <span class="valor_anterior"  style="text-align: left !important; margin: 0 !important; color: transparent"> </span>
            												  
        												</div>
        												<div style="float: right; width: 25%;">
                                                            <i class="lni lni-cart" style="float: right; display: block; font-size: 30px; color: #67b017;"></i>
                                                        </div>
    												</div>
    												<div style="display: flex; align-items: center;">
                											<div class="mt-2">
                                                                            <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-shopping-basket"></i>
                                                                Vendas: <?php echo $data_produtos["total_vendas"];?> </label>
                                                            
                                                                                <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-eye"></i>
                                                                Visualizações: <?php echo $data_produtos['visualizacoes']; ?></label>
                                                            </div>
        											</div>
    											</div>
												<?php } ?>
												
													<?php } else { ?>
												    <div class="detalhes" style="margin: 30px; background-color:#C0C0C0 !important"><i class="lni lni-close"></i> <span>Sem Estoque</span></div>
												<?php } ?>

											</a>

										</div>

									</div>
									
									 
									<?php } ?>

								<?php } ?>

								<?php if($app['exibicao'] ==1){ ?>
								<div class="col-12" style="margin: auto; float: none;">
								    <div class="produto" style="min-height:70px !important; margin: 0 0 30px 0; box-shadow: 0 10px 20px rgba(0, 0, 0, .15); /* background: rgba(0, 0, 0, .05); */ /* border: 1px solid rgba(0, 0, 0, .15); */ border-radius: 12px; overflow: hidden; transition: 0.3s;">
									<a class="vertudo" style="padding: 15px; text-align: center; display: flex;" href="<?php echo $app['url']; ?>/categoria/<?php echo $data_categoria['categoria_id']; ?>">Mais itens de <?php echo $data_categoria['categoria_nome']; ?>  <i style="padding: 10px;" class="lni lni-arrow-right"></i></a>
								
									</div>
								</div>
								<?php } ?>
								
							</div>

						</div>

					</div>

				</div>

				<?php } ?>
				
				
			</div>

		</div>

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include($virtualpath.'/_layout/rdp.php');
include($virtualpath.'/_layout/footer.php');
?>