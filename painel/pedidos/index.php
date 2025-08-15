<?php



// CORE



include('../../_core/_includes/config.php');



// RESTRICT



restrict_estabelecimento();



restrict_expirado();



// SEO



$seo_subtitle = "Pedidos";



$seo_description = "";



$seo_keywords = "";



// HEADER



$system_header .= "";



include('../_layout/head.php');



include('../_layout/top.php');



include('../_layout/sidebars.php');



include('../_layout/modal.php');



?>



<style>

/* Estilos para a coluna Garçom */

.badge-garcom {

    background-color: #28a745 !important;

    color: white !important;

    padding: 4px 8px;

    border-radius: 12px;

    font-size: 0.85em;

    font-weight: 500;

}



.mesa-info {

    font-size: 0.75em;

    color: #666;

    margin-top: 2px;

}



.delivery-info {

    color: #999;

    font-style: italic;

    font-size: 0.9em;

}



/* Botão específico para impressão de mesa */

.mesa-print-btn {

    background-color: #007bff !important;

    color: white !important;

    padding: 8px !important;

    border-radius: 4px !important;

    margin-left: 2px !important;

}



.mesa-print-btn:hover {

    background-color: #0056b3 !important;

}



/* Responsividade para a nova coluna */

@media (max-width: 768px) {

    .table-pedidos th:nth-child(5),

    .table-pedidos td:nth-child(5) {

        display: none;

    }

}

</style>









<?php if( $_GET['msg'] == "aceito" ) {



    



$nome = $_GET['nome'];



$whats = $_GET['whats'];



   



$msg4="Olá, ".strtoupper($nome)."\n";



$msg4.="\n";



$msg4.="*Seu pedido foi aceito*\n";



$msg4;



$text4 = urlencode($msg4);







$url = "https://api.whatsapp.com/send?phone=55".$whats."&text=".$text4."";







?>







<script type="text/javascript">window.open('<?php print $url;?>', '_blank');</script>





<?php



}



?>







<?php if( $_GET['msg'] == "finalizado" ) {



    



$nome = $_GET['nome'];



$whats = $_GET['whats'];



 $tipo = $_GET['tipo'];



$msg4="Olá, ".strtoupper($nome)."\n";



$msg4.="\n";



if ($tipo == "retirada") {

  $msg4 = "*Seu pedido foi finalizado. Agradecemos a sua preferência.*\n";

} else {



  $msg4.="*Seu pedido foi entregue. Agradecemos a sua preferência.*\n";

}





$msg4;



$text4 = urlencode($msg4);







$url = "https://api.whatsapp.com/send?phone=55".$whats."&text=".$text4."";







?>







<script type="text/javascript">window.open('<?php print $url;?>', '_blank');</script>







<?php



}



?>







<?php if( $_GET['msg'] == "excluir" ) {



    



$nome = $_GET['nome'];



$whats = $_GET['whats'];



 $tipo = $_GET['tipo'];



$msg4="Olá, ".strtoupper($nome)."\n";



$msg4.="\n";





  $msg4.="*Precisamos cancelar o seu pedido* ❌\n";

  

  

$msg4;



$text4 = urlencode($msg4);







$url = "https://api.whatsapp.com/send?phone=55".$whats."&text=".$text4."";







?>







<script type="text/javascript">window.open('<?php print $url;?>', '_blank');</script>







<?php



}



?>









<?php if( $_GET['msg'] == "disponivel" ) {



    



$nome = $_GET['nome'];



$whats = $_GET['whats'];



 $tipo = $_GET['tipo'];



$msg4="Olá, ".strtoupper($nome)."\n";



$msg4.="\n";



if ($tipo == "retirada") {

  $msg4 = "*Seu pedido está disponível para retirada.* 🏪\n";

} else {



    $msg4 = "*Seu pedido saiu para a entrega.* 🛵\n";

}





$msg4;



$text4 = urlencode($msg4);







$url = "https://api.whatsapp.com/send?phone=55".$whats."&text=".$text4."";







?>







<script type="text/javascript">window.open('<?php print $url;?>', '_blank');</script>







<?php



}



?>













<?php if( $_GET['msg'] == "reembolsado" ) {



    



$nome = $_GET['n'];



$whats = $_GET['whats'];



$pedido = $_GET['pedido'];

   



$msg4="Olá, ".strtoupper($nome)."\n,";



$msg4.="\n";



$msg4.="Seu pagamento referente ao pedido *N° ".$pedido."* foi *reembolsado*.\n";



$msg4;



$text4 = urlencode($msg4);







$url = "https://api.whatsapp.com/send?phone=55".$whats."&text=".$text4."";







?>







<script type="text/javascript">window.open('<?php print $url;?>', '_blank');</script>







<?php



}



?>

















































<!-- 



<meta http-equiv="refresh" content="60;URL=./" /> -->































<?php







global $db_con;



$eid = $_SESSION['estabelecimento']['id'];







// Variables







$estabelecimento = mysqli_real_escape_string( $db_con, $_GET['estabelecimento_id'] );



$numero = mysqli_real_escape_string( $db_con, $_GET['numero'] );



$nome = mysqli_real_escape_string( $db_con, $_GET['nome'] );



$status = mysqli_real_escape_string( $db_con, $_GET['status'] );



$cupom = mysqli_real_escape_string( $db_con, $_GET['cupom'] );









$getdata = "";







foreach($_GET as $query_string_variable => $value) {



  if( $query_string_variable != "pagina" ) {



    $getdata .= "&$query_string_variable=".htmlclean($value);



  }



}







// Config







$limite = 20;



$pagina = $_GET["pagina"] == "" ? 1 : $_GET["pagina"];



$inicio = ($pagina * $limite) - $limite;







// Query







$query = "SELECT p.*, g.nome as garcom_nome, m.numero as mesa_numero FROM pedidos p ";

$query .= "LEFT JOIN garcons g ON p.rel_garcons_id = g.id ";

$query .= "LEFT JOIN mesas m ON p.rel_mesas_id = m.id ";







$query .= "WHERE 1=1 ";



// Aplicar filtros se estiverem definidos

if( $numero ) {

  $query .= "AND p.id = '$numero' ";

}



if( $nome ) {

  $query .= "AND p.nome LIKE '%$nome%' ";

}



if( $status && $status != 9 ) {

  $query .= "AND p.status = '$status' ";

}



if( $cupom ) {

  $query .= "AND p.cupom = '$cupom' ";

}



// Configuração de datas

$data_inicial = mysqli_real_escape_string( $db_con, $_GET['data_inicial'] );

$data_final = mysqli_real_escape_string( $db_con, $_GET['data_final'] );



if (isset($_GET['filtered'])) {

  if( !$data_inicial ) { $data_inicial = date("d/m/").(date("Y")-1); }

  if( !$data_final ) { $data_final = date("d/m/Y"); }

}



if( $data_inicial ) {

  $data_inicial_sql = datausa_min( $data_inicial );

  $data_inicial_sql = $data_inicial_sql." 00:00:00";

}



if( $data_final ) {

  $data_final_sql = datausa_min( $data_final );

  $data_final_sql = $data_final_sql." 23:59:59";

}



if( $data_inicial && $data_final ) {

  $query .= "AND (p.data_hora > '$data_inicial_sql' AND p.data_hora < '$data_final_sql') ";

}



$query .= "AND p.rel_estabelecimentos_id = '$eid' ";







$query_full = $query;







$query .= "ORDER BY p.id DESC LIMIT $inicio,$limite";







// Run



// Debug temporário - remover depois

// echo "<!-- Debug Query: " . $query . " -->";



$sql = mysqli_query( $db_con, $query );



// Verificar se houve erro na consulta

if (!$sql) {

    echo "<!-- Erro SQL: " . mysqli_error($db_con) . " -->";

}







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









<div id="modalwhats" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal">×</button>

    <h4 class="modal-title">Observação para o motoboy (será anexada ao comprovante)</h4>

      </div>

      <div class="modal-body">

        <textarea id="userMessage" rows="4" cols="50"></textarea>

      </div>

      <div class="modal-footer">

      <a href="#" class="btn btn-default" style="background-color: black; color: white;" data-dismiss="modal">Fechar</a>

        <a href="#" id="whatsappButton" class="btn btn-success">Enviar <i class="lni lni-whatsapp"></i></a>

      </div>

    </div>

  </div>

</div>





<!-- Modal para Impressão de Mesa -->

<div id="modalMesa" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal">×</button>

        <h4 class="modal-title">🍽️ Impressão de Mesa - Finalizar Pedido</h4>

      </div>

      <div class="modal-body">

        <div class="row">

          <div class="col-md-6">

            <div class="form-group">

              <label><strong>Cliente:</strong></label>

              <p id="modalClienteNome" class="form-control-static"></p>

            </div>

            

            <div class="form-group">

              <label for="formaPagamentoMesa"><strong>Forma de Pagamento:</strong></label>

              <select id="formaPagamentoMesa" class="form-control" required>

                <option value="">Selecione...</option>

                <option value="Dinheiro">💵 Dinheiro</option>

                <option value="Cartão de Débito">💳 Cartão de Débito</option>

                <option value="Cartão de Crédito">💳 Cartão de Crédito</option>

                <option value="PIX">📱 PIX</option>

                <option value="Vale Refeição">🎫 Vale Refeição</option>

                <option value="Conta Corrente">🏦 Conta Corrente</option>

              </select>

            </div>

            <!--pedidos_listar-->
            <div class="form-group">
              <label for="pedidos"><strong>Pedido(s):</strong></label>
              <div id="pedidosListar">

              </div>
            </div>

          </div>

          

          

          <div class="col-md-6">

            <div class="form-group">

              <label><strong>Subtotal:</strong></label>

              <p id="modalSubtotal" class="form-control-static"></p>

            </div>

            

            <div class="form-group">

              <label for="taxaGarcom"><strong>Taxa do Garçom (%):</strong></label>

              <div class="input-group">

                <input type="number" id="taxaGarcom" class="form-control" value="10" min="0" max="30" step="0.5">

                <div class="input-group-addon">%</div>

              </div>

              <small class="help-block">Taxa padrão: 10% - Você pode ajustar de 0% a 30%</small>

            </div>

            

            <div class="form-group">

              <label><strong>Valor da Taxa:</strong></label>

              <p id="valorTaxa" class="form-control-static text-success"><strong>R$ 0,00</strong></p>

            </div>

            

            <div class="form-group">

              <label><strong>Total Final:</strong></label>

              <p id="totalFinal" class="form-control-static text-primary"><strong>R$ 0,00</strong></p>

            </div>

          </div>

        </div>

        

        <div class="alert alert-info">

          <i class="lni lni-information"></i>

          <strong>Informação:</strong> A taxa do garçom será adicionada ao valor total e destacada no comprovante.

        </div>

      </div>

      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">

          <i class="lni lni-close"></i> Cancelar

        </button>

        <button type="button" id="btnImprimirMesa" class="btn btn-primary">

          <i class="lni lni-printer"></i> Imprimir Comprovante

        </button>

      </div>

    </div>

  </div>

</div>





<div class="middle minfit bg-gray">







  <div class="container">







    <div class="row">







      <div class="col-md-12">







        <div class="title-icon pull-left">



          <i class="lni lni-ticket-alt"></i>



          <span>Pedidos</span>



        </div>







        <div class="bread-box pull-right">



          <div class="bread">



            <a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>



            <span>/</span>



            <a href="<?php panel_url(); ?>/pedidos">Pedidos</a>



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



                    <div class="col-md-4 col-xs-6 col-sm-6">



                      <div class="form-field-default">



                        <label>Nº:</label>



                        <input type="text" name="numero" placeholder="Nº" value="<?php echo htmlclean( $numero ); ?>"/>



                      </div>



                    </div>



                    <div class="col-md-4 col-xs-6 col-sm-6">



                            <div class="form-field-default">



                            <div class="clear"></div>



                             <label>Status:</label>



                      <div class="fake-select">



                        <i class="lni lni-chevron-down"></i>



                        <select name="status">



                          <option></option>



                                                <?php for( $x = 0; $x < count( $numeric_data['status_pedido'] ); $x++ ) {

                                                ?>



                                                <option value="<?php echo $numeric_data['status_pedido'][$x]['value']; ?>" <?php if( $_GET['status'] == $numeric_data['status_pedido'][$x]['value'] ) { echo 'SELECTED'; }; ?>><?php echo $numeric_data['status_pedido'][$x]['name']; ?></option>



                                                <?php } ?>



                        </select>



                      </div>



                            </div>



                    </div>



                    <div class="clear visible-xs visible-sm"></div>



                    <div class="col-md-4">



                      <div class="form-field-default">



                        <label>Nome do cliente:</label>



                        <input type="text" name="nome" placeholder="Nome" value="<?php echo htmlclean( $nome ); ?>"/>



                      </div>



                    </div>



                    <div class="clear visible-xs visible-sm"></div>



                    <div class="col-md-3 half-left col-sm-6 col-xs-6">



                      <div class="form-field-default">



                        <label>Data inicial:</label>



                        <input class="maskdate datepicker" type="text" name="data_inicial" placeholder="Data inicial" value="<?php echo htmlclean( $data_inicial ); ?>"/>



                      </div>



                    </div>



                    <div class="col-md-3 half-right col-sm-6 col-xs-6">



                      <div class="form-field-default">



                        <label>Data final:</label>



                        <input class="maskdate datepicker" type="text" name="data_final" placeholder="Data inicial" value="<?php echo htmlclean( $data_final ); ?>"/>



                      </div>



                    </div>



                    <div class="clear visible-xs visible-sm"></div>



                    <div class="col-md-3 half-left col-sm-12 col-xs-12">



                      <div class="form-field-default">



                        <label>Cupom:</label>



                        <input type="text" name="cupom" placeholder="Cupom" value="<?php echo htmlclean( $cupom ); ?>"/>



                      </div>



                    </div>



                    <div class="clear visible-xs visible-sm"></div>



                    <div class="col-md-3">



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



                        <a href="<?php panel_url(); ?>/pedidos" class="limpafiltros"><i class="lni lni-close"></i> Limpar filtros</a>



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



      </div>







      <div class="row">







        <div class="col-md-12">







          <table class="listing-table fake-table clean-table table-pedidos">



            <thead>



              <th>Nº</th>



              <th>Nome</th>



              <th>Whatsapp</th>



              <th>Status</th>



              <th>Garçom</th>



              <th>Data/Hora</th>



              <th></th>



            </thead>



            <tbody>







              <?php



                            while ( $data = mysqli_fetch_array( $sql ) ) {

                                

                                    // REMOVE OS PEDIDOS EXCLUIDOS DA LISTA E SÓ OS LISTA SE NA FILTRAGEM O STATUS ESTIVER COMO EXCLUIDO

                                    if ($data['status'] == 9 && $_GET['status'] != 9) {

                                        continue;

                                    }



                            ?>







              <tr class="fullwidth">



                <td>



                                    <span class="fake-table-title hidden-xs hidden-sm">Nº</span>



                                    <div class="fake-table-data"><span class="pedido-numero">#<?php echo $data['id']; ?></span></div>



                                    <div class="fake-table-break"></div>



                </td>



                <td>



                                    <span class="fake-table-title hidden-xs hidden-sm">Nome</span>



                                    <div class="fake-table-data"><?php echo htmlclean( $data['nome'] ); ?></div>



                                    <div class="fake-table-break"></div>



                </td>



                <td class="hidden-xs hidden-sm">



                                    <span class="fake-table-title">Whatsapp</span>



                                    <a href="https://api.whatsapp.com/send?phone=<?php echo  preg_replace('/\D/', '', formato( $data['whatsapp'], "whatsapp" )); ?>" target="_blank">

                                        <div class="fake-table-data colored">

                                            

                                            <?php echo formato( $data['whatsapp'], "whatsapp" ); ?>

                                        </div>

                                    </a>





                                    <div class="fake-table-break"></div>



                </td>

                

                

                <td>



                                    <span class="fake-table-title hidden-xs hidden-sm">Status</span>



                                    <div class="fake-table-data">



                                      <?php if( $data['status'] == "1" ) { ?>



                                          <script type="text/javascript">var audio=new Audio('../_layout/campainha.mp3');audio.addEventListener('canplaythrough',function(){audio.play();});</script>



                                        <span class="badge badge-pendente">Pendente</span>



                                      <?php } ?>



                                      <?php if( $data['status'] == "2" ) { ?>



                                        <span class="badge badge-concluido">Concluído</span>



                                      <?php } ?>



                                      <?php if( $data['status'] == "3" ) { ?>



                                        <span class="badge badge-cancelado">Cancelado</span>



                                      <?php } ?>



                                      <?php if( $data['status'] == "4" ) { ?>



                                        <span class="badge badge-cancelado">Aceito</span>



                                      <?php } ?>



                                      <?php if( $data['status'] == "5" ) { ?>



                                        <span class="badge badge-cancelado">Para Entrega</span>



                                      <?php } ?>



                                      <?php if( $data['status'] == "6" ) { ?>



                                        <span class="badge badge-cancelado">Disponível p/ Retirada</span>



                                      <?php } ?>



                    <?php if( $data['status'] == "7" ) { ?>



                      <span class="badge " style="background-color: #ff0000d9;">Reembolsado</span>



                    <?php } ?>



                    <?php if( $data['status'] == "8" ) { ?>



                      <span class="badge " style="background-color: blue;">Pago</span>



                    <?php } ?>

                    

                    <?php if( $data['status'] == "9" ) { ?>



                      <span class="badge " style="background-color: red;">Excluído</span>



                    <?php } ?>







                                    </div>



                                    <div class="fake-table-break"></div>



                </td>



                <td>



                                    <span class="fake-table-title hidden-xs hidden-sm">Garçom</span>



                                    <div class="fake-table-data">

                                        <?php if( $data['garcom_nome'] ) { ?>

                                            <span class="badge badge-garcom">

                                                <?php echo htmlclean($data['garcom_nome']); ?>

                                            </span>

                                            <?php if( $data['mesa_numero'] ) { ?>

                                                <div class="mesa-info">Mesa <?php echo $data['mesa_numero']; ?></div>

                                            <?php } ?>

                                        <?php } else { ?>

                                            <span class="delivery-info">Delivery</span>

                                        <?php } ?>

                                    </div>



                                    <div class="fake-table-break"></div>



                </td>



                <td>



                                    <span class="fake-table-title hidden-xs hidden-sm">Data/Hora</span>



                                    <div class="fake-table-data"><?php echo databr( $data['data_hora'] ); ?></div>



                                    <div class="fake-table-break"></div>



                </td>



                <td>



                  <span class="fake-table-title hidden-xs hidden-sm">Ações</span>



                                    <div class="fake-table-data">



                    <div class="form-actions pull-right">



                        <a class="color-green" href = "<?php panel_url(); ?>/pedidos/aceitar/?id=<?php echo $data['id']; ?>&nome=<?php echo $data['nome']; ?>&whats=<?php echo $data['whatsapp']; ?>" title="Aceitar"><i class="lni lni-checkmark"></i></a>



                       <a class="color-red" href = "<?php panel_url(); ?>/pedidos/disponivel/?id=<?php echo $data['id']; ?>&nome=<?php echo $data['nome']; ?>&whats=<?php echo $data['whatsapp']; ?>" title="Saiu para entrega"><i class="lni lni-bi-cycle"></i></a>

                       <a class="color-yellow" href = "<?php panel_url(); ?>/pedidos/?msg=motoboy&id=<?php echo $data['id']; ?>" title="Mensagem ao motoboy"><i class="lni lni-bi-cycle"></i></a>

                      



                      



                      <a class="color-white" href = "<?php panel_url(); ?>/pedidos/concluir/?id=<?php echo $data['id']; ?>&nome=<?php echo $data['nome']; ?>&whats=<?php echo $data['whatsapp']; ?>" title="Concluir Pedido"><i class="lni lni-pointer-right"></i></a>



                      <a class="color-white" href = "<?php panel_url(); ?>/pedidos/reembolsar/?id=<?php echo $data['id']; ?>&nome=<?php echo $data['nome']; ?>&whats=<?php echo $data['whatsapp']; ?>" title="Reembolsar"><i class="lni lni-share-alt"></i></a>





                      



                      <a target="_blank" class="color-white" href="<?php panel_url(); ?>/pedidos/imprimir?id=<?php echo $data['id']; ?>" title="Imprimir"><i class="lni lni-printer"></i></a>



                      <?php if( $data['garcom_nome'] && $data['mesa_numero'] ) { ?>

<a class="color-blue mesa-print-btn" href="javascript:void(0);" 

   data-pedido-id="<?php echo $data['id']; ?>"
   
   data-mesa-id="<?php echo $data['rel_mesas_id']; ?>"

   data-cliente-nome="<?php echo htmlspecialchars($data['nome']); ?>" 

   data-subtotal="<?php echo (isset($data['total']) && is_numeric($data['total'])) ? $data['total'] : '0.00'; ?>" 

   title="Imprimir Mesa">

   <i class="lni lni-restaurant"></i>

</a>

                      <?php } ?>



                      <a class="color-yellow" href="<?php panel_url(); ?>/pedidos/editar?id=<?php echo $data['id']; ?>" title="Editar"><i class="lni lni-magnifier"></i></a>

                      

                      <a class="color-red" onclick="if(confirm('Tem certeza que deseja cancelar este pedido?')) document.location = '<?php panel_url(); ?>/pedidos/excluir/?id=<?php echo $data['id']; ?>&nome=<?php echo $data['nome']; ?>&whats=<?php echo $data['whatsapp']; ?>'" title="Excluir Pedido"><i class="lni lni-trash"></i></a>

                      

                      <!-- Antigo botão de exclusão -->

                      <!-- <a class="color-red" onclick="if(confirm('Tem certeza que deseja cancelar este pedido?')) document.location = '<?php panel_url(); ?>/pedidos/deletar/?id=<?php echo $data['id']; ?>'" href="#" title="Excluir"><i class="lni lni-trash"></i></a> -->



                    </div>



                                    </div>



                                    <div class="fake-table-break"></div>



                </td>



              </tr>







                            <?php } ?>







                            <?php if( $total_results == 0 ) { ?>







                               <tr class="fullwidth">



                                <td colspan="7">



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



            $paginationpath = "pedidos";



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





// === DEBUG: Sistema de Modal de Mesa ===

console.log('=== TESTE DO SISTEMA DE MESA ===');



// Função de teste global

window.testeModalMesa = function() {

    console.log('Iniciando teste do modal de mesa...');

    

    // Testar se elementos existem

    const botoes = document.querySelectorAll('.mesa-print-btn');

    console.log('Botões de mesa encontrados:', botoes.length);

    

    const modal = document.getElementById('modalMesa');

    console.log('Modal encontrado:', !!modal);

    

    if (modal) {

        console.log('Tentando abrir modal diretamente...');

        if (typeof $ !== 'undefined' && $.fn.modal) {

            $('#modalMesa').modal('show');

        } else {

            modal.style.display = 'block';

            modal.classList.add('show');

        }

    }

    

    // Testar função abrirModalMesa

    if (typeof abrirModalMesa === 'function') {

        console.log('Testando função abrirModalMesa...');

        //abrirModalMesa(999, 'Cliente Teste', 50.00);

        abrirModalMesa()

    } else {

        console.error('Função abrirModalMesa não encontrada!');

    }

};



// Auto-executar diagnóstico em 3 segundos

setTimeout(function() {

    console.log('=== DIAGNÓSTICO AUTOMÁTICO ===');

    const botoes = document.querySelectorAll('.mesa-print-btn');

    const modal = document.getElementById('modalMesa');

    

    console.log('Botões encontrados:', botoes.length);

    console.log('Modal existe:', !!modal);

    console.log('jQuery disponível:', typeof $ !== 'undefined');

    console.log('Bootstrap modal disponível:', typeof $.fn.modal !== 'undefined');

    console.log('Função abrirModalMesa:', typeof abrirModalMesa);

    

    if (botoes.length > 0) {

        console.log('Primeiro botão onclick:', botoes[0].getAttribute('onclick'));

    }

}, 3000);





// Funções para o Modal de Mesa

let dadosPedidoMesa = {};



function abrirModalMesa(pedidoId, clienteNome, subtotal) {

    console.log('Abrindo modal mesa:', pedidoId, clienteNome, subtotal);



    // Buscar subtotal do pedido de mesa via AJAX

    $.ajax({

        url: '<?php panel_url(); ?>/pedidos/buscar_mesa_subtotal.php',

        method: 'GET',

        data: { id: pedidoId },

        dataType: 'json',

        success: function(response) {

          let htmlItens = '';
            let valorSubtotal = 0;

            if (response && response.sucesso && response.itens) {

                response.itens.forEach(function(item) {
                    htmlItens += `
                        <p>
                            ${item.quantidade}x ${item.nome_produto} - 
                            R$ ${item.preco_unitario} (R$ ${item.subtotal_item})
                        </p>
                    `;
                });
                valorSubtotal = parseFloat(response.subtotal) || 0;

            } else {
                htmlItens = '<p>Nenhum item encontrado</p>';
                valorSubtotal = parseFloat(subtotal) || 0;
            }

            // Insere os itens na div
            $('#pedidosListar').html(htmlItens);

            dadosPedidoMesa = {

                id: pedidoId,

                nome: clienteNome,

                subtotal: valorSubtotal

            };



            // Verificar se o modal existe

            const modal = document.getElementById('modalMesa');

            if (!modal) {

                console.error('Modal não encontrado!');

                alert('Erro: Modal não encontrado. Abrindo impressão direta.');

                // Fallback - abrir diretamente

                const url = '<?php panel_url(); ?>/pedidos/imprimir_mesa?id=' + pedidoId + '&forma_pagamento=Dinheiro&taxa_garcom=10';

                window.open(url, '_blank');

                return;

            }



            // Preencher dados no modal

            const nomeElement = document.getElementById('modalClienteNome');

            const subtotalElement = document.getElementById('modalSubtotal');



            if (nomeElement) nomeElement.textContent = clienteNome;

            if (subtotalElement) subtotalElement.textContent = 'R$ ' + dadosPedidoMesa.subtotal.toFixed(2).replace('.', ',');



            // Calcular valores iniciais

            calcularTotalMesa();



            // Mostrar modal

            try {

                if (typeof $ !== 'undefined' && $.fn.modal) {

                    $('#modalMesa').modal('show');

                } else {

                    // Fallback sem jQuery

                    modal.style.display = 'block';

                    modal.classList.add('show');

                }

            } catch (error) {

                console.error('Erro ao abrir modal:', error);

                alert('Erro ao abrir modal. Verifique o console.');

            }

        },

        error: function() {

            // Se der erro, usa o valor passado

            dadosPedidoMesa = {

                id: pedidoId,

                nome: clienteNome,

                subtotal: parseFloat(subtotal) || 0

            };

            const modal = document.getElementById('modalMesa');

            if (!modal) {

                alert('Erro: Modal não encontrado. Abrindo impressão direta.');

                const url = '<?php panel_url(); ?>/pedidos/imprimir_mesa?id=' + pedidoId + '&forma_pagamento=Dinheiro&taxa_garcom=10';

                window.open(url, '_blank');

                return;

            }

            const nomeElement = document.getElementById('modalClienteNome');

            const subtotalElement = document.getElementById('modalSubtotal');

            if (nomeElement) nomeElement.textContent = clienteNome;

            if (subtotalElement) subtotalElement.textContent = 'R$ ' + dadosPedidoMesa.subtotal.toFixed(2).replace('.', ',');

            calcularTotalMesa();

            try {

                if (typeof $ !== 'undefined' && $.fn.modal) {

                    $('#modalMesa').modal('show');

                } else {

                    modal.style.display = 'block';

                    modal.classList.add('show');

                }

            } catch (error) {
                $('#pedidosListar').html('<p>Erro ao carregar itens</p>');
                alert('Erro ao abrir modal. Verifique o console.');

            }

        }

    });

}



function calcularTotalMesa() {

    try {

        const subtotal = dadosPedidoMesa.subtotal || 0;

        const taxaElement = document.getElementById('taxaGarcom');

        const valorTaxaElement = document.getElementById('valorTaxa');

        const totalFinalElement = document.getElementById('totalFinal');

        

        if (!taxaElement || !valorTaxaElement || !totalFinalElement) {

            console.error('Elementos de cálculo não encontrados');

            return;

        }

        

        const taxaPercent = parseFloat(taxaElement.value) || 0;

        const valorTaxa = subtotal * (taxaPercent / 100);

        const totalFinal = subtotal + valorTaxa;

        

        valorTaxaElement.innerHTML = '<strong>R$ ' + valorTaxa.toFixed(2).replace('.', ',') + '</strong>';

        totalFinalElement.innerHTML = '<strong>R$ ' + totalFinal.toFixed(2).replace('.', ',') + '</strong>';

        

        console.log('Cálculo realizado:', {subtotal, taxaPercent, valorTaxa, totalFinal});

    } catch (error) {

        console.error('Erro no cálculo:', error);

    }

}



// Event listeners para o modal de mesa

$(document).ready(function() {

    console.log('Documento pronto - inicializando modal de mesa');
  
    

    // Verificar se jQuery e Bootstrap estão disponíveis

    if (typeof $ === 'undefined') {

        console.error('jQuery não está carregado!');

        return;

    }

    

    if (typeof $.fn.modal === 'undefined') {

        console.error('Bootstrap modal não está disponível!');

        return;

    }

    

    // Event listener para botões de mesa usando delegação de eventos

    $(document).on('click', '.mesa-print-btn', function(e) {

        e.preventDefault();

        e.stopPropagation();

        

        console.log('Botão mesa clicado!');


        const pedidoId = $(this).data('pedido-id');

        const clienteNome = $(this).data('cliente-nome');

        const subtotal = $(this).data('subtotal');

        

        console.log('Dados extraídos:', {pedidoId, clienteNome, subtotal});

        

        if (pedidoId && clienteNome && subtotal) {

            abrirModalMesa(pedidoId, clienteNome, subtotal);

        } else {

            console.error('Dados incompletos para abrir modal');

            alert('Erro: Dados incompletos para abrir modal de mesa');

        }

    });

    

    // Atualizar cálculos quando taxa mudar

    $('#taxaGarcom').on('input', function() {

        console.log('Taxa alterada:', this.value);

        calcularTotalMesa();

    });

    

    // Botão de imprimir mesa

    $('#btnImprimirMesa').click(function() {

        console.log('Botão imprimir mesa clicado');


        const formaPagamento = document.getElementById('formaPagamentoMesa').value;

        const taxaGarcom = document.getElementById('taxaGarcom').value;

        

        console.log('Forma pagamento:', formaPagamento);

        console.log('Taxa garçom:', taxaGarcom);

        

        if (!formaPagamento) {

            alert('Por favor, selecione a forma de pagamento!');

            return;

        }
        

        // Construir URL para impressão

        const url = '<?php panel_url(); ?>/pedidos/imprimir_mesa?id=' + dadosPedidoMesa.id + 

                   '&forma_pagamento=' + encodeURIComponent(formaPagamento) + 

                   '&taxa_garcom=' + taxaGarcom;

        

        console.log('URL de impressão:', url);

        

        // Abrir impressão em nova janela

        window.open(url, '_blank');

        

        // Fechar modal

        $('#modalMesa').modal('hide');

    });

    
    

    // Teste do modal

    window.testarModal = function() {

        console.log('Testando modal...');

        $('#modalMesa').modal('show');

    };

});





</script>









<?php

if ($_GET['msg'] == "motoboy") {

    $type = $_GET['msg'];

    $id = $_GET['id'];



    // Busca o pedido no banco de dados

    $pedidoQuery = mysqli_query($db_con, "SELECT * FROM pedidos WHERE id = '$id'");

    $pedido = mysqli_fetch_array($pedidoQuery);



    // Monta o endereço concatenando as colunas do banco

    $rua = $pedido['endereco_rua'] ?? '';

    $numero = $pedido['endereco_numero'] ?? '';

    $bairro = $pedido['endereco_bairro'] ?? '';

    $cidade = $pedido['cidade'] ?? ''; // Certifique-se de que esta coluna contém o nome da cidade

    $estado = $pedido['estado'] ?? ''; // Certifique-se de que esta coluna contém o estado

    $cep = $pedido['endereco_cep'] ?? '';



    // Verifica se os dados estão completos

    if (!empty($rua) && !empty($numero) && !empty($cidade) && !empty($estado)) {

        $endereco = "$rua, $numero, $bairro, $cidade, $estado, $cep";

        $enderecoFormatado = urlencode($endereco); // Formata para o Google Maps

        $googleMapsLink = "https://www.google.com/maps/search/?api=1&query=$enderecoFormatado";

    } else {

        $googleMapsLink = "Endereço não disponível. Verifique os dados do pedido.";

    }



    // Monta o comprovante

    $comprovante = urlencode($pedido['comprovante'] ?? 'Informação indisponível');



    // Gera o script com os dados

    echo '<script>';

    echo 'var predefinedMessage = decodeURIComponent("' . $comprovante . '").replace(/\\+/g, " ");';

    echo 'var googleMapsLink = "' . $googleMapsLink . '";';

    echo '$(document).ready(function() {';

    echo '    $("#modalwhats").modal("show");';

    echo '    $("#whatsappButton").on("click", function() {';

    echo '        var userMessage = $("#userMessage").val();';

    echo '        var whatsappMessage = "*Observação:* " + userMessage + "\\n\\nComprovante\\n\\n" + predefinedMessage + "\\n\\n*Endereço no Google Maps:* " + googleMapsLink;';

    echo '        window.open("https://api.whatsapp.com/send?text=" + encodeURIComponent(whatsappMessage), "_blank");';

    echo '    });';

    echo '});';

    echo '</script>';

}

?>















<!--funcionando sem a do moto boy-->

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel de Pedidos</title>

    <script>

        function verificarUrlEAtualizar() {

            const urlAtual = window.location.pathname;

            const urlDesejada = '/painel/pedidos/';



            // Se a URL atual for a desejada, ativa a atualização automática

            if (urlAtual === urlDesejada) {

                // Atualiza a página a cada 2 minutos

                setTimeout(() => {

                    location.reload();

                }, 120000); // 120000 milissegundos = 2 minutos

            } else {

                // Se a URL não for a desejada, redireciona de volta

                window.location.href = urlDesejada;

            }

        }



        // Executa a verificação ao carregar a página

        window.addEventListener('load', verificarUrlEAtualizar);



        // Função para abrir links em nova guia

        function abrirNovaGuia(url) {

            window.open(url, '_blank'); // Abre a URL em uma nova guia

        }

    </script>

</head>

<body>

   <!-- <h1>Painel de Pedidos</h1>

    <button onclick="abrirNovaGuia('/outra-pagina');">Abrir Outra Página</button>

    <button onclick="abrirNovaGuia('/outra-pagina2');">Abrir Outra Página 2</button>

    <!-- Adicione mais botões conforme necessário -->

</body>

</html>