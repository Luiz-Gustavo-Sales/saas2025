<?php

// CORE

include('../../../_core/_includes/config.php');

session_start();

// RESTRICT

restrict_estabelecimento();

// SEO

$seo_subtitle = "Editar pedido";

$seo_description = "";

$seo_keywords = "";

// HEADER

$system_header .= "";

include('../../_layout/head.php');

include('../../_layout/top.php');

include('../../_layout/sidebars.php');

include('../../_layout/modal.php');

?>

<?php

  // Globals

  $eid = $_SESSION['estabelecimento']['id'];

  $id = mysqli_real_escape_string( $db_con, $_GET['id'] );

  $edit = mysqli_query( $db_con, "SELECT * FROM pedidos WHERE id = '$id' AND rel_estabelecimentos_id = '$eid' LIMIT 1");

  $hasdata = mysqli_num_rows( $edit );

  $data = mysqli_fetch_array( $edit );

  // Checar se formulário foi executado

  $formdata = $_POST['formdata'];

  if( $formdata ) {

    // Debug: log dos dados recebidos
    error_log("Form data recebido: " . print_r($_POST, true));

    // Setar campos

    $nome = mysqli_real_escape_string( $db_con, $_POST['nome'] );

    $email = mysqli_real_escape_string( $db_con, $_POST['email'] );

    $whatsapp = mysqli_real_escape_string( $db_con, $_POST['whatsapp'] );

    $endereco = mysqli_real_escape_string( $db_con, $_POST['endereco'] );

    $status = mysqli_real_escape_string( $db_con, $_POST['status'] );

    $observacoes = mysqli_real_escape_string( $db_con, $_POST['observacoes'] );

    // Debug: verificar valores após sanitização
    error_log("Valores após sanitização - Nome: $nome, Status: $status, EID: $eid");

    // Checar Erros

    $checkerrors = 0;

    $errormessage = array();

      // -- Estabelecimento

      if( !$eid ) {

        $checkerrors++;

        $errormessage[] = "O estabelecimento não pode ser nulo";

      }

      // -- Nome

      if( !$nome ) {

        $checkerrors++;

        $errormessage[] = "O nome não pode ser nulo";

      }

      // -- Status

      if( !$status ) {

        $checkerrors++;

        $errormessage[] = "O status não pode ser nulo";

      }

      // -- Estabelecimento

      if( $data['rel_estabelecimentos_id'] != $eid ) {

        $checkerrors++;

        $errormessage[] = "Ação inválida";

      }

    // Executar registro

    if( !$checkerrors ) {

      // Primeiro, vamos verificar se o registro existe
      $check_query = "SELECT id FROM pedidos WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";
      $check_result = mysqli_query($db_con, $check_query);
      
      if( mysqli_num_rows($check_result) == 0 ) {
        error_log("Pedido não encontrado para atualização - ID: $id, EID: $eid");
        header("Location: index.php?msg=erro&id=".$id."&debug=not_found");
        exit;
      }

      // Vamos tentar atualizar apenas os campos que sabemos que existem
      $update_parts = array();
      
      if( $nome ) $update_parts[] = "nome = '$nome'";
      if( $status ) $update_parts[] = "status = '$status'";
      if( $email ) $update_parts[] = "email = '$email'";
      if( $whatsapp ) $update_parts[] = "whatsapp = '$whatsapp'";
      if( $endereco ) $update_parts[] = "endereco = '$endereco'";
      if( $observacoes ) $update_parts[] = "observacoes = '$observacoes'";
      
      if( empty($update_parts) ) {
        header("Location: index.php?msg=erro&id=".$id."&debug=no_data");
        exit;
      }
      
      $update_query = "UPDATE pedidos SET " . implode(', ', $update_parts) . " WHERE id = '$id' AND rel_estabelecimentos_id = '$eid'";

      // Debug: log da query
      error_log("UPDATE Query: " . $update_query);
      error_log("ID: " . $id . " | EID: " . $eid);

      if( mysqli_query( $db_con, $update_query ) ) {

        // Verificar se alguma linha foi afetada
        if( mysqli_affected_rows($db_con) > 0 ) {
          header("Location: index.php?msg=sucesso&id=".$id);
        } else {
          error_log("Nenhuma linha foi afetada na atualização");
          header("Location: index.php?msg=erro&id=".$id."&debug=no_rows_affected");
        }

      } else {

        // Log do erro específico
        error_log("Erro MySQL: " . mysqli_error($db_con));
        header("Location: index.php?msg=erro&id=".$id."&debug=sql_error");

      }

    } else {
      // Se há erros de validação, não redireciona
      error_log("Erros de validação: " . print_r($errormessage, true));
    }

  }

?>

<div class="middle minfit bg-gray">

  <div class="container">

    <div class="row">

      <div class="col-md-12">

        <div class="title-icon pull-left">

          <i class="lni lni-shopping-basket"></i>

          <span>Editar pedido</span>

        </div>

        <div class="bread-box pull-right">

          <div class="bread">

            <a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>

            <span>/</span>

            <a href="<?php panel_url(); ?>/pedidos">Pedidos</a>

            <span>/</span>

            <a href="<?php panel_url(); ?>/pedidos/editar?id=<?php echo $id; ?>">Editar</a>

          </div>

        </div>

      </div>

    </div>

    <!-- Content -->

    <div class="data box-white mt-16">

      <?php if( $hasdata ) { ?>

      <form id="the_form" class="form-default" method="POST">

          <div class="row">

            <div class="col-md-12">

              <?php 
              if( $checkerrors ) { 
                echo '<div class="alert alert-danger">';
                echo '<strong>Erros encontrados:</strong><ul>';
                foreach($errormessage as $error) {
                  echo '<li>' . $error . '</li>';
                }
                echo '</ul></div>';
              } 
              ?>

              <?php if( $_GET['msg'] == "erro" ) { ?>

                <?php 
                $debug_msg = "Erro, tente novamente!";
                if( $_GET['debug'] == "no_rows_affected" ) {
                  $debug_msg .= " (Nenhuma linha foi afetada - verifique se o pedido existe)";
                } elseif( $_GET['debug'] == "sql_error" ) {
                  $debug_msg .= " (Erro na consulta SQL - verifique os logs)";
                } elseif( $_GET['debug'] == "not_found" ) {
                  $debug_msg .= " (Pedido não encontrado ou sem permissão)";
                } elseif( $_GET['debug'] == "no_data" ) {
                  $debug_msg .= " (Nenhum dado para atualizar)";
                }
                modal_alerta($debug_msg,"erro"); 
                ?>

              <?php } ?>

              <?php if( $_GET['msg'] == "sucesso" ) { ?>

                <?php modal_alerta("Editado com sucesso!","sucesso"); ?>

              <?php } ?>

            </div>

          </div>

          <div class="row">

            <div class="col-md-6">

              <div class="form-field-default">

                <label>ID do Pedido:</label>

                <input type="text" readonly value="<?php echo htmlclean( $data['id'] ); ?>">

              </div>

            </div>

            <div class="col-md-6">

              <div class="form-field-default">

                <label>Data/Hora:</label>

                <input type="text" readonly value="<?php echo htmlclean( $data['data_hora'] ); ?>">

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-6">

              <div class="form-field-default">

                <label>Nome do Cliente:</label>

                <input type="text" id="input-nome" name="nome" placeholder="Nome" value="<?php echo htmlclean( $data['nome'] ); ?>">

              </div>

            </div>

            <div class="col-md-6">

              <div class="form-field-default">

                <label>Email:</label>

                <input type="email" name="email" placeholder="Email" value="<?php echo htmlclean( $data['email'] ); ?>">

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-6">

              <div class="form-field-default">

                <label>WhatsApp:</label>

                <input type="text" name="whatsapp" placeholder="WhatsApp" value="<?php echo htmlclean( $data['whatsapp'] ); ?>">

              </div>

            </div>

            <div class="col-md-6">

              <div class="form-field-default">

                <label>Valor Total:</label>

                <input type="text" readonly value="R$ <?php echo htmlclean( number_format($data['v_pedido'], 2, ',', '.') ); ?>">

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-12">

              <div class="form-field-default">

                <label>Endereço:</label>

                <textarea rows="3" name="endereco" placeholder="Endereço de entrega"><?php echo htmlclean( $data['endereco'] ); ?></textarea>

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-6">

              <div class="form-field-default">

               <label>Status do Pedido:</label>

                  <div class="fake-select">

                    <i class="lni lni-chevron-down"></i>

                    <select name="status">

                      <option value="1" <?php if( $data['status'] == 1 ) { echo "SELECTED"; }; ?>>Pendente</option>

                      <option value="2" <?php if( $data['status'] == 2 ) { echo "SELECTED"; }; ?>>Finalizado</option>

                      <option value="3" <?php if( $data['status'] == 3 ) { echo "SELECTED"; }; ?>>Cancelado</option>

                      <option value="4" <?php if( $data['status'] == 4 ) { echo "SELECTED"; }; ?>>Em preparo</option>

                      <option value="5" <?php if( $data['status'] == 5 ) { echo "SELECTED"; }; ?>>Pronto</option>

                      <option value="6" <?php if( $data['status'] == 6 ) { echo "SELECTED"; }; ?>>Saiu para entrega</option>

                      <option value="7" <?php if( $data['status'] == 7 ) { echo "SELECTED"; }; ?>>Reembolsado</option>

                      <option value="8" <?php if( $data['status'] == 8 ) { echo "SELECTED"; }; ?>>Pago</option>

                      <option value="9" <?php if( $data['status'] == 9 ) { echo "SELECTED"; }; ?>>Excluído</option>

                    </select>

                    <div class="clear"></div>

                  </div>

              </div>

            </div>

            <div class="col-md-6">

              <div class="form-field-default">

                <label>Forma de Pagamento:</label>

                <input type="text" readonly value="<?php echo htmlclean( $data['forma_pagamento'] ); ?>">

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-12">

              <div class="form-field-default">

                <label>Observações:</label>

                <textarea rows="4" name="observacoes" placeholder="Observações do pedido"><?php echo htmlclean( $data['observacoes'] ); ?></textarea>

              </div>

            </div>

          </div>

          <?php if( $data['comprovante'] ) { ?>

          <div class="row">

            <div class="col-md-12">

              <div class="form-field-default">

                <label>Comprovante/Itens do Pedido:</label>

                <div style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; white-space: pre-line;">

                  <?php echo htmlclean( $data['comprovante'] ); ?>

                </div>

              </div>

            </div>

          </div>

          <?php } ?>

          <div class="row lowpadd">

            <div class="col-md-6 col-sm-5 col-xs-5">

              <div class="form-field form-field-submit">

                <a href="<?php panel_url(); ?>/pedidos" class="backbutton pull-left">

                  <span><i class="lni lni-chevron-left"></i> Voltar</span>

                </a>

              </div>

            </div>

            <div class="col-md-6 col-sm-7 col-xs-7">

              <input type="hidden" name="formdata" value="true"/>

              <div class="form-field form-field-submit">

                <button class="pull-right">

                  <span>Salvar <i class="lni lni-chevron-right"></i></span>

                </button>

              </div>

            </div>

          </div>

      </form>

      <?php } else { ?>

        <span class="nulled nulled-edit color-red">Erro, pedido inválido ou não encontrado!</span>

      <?php } ?>

    </div>

    <!-- / Content -->

  </div>

</div>

<?php 

// FOOTER

$system_footer .= "";

include('../../_layout/rdp.php');

include('../../_layout/footer.php');

?>

<script>

$(document).ready( function() {

  // Globais

  $("#the_form").validate({

      /* REGRAS DE VALIDAÇÃO DO FORMULÁRIO */

      rules:{

        nome:{

        required: true

        },

        status:{

        required: true

        }

      },

      /* DEFINIÇÃO DAS MENSAGENS DE ERRO */

      messages:{

        nome:{

          required: "Esse campo é obrigatório"

        },

        status:{

          required: "Esse campo é obrigatório"

        }

      }

    });

});

</script>