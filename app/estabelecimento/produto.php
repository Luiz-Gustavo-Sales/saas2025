<?php
// CORE
include($virtualpath . '/_layout/define.php');
//URL
$pegaurl =  "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// APP
global $app;
is_active($app['id']);
global $inparametro;
$back_button = "true";
// Querys
$app_id = $app['id'];
$produto = $inparametro;
// Has content
$query_content = mysqli_query($db_con, "SELECT * FROM produtos WHERE rel_estabelecimentos_id = '$app_id' AND id = '$produto' AND status = '1' ORDER BY id ASC LIMIT 1");
$data_content = mysqli_fetch_array($query_content);

$query_content2 = mysqli_query($db_con, "SELECT * FROM estabelecimentos WHERE id = '$app_id'");
$data_content2 = mysqli_fetch_array($query_content2);


$has_content = mysqli_num_rows($query_content);
// SEO
$seo_subtitle = $app['title'] . " - " . $data_content['nome'];
$seo_keywords = $app['title'] . ", " . $seo_title . ", " . $data_content['nome'];
$seo_image = thumber($data_content['destaque'], 400);
// HEADER
$system_header .= "";
include($virtualpath . '/_layout/head.php');
include($virtualpath . '/_layout/top.php');
include($virtualpath . '/_layout/sidebars.php');
include($virtualpath . '/_layout/modal.php');
instantrender();


if (isset($_POST['quantidade_para_remover'])) {
    $quantidade_para_remover = $_POST['quantidade_para_remover'];
    $remove_query = mysqli_query($db_con, "UPDATE `produtos` SET `posicao` = `posicao` - '$quantidade_para_remover' WHERE `id` = '$produto'");
}

// Verifica se a coluna 'visualizacoes' existe na tabela 'produtos'
$colunaExiste = $db_con->query("SHOW COLUMNS FROM produtos LIKE 'visualizacoes'");
if ($colunaExiste->num_rows == 0) {
    // Se a coluna não existir, adiciona a coluna 'visualizacoes'
    $db_con->query("ALTER TABLE produtos ADD visualizacoes INT DEFAULT 0");
}


// Nome do cookie
$cookieName = "visualizado_$produto";

// Verifica se o cookie para esse produto existe e não expirou
if (!isset($_SESSION["visualizado_$produto"]) || time() > $_SESSION["visualizado_$produto"]) {
    $_SESSION["visualizado_$produto"] = time() + 86400;

    $updateQuery = "UPDATE produtos SET visualizacoes = visualizacoes + 1 WHERE id = '$produto'";
    $db_con->query($updateQuery);
}

?>

<div class="sceneElement">

	<div class="header-interna">

		<div class="locked-bar visible-xs visible-sm">

			<div class="avatar">
				<div class="holder">
					<a href="<?php echo $app['url']; ?>">
						<img src="<?php echo $app['avatar']; ?>" />
					</a>
				</div>
			</div>

		</div>

		<div class="holder-interna holder-interna-nopadd visible-xs visible-sm"></div>

	</div>

	<?php if ($has_content) { ?>

		<div class="middle">

			<div class="container nopaddmobile">

				<div class="row rowtitle hidden-xs hidden-sm">

					<div class="col-md-12">
						<div class="title-icon">
							<span><?php echo $data_content['nome']; ?></span>
						</div>
						<div class="bread-box">
							<div class="bread">
								<a href="<?php echo $app['url']; ?>"><i class="lni lni-home"></i></a>
								<span>/</span>
								<a href="<?php echo $app['url']; ?>/categoria/<?php echo data_info("categorias", $data_content['rel_categorias_id'], "id"); ?>">Categorias</a>
								<span>/</span>
								<a href="<?php echo $app['url']; ?>/categoria/<?php echo data_info("categorias", $data_content['rel_categorias_id'], "id"); ?>"><?php echo data_info("categorias", $data_content['rel_categorias_id'], "nome"); ?></a>
								<?php if ($data_content['rel_sub_categoria_id']) { ?>
									<span>/</span>
									<a href="<?php echo $app['url']; ?>/subcategoria/<?php echo $data_content['rel_sub_categoria_id']; ?>"><?php echo data_info("sub_categorias", $data_content['rel_sub_categoria_id'], "nome"); ?></a>
								<?php } ?>
								<span>/</span>
								<a href="<?php echo $app['url']; ?>/produto/<?php echo $produto; ?>"><?php echo $data_content['nome']; ?></a>
							</div>
						</div>
					</div>

					<div class="col-md-12 hidden-xs hidden-sm">
						<div class="clearline"></div>
					</div>

				</div>

				<div class="single-produto nost">

					<div class="row">

						<div class="col-md-5">

							<div class="galeria">

								<div class="row">

									<div class="col-md-12">

										<?php
										$query_galeria = "SELECT * FROM midia WHERE rel_id = '$produto' ORDER BY id ASC";
										?>

										<div id="carouselgaleria" class="carousel slide">

											<div class="carousel-inner">

												<?php
												if ($data_content['video_link']) {
												?>

													<div class="item zoomer active">
														<a data-fancybox="galeria" href="<?php echo imager($data_content['destaque']); ?>">
															<iframe width="100%" height="345" src="https://www.youtube.com/embed/<?php echo $data_content['video_link']; ?>" frameborder="0" allowfullscreen>
															</iframe>
														</a>
													</div>
												<?php
												} else {
												?>

													<div class="item zoomer active">
														<a data-fancybox="galeria" href="<?php echo imager($data_content['destaque']); ?>">
															<img src="<?php echo imager($data_content['destaque']); ?>" alt="<?php echo $data_content['nome']; ?>" title="<?php echo $data_content['nome']; ?>" />
														</a>
													</div>

												<?php

												}

												?>

												<?php
												$actual = 0;
												$sql_galeria = mysqli_query($db_con, $query_galeria);
												$total_galeria = mysqli_num_rows($sql_galeria);
												while ($data_galeria = mysqli_fetch_array($sql_galeria)) {
												?>

													<div class="item zoomer">
														<a data-fancybox="galeria" href="<?php echo imager($data_galeria['url']); ?>">
															<img src="<?php echo imager($data_galeria['url']); ?>" alt="<?php echo $data_content['nome']; ?>" title="<?php echo $data_content['nome']; ?>" />
														</a>
													</div>

												<?php $actual++;
												} ?>

											</div>

											<?php if ($total_galeria >= 1) { ?>

												<a class="left seta seta-esquerda carousel-control" href="#carouselgaleria" data-slide="prev">
													<span class="glyphicon glyphicon-chevron-left"></span>
												</a>
												<a class="right seta seta-direita carousel-control" href="#carouselgaleria" data-slide="next">
													<span class="glyphicon glyphicon-chevron-right"></span>
												</a>

											<?php } ?>
											
											<!-- Thumbnails -->
                                            <ol class="carousel-indicators list-inline">
                                              <?php
												if ($data_content['video_link']) {
												?>
                                                    <li class="list-inline-item active">
                                                        <a id="carousel-selector-0" class="selected" data-slide-to="0" data-target="#carouselgaleria">
                                                          <img src="https://mercadorio.com.br/player.jpg">
                                                        </a>
                                                    </li>
												<?php
												} else {
												?>
                                                    <li class="list-inline-item active">
                                                        <a id="carousel-selector-0" class="selected" data-slide-to="0" data-target="#carouselgaleria">
                                                          <img src="<?php echo imager($data_content['destaque']); ?>" alt="<?php echo $data_content['nome']; ?>" title="<?php echo $data_content['nome']; ?>" />
                                                        </a>
                                                    </li>
												<?php

												}

												?>

												<?php
												$actual = 0;
												$ordemCarrosel = 1;
												$sql_galeria = mysqli_query($db_con, $query_galeria);
												$total_galeria = mysqli_num_rows($sql_galeria);
												while ($data_galeria = mysqli_fetch_array($sql_galeria)) {
												?>
                                                    <li class="list-inline-item">
                                                        <a id="carousel-selector-1" data-slide-to="<?= $ordemCarrosel; ?>" data-target="#carouselgaleria">
                                                          <img src="<?php echo imager($data_galeria['url']); ?>" alt="<?php echo $data_content['nome']; ?>" title="<?php echo $data_content['nome']; ?>" />
                                                        </a>
                                                    </li>

												<?php $ordemCarrosel++; $actual++;
												} ?>
                                             </ol>

										</div>

									</div>

								</div>

							</div>

						</div>

						<div class="col-md-7">

							<div class="padd-container-mobile">

								<div class="produto-detalhes">

									<div class="row visible-xs visible-sm">
										<div class="col-md-12">
											<div class="nome">
												<span><?php echo htmlclean($data_content['nome']); ?></span>
											</div>
										</div>
									</div>

									<div class="row">

										<div class="col-md-3 hidden-xs hidden-sm">

											<span class="thelabel thelabel-normal">Link do Produto:</span>

										</div>

										<div class="col-md-9">


											<div class="row">
												<div class="col-md-12">
													<div class="ref">
														<span><a href="https://api.whatsapp.com/send?text=<?php print $pegaurl; ?>" target="_blank" class="btn btn-success btn-sm" style="color:#FFFFFF"><i class="lni lni-whatsapp"></i> Compartilhe no WhatsApp</a></span>
													</div>
												</div>
											</div>


										</div>

									</div>

									<div class="row">

										<div class="col-md-3 hidden-xs hidden-sm">

											<span class="thelabel thelabel-normal">REF:</span>

										</div>

										<div class="col-md-9">

											<?php if ($data_content['ref']) { ?>
												<div class="row">
													<div class="col-md-12">
														<div class="ref">
															<span>#<?php echo htmlclean($data_content['ref']); ?></span>
														</div>
													</div>
												</div>
											<?php } ?>

										</div>

									</div>

									<div class="row">

										<div class="col-md-3 hidden-xs hidden-sm">

											<span class="thelabel thelabel-normal">Detalhes:</span>

										</div>

										<div class="col-md-9">

											<?php if ($data_content['descricao']) { ?>
												<div class="row">
													<div class="col-md-12">
														<div class="descricao">
															<span><?php echo nl2br($data_content['descricao']); ?></span>
														</div>
													</div>
												</div>
											<?php } ?>
											<div class="row">
												<div class="col-md-12">
													<?php
													// Seta valor
													if ($data_content['oferta'] == "1") {
														$valor_final = $data_content['valor_promocional'];
													} else {
														$valor_final = $data_content['valor'];
													}
													if ($data_content['variacao_menor'] == 1 and has_variacao($data_content['id'])) {
														$valoresVariacoes = [];
														$variacao = json_decode($data_content['variacao'], TRUE);
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
															$valor_final2 = current($valoresVariacoes);
														} else {
															$valor_final = $data_content['valor'];
														}
													}
													?>

													<div class="valor_anterior">

														<span><?php if ($data_content['oferta'] == "1") { ?>De: <strike><?php echo dinheiro($data_content['valor'], "BR"); ?></strike><?php } ?> <?php if (has_variacao($data_content['id']) and current($valoresVariacoes) > 0) {
																																																		echo "A partir de";
																																																	} else {
																																																		echo "Por apenas";
																																																	}; ?></span>


													</div>

													<div class="valor">


														<?php if (has_variacao($data_content['id']) and current($valoresVariacoes) > 0) { ?>
															<span>R$ <?php echo dinheiro($valor_final2, "BR"); ?></span>
														<? } else { ?>
															<span>R$ <?php echo dinheiro($valor_final, "BR"); ?></span>
														<? } ?>

													</div>

													<div>
														<div style="background: url(<?php echo thumber($app['banner_frete_gratis'], 450) ?>) no-repeat center center; background-size: contain; height:50px; width: 150px; margin-top: 10px; margin-bottom: 10px;" />
													</div>
												</div>
											</div>

										</div>
											<?php 
									$comissionamento_query = mysqli_query($db_con, "SELECT comissionamento FROM `assinaturas` WHERE `rel_estabelecimentos_id` = '$app_id'");
											        $tem_comissionamento = mysqli_fetch_array($comissionamento_query);
											        
						                if( $tem_comissionamento[0] != "1" ) { ?>

    										<div class="row">
    
    											<div class="col-md-10 col-sm-10 col-xs-10" style="max-width: 320px; margin-bottom: 10px;">
    												<a href="https://wa.me/<?php echo "55" . clean_str(htmlclean($app['contato_whatsapp'])); ?>?text=<?php echo urlencode('*Estou em dúvida sobre esse produto e o frete para minha região:* ' . $pegaurl); ?>" target="_blank">
    													<div style="background-color: #449D44; color: white; padding: 10px; border-radius: 10px;">
    														<i class="lni lni-whatsapp" style="color: white;"></i>
    														Está com dúvida? Chame no zap!
    													</div>
    												</a>
    											</div>
    
    											</a>
    
    										</div>
    										
										<?php } ?>

									</div>

								</div>

								<?php if ($data_content['mostrar_estoque'] == 1 and $data_content['posicao']) { ?>
									<div class="row">

										<div class="col-md-3 hidden-xs hidden-sm">

											<span class="thelabel thelabel-normal">Estoque:</span>

										</div>

										<div class="col-md-9">


											<div class="row">
												<div class="col-md-12">
													<div class="ref">
														<span><?php echo htmlclean($data_content['posicao']); ?> unidade(s)</span>
													</div>
												</div>
											</div>

										</div>

									</div>
								<?php } ?>

								<?php if ($data_content['aviso_baixo_estoque'] == 1 and $data_content['estoque'] < $data_content['baixo_estoque']) { ?>
									<div class="row">



										<div class="col-md-12">


											<div class="row">
												<div class="col-md-12">
													<div class="ref">
														<span class="thelabel thelabel-normal">Baixo estoque: <? echo $data_content['baixo_estoque']; ?></span>
													</div>
												</div>
											</div>

										</div>


									</div>

								<?php } ?>


								<?php if ($data_content['estoque'] == "0" and $data_content['mostrar_estoque'] == "1") { ?>
									<div class="row">



										<div class="col-md-12">


											<div class="row">
												<div class="col-md-12">
													<div class="ref">
														<span class="thelabel thelabel-normal">
															<h3>Produto indisponível</h3>
														</span>
													</div>
												</div>
											</div>

										</div>


									</div>

								<?php } ?>



							</div>

							<form id="the_form" method="POST">

								<?php if (data_info("estabelecimentos", $app['id'], "funcionamento") == "1") { ?>

									<div class="comprar">

										<?php if (has_variacao($data_content['id']) && $app['funcionalidade_variacao'] == "1") { ?>

											<?php
											$variacao = json_decode($data_content['variacao'], TRUE);
											for ($x = 0; $x < count($variacao); $x++) {
											?>

												<div class="line line-variacao" min="<?php echo htmljson($variacao[$x]['escolha_minima']); ?>" max="<?php echo htmljson($variacao[$x]['escolha_maxima']); ?>">
													<div class="row">
														<div class="col-md-6">
															<span class="thelabel pull-left">
																<?php echo htmljson($variacao[$x]['nome']); ?>
															</span>
														</div>
														<div class="col-md-6">
															<span class="escolhas pull-right">
																Minímo: <?php echo htmljson($variacao[$x]['escolha_minima']); ?> e Máximo: <?php echo htmljson($variacao[$x]['escolha_maxima']); ?>
															</span>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">

															<div class="opcoes">

																<?php
																for ($y = 0; $y < count($variacao[$x]['item']); $y++) {
																?>

																	<div produto-id="<?php echo $data_content['id']; ?>" variacao-id="<?php echo $x; ?>" variacao-produto-id="<?php echo $y; ?>" class="opcao<?php if (htmljson($variacao[$x]['item'][$y]['estoque']) == "0" and $data_content['mostrar_estoque'] == "1") { echo "indisponivel";}; ?>" variacao-item="<?php echo $y; ?>" variacao-prod="<?php echo $x; ?>" valor-adicional="<?php echo htmljson($variacao[$x]['item'][$y]['valor']); ?>">
																		<div class=" check <?php if (htmljson($variacao[$x]['item'][$y]['quantidade']) == 1) { echo "fakehidden"; } ?>">
																			<i class="lni"></i>
																		</div>
																		<div class="detalhes" style="width: 47%;">
																			<span class="titulo">
																				<?php echo htmljson($variacao[$x]['item'][$y]['nome']); ?>
																				<?php if ($variacao[$x]['item'][$y]['valor']) { ?>
																					(+ R$ <?php echo dinheiro(htmljson($variacao[$x]['item'][$y]['valor']), "BR"); ?>)
																				<?php } ?>
																			</span>
																			<span class="descricao"><?php echo htmljson($variacao[$x]['item'][$y]['descricao']); ?></span>
																			<span class="descricao"><?php if ($data_content['mostrar_estoque'] == 1 and htmljson($variacao[$x]['item'][$y]['estoque'])) { ?>
																					Estoque: <?php echo htmljson($variacao[$x]['item'][$y]['estoque']); ?>
																				<?php } ?>

																				<?php if ($data_content['aviso_baixo_estoque'] == 1 and htmljson($variacao[$x]['item'][$y]['estoque']) < htmljson($variacao[$x]['item'][$y]['baixo_estoque'])) { ?>
																					<br>Baixo estoque: <? echo htmljson($variacao[$x]['item'][$y]['baixo_estoque']); ?>
																				<?php } ?>
																				<?php if (htmljson($variacao[$x]['item'][$y]['estoque']) == "0") { ?>
																					<br>Indisponível
																				<?php } ?>

																			</span>
																		</div>
																		<div class="clear"></div>

																		<div class="campo-numero pull-right">

																			<?php if (htmljson($variacao[$x]['item'][$y]['quantidade']) == 1) { ?>
																				<i class="decrementar-var lni lni-minus val-variacao" onclick="javascript:decrementar-var('#quantidade<?php echo $x; ?>_<?php echo $y; ?>'); ativachecks();"></i>
																				<input class="quantidadeVariacao" id="quantidade<?php echo $x; ?>_<?php echo $y; ?>" type="text" name="quantidade_variacao[<?php echo $x; ?>][<?php echo $y; ?>]" value="0" />
																				<!--<i class="incrementar lni lni-plus val-variacao" onclick="javascript:var input = $(this).prev('input.quantidadeVariacao'); var currentVal = parseInt(input.val()); if (!isNaN(currentVal) && currentVal > 0) { input.val(currentVal + 1); var opcao = $('div[produto-id=' + <?php echo $data_content['id']; ?> + '][variacao-item=' + <?php echo $y; ?> + '][variacao-prod=' + <?php echo $x; ?> + '].opcao'); opcao.addClass('active'); }"></i>-->
																				<i class="incrementar lni lni-plus val-variacao" onclick="javascript:var inputqtd = $(this).prev('input.quantidadeVariacao');  var currentValqtd = parseInt(inputqtd.val()); var input = $(this); var currentVal = parseInt(input.val()); if (!isNaN(currentVal) && currentValqtd > 0)  { input.val(currentVal + 1); } var opcao = $('div[produto-id=' + <?php echo $data_content['id']; ?> + '][variacao-item=' + <?php echo $y; ?> + '][variacao-prod=' + <?php echo $x; ?> + '].opcao'); opcao.addClass('active');"></i>

																				<input class="fakehidden" id="quantidade<?php echo $x; ?>" type="text" name="quantidade_variacao[<?php echo $x; ?>][<?php echo $y; ?>]" value="0" />
																			<?php } ?>

																		</div>
																	</div>
																	



																<?php } ?>

															</div>
															<div class="clear"></div>
															<input class="fakehidden variacao_validacao" type="text" name="variacao_<?php echo $x; ?>_validacao" value="" />
															<input class="fakehidden variacao_escolha" type="text" name="variacao[<?php echo $x; ?>]" value="" />
															<div class="error-holder"></div>
														</div>
													</div>
												</div>

											<?php } ?>
											<input id="quantidade" type="number" name="quantidade" value="1" class="fakehidden" />
										<?php } else { ?>
											<div class="line quantidade quantidadeProduto" sacola-produto-id="<?php echo $data_content['id']; ?>">
												<div class="row">
													<div class="col-md-3 col-sm-2 col-xs-2">
														<span class="thelabel hidden-xs hidden-sm">Quantidade:</span>
														<span class="thelabel visible-xs visible-sm">Qntd:</span>
													</div>
													<div class="col-md-9 col-sm-10 col-xs-10">
														<div class="campo-numero pull-right">
															<?php echo htmlclean($_SESSION['sacola'][$app['id']][$data_content['id']]['observacoes']); ?>
															<i class="decrementar lni lni-minus" onclick="decrementar('#quantidade');"></i>
															<input id="quantidade" type="number" name="quantidade" value="<?php if ($_SESSION['sacola'][$app['id']][$data_content['id']]['quantidade']) {
																																echo htmlclean($_SESSION['sacola'][$app['id']][$data_content['id']]['quantidade']);
																															} else {
																																echo '1';
																															} ?>" />
															<i class="incrementar lni lni-plus" onclick="incrementar('#quantidade');"></i>
														</div>
													</div>
												</div>
											</div>

										<? } ?>

										<div class="line observacoes">
											<div class="row">
												<div class="col-md-3 col-sm-2 col-xs-2">
													<span class="thelabel hidden-xs hidden-sm">Observações:</span>
													<span class="thelabel visible-xs visible-sm">Obs:</span>
												</div>
												<div class="col-md-9 col-sm-10 col-xs-10">
													<textarea id="observacoes" rows="5" name="observacoes" placeholder="Observações:"><?php echo htmlclean($_SESSION['sacola'][$app['id']][$data_content['id']]['observacoes']); ?></textarea>
												</div>
											</div>
										</div>







										<div class="line botoes subtotal-adicionar">
											<div class="row">
												<div class="col-md-6">
													<div class="subtotal">
														<strong>Valor:</strong>
														<span>R$ <?php $valor_finalcerto = $valor_final; 
														echo dinheiro($valor_final, "BR"); ?></span>
													</div>
												</div>

												<?php if ($data_content['estoque'] == "0" and $data_content['mostrar_estoque'] == "1") { ?>


												<? } else { ?>
													<div class="col-md-6">
														<span class="sacola-adicionar botao-acao pull-right"><i class="icone icone-sacola"></i>
															</i> <span>Adicionar a sacola</span></span>
													</div>

												<? } ?>

												<br>

											</div>

										</div>

									<?php } else { ?>

										<div class="comprar">

											<div class="line quantidade">
												<div class="row">
													<div class="col-md-3 col-sm-2 col-xs-2 hidden-xs hidden-sm">
														<span class="thelabel">Aviso:</span>
													</div>
													<div class="col-md-9 col-sm-12 col-xs-12">
														<span>O estabelecimento encontra-se temporariamente fechado, impossibilitando a realização de pedidos no momento!</span>
													</div>
												</div>
											</div>
											<div class="line botoes">
												<div class="row">
													<div class="col-md-12">
														<a target="_blank" href="https://wa.me/55<?php echo clean_str(htmlclean($app['contato_whatsapp'])); ?>" class="botao-acao pull-right"><i class="lni lni-whatsapp"></i> <span>Entre em contato</span></a>
													</div>
												</div>
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
	<?php
		$cat_id = $data_content['rel_categorias_id'];
		$eid = $app['id'];
		$query_relacionados = mysqli_query( $db_con, "SELECT * FROM produtos WHERE rel_estabelecimentos_id = '$eid' AND rel_categorias_id = '$cat_id' AND id != '$produto' AND visible = '1' AND status = '1' ORDER BY id DESC LIMIT 4" );
		$total_relacionados = mysqli_num_rows( $query_relacionados );
		
		
		?>

		<?php if( $total_relacionados >= 1 ) { ?>

		<div class="container relacionados">

			<div class="categoria">

				<div class="row">

					<div class="col-md-10 col-sm-10 col-xs-10">
						
						<span class="title">Veja também</span>
					
					</div>

					<div class="col-md-2 col-sm-2 col-xs-2">
						
						<a class="vertudo" href="<?php echo $app['url']; ?>/categoria/<?php echo $cat_id; ?>"><i class="lni lni-arrow-right"></i></a>
					
					</div>

				</div>

				<div class="produtos">

					<div class="row tv-grid">

						<div class="tv-infinite">

							<?php
							while ( $data_produtos = mysqli_fetch_array( $query_relacionados ) ) {
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
                                                $valor_final = $data_produtos['valor'];
                                            }
                                        }
							?>

								<div class="col-md-3 col-infinite">

		

										<div class="produto" style="min-height: 250px !important; margin: 0 0 30px 0; box-shadow: 0 10px 20px rgba(0, 0, 0, .15); /* background: rgba(0, 0, 0, .05); */ /* border: 1px solid rgba(0, 0, 0, .15); */ border-radius: 12px; overflow: hidden; transition: 0.3s;">
										      
										    
										    
										    <?php if($data_produtos['estoque'] == 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<?php if($data_produtos['estoque'] == 2 && $data_produtos['posicao'] >= 1 ) { ?>

											<a href="<?php echo $app['url']; ?>/produto/<?php echo $data_produtos['id']; ?>" title="<?php echo $data_produtos['nome']; ?>">
												
											<?php } else { ?>
											
											<a href="<?php echo $app['url']; ?>">
											
											<?php } ?>
											<?php } ?>
										    
												
												<!-- <div class="capa" style="background: url(<?php echo thumber( $data_produtos['destaque'], 450 ); ?>) no-repeat center center;">
													<span class="nome"><?php echo htmlclean( $data_produtos['nome'] ); ?></span>
												</div> -->
												<div class="capa" style="position: relative; display: flex; align-items: center; justify-content: center; border-radius: 6px 6px 0 0 !important; background: url(<?php echo thumber( $data_produtos['destaque'], 450 ); ?>) no-repeat center center;">
												    <div style="position: absolute; font-weight: 600; pointer-events: none; z-index: 9;   bottom: 0px !important; left: 0px !important;">
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
                                                                    <div style="color: white; background-color: #239c1a; padding-left: 10px; padding-right: 10px;    bottom: 0px;">
                                                                        Oferta Do Dia
                                                                    </div>
                                                                <?php
                                                            }
                                                        } else {
                                                            echo "Erro na consulta SQL: " . mysqli_error($db_con);
                                                        }
                                                        
                                                        ?>
                                
                                                    </div>
										    
												    <?php if( $data_produtos['oferta'] == "1" ) { ?><div class="visualizacoes" style="font-weight: 600; position: absolute; top: 0; left: 0; background-color: #feeeea; color: #ee4d2d;padding: 3px; font-size: 12px;">
                                                        - R$ 
                                                            <?php echo $data_produtos['valor'] - $data_produtos['valor_promocional']; // Substitua pelo número real de visualizações ?>
                                                        </div>
												      
												      <?php }?>
												      <div class="visualizacoes" style="position: absolute; top: 10px; right: 10px; background-color: white; color: #333; padding: 5px; border-radius: 10px; font-size: 14px;">
                                                            <i class="lni lni-eye" style="margin-right: 5px;"></i>
                                                            <?php echo $data_produtos['visualizacoes']; // Substitua pelo número real de visualizações ?>
                                                        </div>
                                                        
												    
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
												<div style="padding: 0px 8px 5px 8px;">
													<div style="display: -webkit-box !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; display: block; margin: 0 0 8px 0; font-weight: 600; color: rgba(0, 0, 0, .5); font-size: 14px; line-height: 16px;">
    													<?php echo htmlclean( $data_produtos['nome'] ); ?>
    												</div>
    												
    												<span class="apenas <?php if( $data_produtos['oferta'] != "1" ) { echo 'apenas-single'; }; ?>">
    													<?php if( has_variacao( $data_produtos['id'] ) ) { echo "Por apenas"; } else { echo "Por <br/> apenas"; }; ?>
    												</span>
    												
    												
    												<div style="display: flex; align-items: center;">
    												<div style="float: left; width: 75%;">
        												<span class="valor" style="text-align: left !important; margin: 0 !important; color: #67b017;">R$ <?php echo dinheiro( $valor_final, "BR" ); ?></span>
        												<?php if( $data_produtos['oferta'] == "1"  && $data_produtos['variacao_menor'] != 1 ) { ?>
        													<span class="valor_anterior"  style="text-align: left !important; margin: 0 !important; color:#FF0000">De: <?php echo dinheiro( $data_produtos['valor'], "BR" ); ?></span>
        												<?php } else{
        												    ?>
        												    <span class="valor_anterior"  style="text-align: left !important; margin: 0 !important; color: transparent"> </span>
        												    <?php
        												} ?>
        												
            											<div style="display: flex; align-items: center;">
                											<div class="mt-2">
                                                                            <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-shopping-basket"></i>
                                                                Vendas: <?php echo $data_produtos["total_vendas"];?> </label>
                                                            
                                                                                <label class="label label-estatisticas" style="font-weight: 510 !important; font-size: 13px; color: rgba(0, 0, 0, .5) !important;"><i class="lni lni-eye"></i>
                                                                Visualizações: <?php echo $data_produtos['visualizacoes']; ?></label>
                                                            </div>
        												</div>
    												</div>
    												<div style="float: right; width: 25%;">
                                                        <i class="lni lni-cart" style="float: right; display: block; font-size: 30px; color: #67b017;"></i>
                                                    </div>
    												</div>
												
												</div>
											
												
												
												
												
												<?php } else { ?>
												<div style="padding: 0px 8px 5px 8px;">
													<div style="display: block; margin: 0 0 8px 0; font-weight: 600; color: rgba(0, 0, 0, .5); font-size: 14px; line-height: 16px;">
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
												    <div class="detalhes" style="background-color:#C0C0C0 !important"><i class="lni lni-close"></i> <span>Sem Estoque</span></div>
												<?php } ?>

											</a>

										</div>

								</div>

							<?php } ?>

						</div>

					</div>

				</div>


		</div>

		<?php } else { ?>

		<div class="fillrelacionados visible-xs visible-sm"></div>

		<?php } ?>

	<?php } else { ?>

		<div class="middle">

			<div class="container">

				<div class="row">

					<span class="nulled nulled-content">Produto inválido ou inativo!</span>

				</div>

			</div>

		</div>

	<?php } ?>

</div>


<?php
// FOOTER
$system_footer .= "";
include($virtualpath . '/_layout/rdp.php');
include($virtualpath . '/_layout/footer.php');
?>

<script>
	$(".quantidadeProduto").change(function() {
		var token = "<?php echo session_id(); ?>";
		var produtoId = $(this).attr('sacola-produto-id');
		var modo = "estoque";
		var quantidade = $(this).find("input[name='quantidade']").val();
		var data = "quantidade=" + quantidade;
		var quantidadeField = $(this).find("#quantidade");

		$.post("<?php $app['url'] ?>/app/estabelecimento/_ajax/sacola.php", {
				token: token,
				modo: modo,
				data: data,
				produtoId: produtoId
			})
			.done(function(data) {
				data = $.parseJSON(data);
				if (data.error) {
					quantidadeField.val(parseInt(data.estoque));
					alert(data.message);
				}
			});
	});

	$(".quantidadeVariacao").change(function() {
		var token = "<?php echo session_id(); ?>";
		var produtoId = $(this).parent().parent().attr('produto-id');
		var variacaoId = $(this).parent().parent().attr('variacao-id');
		var variacaoProdutoId = $(this).parent().parent().attr('variacao-produto-id');
		var modo = "estoque-variavel";
		var quantidade = $(this).val();
		var data = "quantidade=" + quantidade;
		var quantidadeField = $(this);

		$.post("<?php $app['url'] ?>/app/estabelecimento/_ajax/sacola.php", {
				token: token,
				modo: modo,
				data: data,
				produtoId: produtoId,
				variacaoId: variacaoId,
				variacaoProdutoId: variacaoProdutoId
			})
			.done(function(data) {
				data = $.parseJSON(data);
				if (data.error) {
					quantidadeField.val(parseInt(data.estoque));
					ativachecks();
					alert(data.message);
				}
			});
	});

	$(document).on('change', '#the_form', function(e) {



		var total = parseFloat("<?php echo $valor_finalcerto; ?>");

		var vezes = parseFloat($("#quantidade").val());
		total = total * vezes;
		total = parseFloat(total).toFixed(2);

		$('.subtotal').html('<strong>Total:</strong> R$ ' + total);

	});

	$("#the_form").trigger("change");

	<?php if (has_variacao($data_content['id']) && $app['funcionalidade_variacao'] == "1") { ?>

		$(document).ready(function() {

			var form = $("#the_form");
			form.validate({
				focusInvalid: true,
				invalidHandler: function() {},
				errorPlacement: function errorPlacement(error, element) {
					element.closest(".line-variacao").find(".error-holder").html(error);
				},
				rules: {
					<?php
					$variacao = json_decode($data_content['variacao'], TRUE);
					$validacao = "";
					for ($x = 0; $x < count($variacao); $x++) {
						$validacao .= "variacao_" . $x . "_validacao: {\n";
						if (htmljson($variacao[$x]['escolha_minima']) >= 1) {
							$validacao .= "required: true,\n";
						}
						$validacao .= "min: " . htmljson($variacao[$x]['escolha_minima']) . ",\n";
						$validacao .= "max: " . htmljson($variacao[$x]['escolha_maxima']) . "\n";
						$validacao .= "},\n";
					}
					$validacao = trim($validacao, ",\n");
					echo $validacao;
					echo "\n";
					?>
				},
				messages: {
					<?php
					$variacao = json_decode($data_content['variacao'], TRUE);
					$validacao = "";
					for ($x = 0; $x < count($variacao); $x++) {
						$validacao .= "variacao_" . $x . "_validacao: {\n";
						if (htmljson($variacao[$x]['escolha_minima']) >= 1) {
							$validacao .= "required: 'Campo obrigatório',\n";
						}
						$validacao .= "min: 'Você deve escolher ao menos " . htmljson($variacao[$x]['escolha_minima']) . "',\n";
						$validacao .= "max: 'Você deve escolher no máximo " . htmljson($variacao[$x]['escolha_maxima']) . "'\n";
						$validacao .= "},\n";
					}
					$validacao = trim($validacao, ",\n");
					echo $validacao;
					echo "\n";
					?>
				}
			});

		});

		function ativachecks() {

			$(".line-variacao").each(function(index) {
				var contachecks = 0;
				var selecteds = "";				
				var max = parseInt($(this).attr("max"));

				$(this).find(".opcao.active").each(function(indexOpcao) {
					if ($(this).find('.quantidadeVariacao').length) {
						contachecks += parseInt($(this).find('.quantidadeVariacao').val());
						console.log("contachecks", contachecks);
					} else {
						contachecks++;
					}
					selecteds = selecteds + $(this).attr("variacao-item") + ",";
				});
				
								
    // 		if (contachecks > max) {
    //             alert("A quantidade total de variações selecionadas deve estar entre " + min + " e " + max + ".");
    //             return;
    //         }
				
				selecteds = selecteds.substring(0, selecteds.length - 1);
				$(this).find(".variacao_validacao").attr("value", contachecks);
				$(this).find(".variacao_escolha").attr("value", selecteds);
			});
			$("#the_form").trigger("change");
			$(".variacao_validacao").valid();

		}

		$(".val-variacao").click(function() {
			ativachecks();
			$("#the_form").trigger("change");
		});

		$(".opcoes .opcao .detalhes, .incrementar").click(function() {
			var opcaoParent = $(this).parent();

			var max = parseInt(opcaoParent.closest(".line-variacao").attr("max"));
			var parcial = parseInt(opcaoParent.closest(".line-variacao").find(".variacao_validacao").val());

			if (opcaoParent.hasClass("active")) {
				opcaoParent.removeClass("active");
			} else {
				if (parcial < max) {
					opcaoParent.addClass("active");
				} else {
					alert("Você já escolheu o maximo de opções permitidas, você deve remover alguma caso queira escolher uma nova!");
				}
			}
			ativachecks();
			$("#the_form").trigger("change");

		});

		$(".opcoes .opcao .check").click(function() {
			var opcaoParent = $(this).parent();

			var max = parseInt(opcaoParent.closest(".line-variacao").attr("max"));
			var parcial = parseInt(opcaoParent.closest(".line-variacao").find(".variacao_validacao").val());

			if (opcaoParent.hasClass("active")) {
				opcaoParent.removeClass("active");
			} else {
				if (parcial < max) {
					opcaoParent.addClass("active");
				} else {
					alert("Você já escolheu o maximo de opções permitidas, você deve remover alguma caso queira escolher uma nova!");
				}
			}
			ativachecks();
			$("#the_form").trigger("change");

		});

		$(document).on('change', '#the_form', function(e) {

			var total = parseFloat("<?php echo $valor_finalcerto; ?>");

			$(".opcao.active").each(function() {
				var adiciona = parseFloat($(this).attr("valor-adicional"));
				if ($(this).find('.quantidadeVariacao').length) {
					adiciona = adiciona * parseInt($(this).find('.quantidadeVariacao').val());
				}
				if (adiciona > 0) {
					total = total + adiciona;
				}
			});

			var vezes = parseFloat($("#quantidade").val());
			total = total * vezes;
			total = parseFloat(total).toFixed(2);

			$('.subtotal').html('<strong>Total:</strong> R$ ' + total);

		});

		$(document).ready(function() {

			$(".opcoes .opcao .check").each(function() {
				var alturacheck = $(this).parent().height();
				$(this).css("height", alturacheck);
			});

			ativachecks();

		});

	<?php } ?>

	$(".sacola-adicionar").click(function() {

		if ($('label.error:visible').length) {

			alert("Existem campos obrigatórios a serem preenchidos!");

			$('html, body').animate({
				scrollTop: $("label.error:visible").offset().top - 150
			}, 500);

		}

		<?php if (has_variacao($data_content['id']) && $app['funcionalidade_variacao'] == "1") { ?>
			if ($(".variacao_validacao").valid()) {
			<?php } ?>

			var eid = "<?php echo $app['id']; ?>";
			var former = "#the_form";
			var token = "<?php echo session_id(); ?>";
			var produto = "<?php echo $produto; ?>";
			var modo = "adicionar";
			var data = $("#the_form").serialize();

			$("#modalcarrinho .modal-body").html('<div class="adicionado"><i class="loadingicon lni lni-reload rotating"></i><div>');
			$.post("<?php echo $app['url']; ?>/app/estabelecimento/_ajax/sacola.php", {
					token: token,
					produto: produto,
					modo: modo,
					data: data
				})
				.done(function(data) {
					$("#modalcarrinho .modal-body").html(data);
				});
			$("#modalcarrinho").modal("show");
			sacola_count(eid, token);

			<?php if (has_variacao($data_content['id']) && $app['funcionalidade_variacao'] == "1") { ?>
			}
		<?php } ?>

	});
</script>

<script>
			$( ".campo-numero .decrementar-var" ).click(function() {

                    var valor = parseInt( $(this).siblings('input').val() );
                    var newvalor = Math.max(valor-1, 0);
                    
                    	$(this).siblings('input').val(newvalor);
                    
                    $(this).siblings('input').trigger("change");
                    ativachecks();
                                                                            
             });
			
</script>

<div class="releaseclick"></div>