<?php
// CORE
include('../../../_core/_includes/config.php');
// RESTRICT
restrict_estabelecimento();
restrict_expirado();
// SEO
$seo_subtitle = "Adicionar categoria";
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

  global $numeric_data;

  // Checar se formulário foi executado

  $formdata = $_POST['formdata'];

  if( $formdata ) {

    // Setar campos

    $estabelecimento = $_SESSION['estabelecimento']['id'];
    $nome = mysqli_real_escape_string( $db_con, $_POST['nome'] );
    $ordem = mysqli_real_escape_string( $db_con, $_POST['ordem'] );
    $visible = mysqli_real_escape_string( $db_con, $_POST['visible'] );
    $status = "1";
    
    
    //att
    
        if( $_FILES['icone']['name'] ) {

        $upload = upload_image( "categorias", $_FILES['icone'] );
        
        
        if ( $upload['status'] == "1" ) {
          $icone = $upload['url'];
        } else {
          $checkerrors++;
          for( $x=0; $x < count( $upload['errors'] ); $x++ ) {
            $errormessage[] = $upload['errors'][$x];
          }
        }

      }
    

    // Checar Erros

    $checkerrors = 0;
    $errormessage = array();

      // -- Estabelecimento

      if( !$estabelecimento ) {
        $checkerrors++;
        $errormessage[] = "O estabelecimento não pode ser nulo";
      }

      // -- Nome

      if( !$nome ) {
        $checkerrors++;
        $errormessage[] = "O nome não pode ser nulo";
      }
      
      // -- Ordem

      if( !$ordem ) {
        $checkerrors++;
        $errormessage[] = "Coloque a ordem da categoria";
      }
      
      

    // Executar registro

    if( !$checkerrors ) {
        
        //att
        
        // $cat = new_categoria( $estabelecimento,$nome,$ordem,$visible,$status,$icone );
        
        // header("Location: index.php?msg=" mysqli_error($conexao). $cat);
 
          	// Verifica se a coluna 'icone' existe na tabela 'produtos'
            $colunaExiste = $db_con->query("SHOW COLUMNS FROM categorias LIKE 'icone'");
            if ($colunaExiste->num_rows == 0) {
                // Se a coluna não existir, adiciona a coluna 'icone'
                $db_con->query("ALTER TABLE categorias ADD icone VARCHAR(255) DEFAULT NULL");
            }
              

        if( mysqli_query( $db_con, "INSERT INTO categorias (rel_estabelecimentos_id,nome,ordem,visible,status,icone) VALUES ('$estabelecimento','$nome','$ordem','$visible','$status','$icone');") ) {
          

        header("Location: index.php?msg=sucesso");

      } else {
          
        header("Location: index.php?msg=erro");

      }

    }

  }
  
?>

<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

        <div class="title-icon pull-left">
          <i class="lni lni-radio-button"></i>
          <span>Adicionar categoria</span>
        </div>

        <div class="bread-box pull-right">
          <div class="bread">
            <a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
            <span>/</span>
            <a href="<?php panel_url(); ?>/categorias">Categorias</a>
            <span>/</span>
            <a href="<?php panel_url(); ?>/categorias/adicionar">Adicionar</a>
          </div>
        </div>
        
			</div>

		</div>

		<!-- Content -->

		<div class="data box-white mt-16">

      <form id="the_form" class="form-default" method="POST" enctype="multipart/form-data">

          <div class="row">

            <div class="col-md-12">

              <?php if( $checkerrors ) { list_errors(); } ?>

              <?php if( $_GET['msg'] == "erro" ) { ?>

                <?php modal_alerta("Erro, tente novamente!","erro"); ?>

              <?php } ?>

              <?php if( $_GET['msg'] == "sucesso" ) { ?>

                <?php modal_alerta("Cadastro efetuado com sucesso!","sucesso"); ?>

              <?php } ?>

            </div>

          </div>
          
          <div class="row">

            <div class="col-md-12">
              <label>Icone:</label>
              <div class="file-preview">

                <div class="image-preview image-preview-product" id="image-preview" style='background: url("") no-repeat center center; background-size: auto 102%;'>
                  <label for="image-upload" id="image-label">Enviar imagem</label>
                  <input type="file" name="icone" id="image-upload"/>
                </div>
                <span class="explain">Selecione o icone clicando no campo ou arrastando o arquivo!</span>

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-8">

              <div class="form-field-default">

                  <label>Nome:</label>
                  <input type="text" id="input-nome" name="nome" placeholder="Nome" value="<?php echo htmlclean( $_POST['nome'] ); ?>">

              </div>

            </div>
            
            <div class="col-md-4">

              <div class="form-field-default">

                  <label>Ordem:</label>
                  <input type="text" id="input-ordem" name="ordem" placeholder="Ordem" value="<?php echo htmlclean( $_POST['ordem'] ); ?>" maxlength="4">

              </div>

            </div>

          </div>

          <div class="row">

            <div class="col-md-12">

              <div class="form-field-default">

                  <label>Categoria visível?</label>
                  <span class="form-tip">Se habilitado a categoria irá aparecer em todo site, caso contrário, irá aparecer apenas para quem você compartilhar o link.</span>
                  <div class="radios">
                    <div class="spacer"></div>
                    <div class="form-field-radio">
                      <input type="radio" name="visible" value="1" <?php if( $_POST['visible'] == 1 OR !$_POST['visible'] ){ echo 'CHECKED'; }; ?>> Sim
                    </div>
                    <div class="form-field-radio">
                      <input type="radio" name="visible" value="2" <?php if( $_POST['visible'] == 2 ){ echo 'CHECKED'; }; ?>> Não
                    </div>
                  </div>
                  <div class="clear"></div>

              </div>

            </div>

          </div>

          <div class="row lowpadd">

          	<div class="col-md-6 col-sm-5 col-xs-5">
          		<div class="form-field form-field-submit">
  							<a href="<?php panel_url(); ?>/categorias" class="backbutton pull-left">
  								<span><i class="lni lni-chevron-left"></i> Voltar</span>
  							</a>
  						</div>
          	</div>

  					<div class="col-md-6 col-sm-7 col-xs-7">
  						<input type="hidden" name="formdata" value="true"/>
  						<div class="form-field form-field-submit">
  							<button class="pull-right">
  								<span>Cadastrar <i class="lni lni-chevron-right"></i></span>
  							</button>
  						</div>
  					</div>

          </div>

      </form>

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
    
        
    // Preview avatar
      $.uploadPreview({
        input_field: "#image-upload",
        preview_box: "#image-preview",
        label_field: "#image-label",
        label_default: "Envie ou arraste",
        label_selected: "Trocar imagem",
        no_label: false
      });

  // Preview capa
  $.uploadPreview({
    input_field: "#image-upload2",
    preview_box: "#image-preview2",
    label_field: "#image-label2",
    label_default: "Envie ou arraste",
    label_selected: "Trocar imagem",
    no_label: false
  });
          
  // Globais

  $("#the_form").validate({

      /* REGRAS DE VALIDAÇÃO DO FORMULÁRIO */

      rules:{

        estabelecimento_id:{
        required: true
        },
        nome:{
        required: true
        },
        ordem:{
          required: true
        }

      },
          
      /* DEFINIÇÃO DAS MENSAGENS DE ERRO */
              
      messages:{

        estabelecimento_id:{
          required: "Esse campo é obrigatório"
        },
        nome:{
          required: "Esse campo é obrigatório"
        },
        ordem:{
          required: "Esse campo é obrigatório"
        }

      }

    });

  });

</script>