<?php 
error_reporting(0);
$modo = $_GET['modo'];
?>

<?php if( $modo == "variacao" ) { ?>
<div class="panel-group panel-filters panel-subvariacao" style="display: none;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a class="subvariacao-link" data-toggle="collapse" href="">
          <div class="row alignmiddle">
            <div class="col-md-2 col-sm-2 col-xs-3">
              <i class="menos lni lni-minus"></i>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-6">
              <span class="variacao-desc"></span>
            </div>
            <div class="col-md-2 col-sm-2 col-xs-3">
              <i class="deletar deletar-variacao lni lni-trash"></i>
            </div>
          </div>
        </a>
      </h4>
    </div>
    <div id="" class="subvariacao-body panel-collapse collapse in">
      <div class="panel-body panel-body-subvariacao">
        <div class='variacao' variacao-id=''>
          <div class='title'>
            <div class='row'>
              <div class='col col-md-6 col-sm-12 col-xs-12'>
                <div class='form-field-default'>
                    <label>Nome da variação:</label>
                    <input class='variacao-nome' type='text' name='' placeholder='Nome'/>
                </div>
              </div>
              <div class='col col-md-3 col-sm-6 col-xs-6'>
                <div class='form-field-default'>
                    <label>Escolha minima:</label>
                    <input class='variacao-escolha-minima numberinput' type='number' name='' min='0' value='1'/>
                </div>
              </div>
              <div class='col col-md-3 col-sm-6 col-xs-6'>
                <div class='form-field-default'>
                    <label>Escolha máxima:</label>
                    <input class='variacao-escolha-maxima numberinput' type='number' name='' min='1' value='1'/>
                </div>
              </div>
            </div>
          </div>
          <div class='content'>
            <div class='row'>
              <div class='col-md-12'>
                <div class='render-itens'>
                  <div class='col-md-4'>
                    <div class='adicionar adicionar-item'>
                      <i class='lni lni-plus'></i>
                      <span>Adicionar item</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php } ?>

<?php if( $modo == "item" ) { ?>



                                                    <div class='col-md-4 col-item' variacao-id='' item-id=''>
                                                      <div class='item'>
                                                          <div class='title'>
                                                            <div class='row'>
                                                              <div class='col col-md-10 col-sm-10 col-xs-10'>
                                                                <div class='form-field-default'>
                                                                    <label>Nome:</label>
                                                                    <input class='item-nome' type='text' name='' placeholder='Nome' value=""/>
                                                                </div>
                                                              </div>
                                                              <div class='col col-md-2 col-sm-2 col-xs-2'>
                                                                <div class='remover deletar-item'>
                                                                  <i class='lni lni-trash'></i>
                                                                </div>
                                                              </div>
                                                            </div>
                                                          </div>
                                                          <div class='content'>
                                                            <div class='row'>
                                                              <div class='col col-md-12'>
                                                                <div class='form-field-default'>
                                                                    <label>Descrição:</label>
                                                                    <textarea rows='1' class='item-descricao' name='' placeholder='Descrição'></textarea>
                                                                </div>
                                                              </div>
                                                              <div class='col col-md-12'>
                                                                <div class='form-field-default'>
                                                                    <label>Valor adicional:</label>
                                                                    <input class='item-valor maskmoney' type='text' name='' placeholder='Valor' value=""/>
                                                                </div>
                                                              </div>
                                                                                                                          <div class='col col-md-12'>
                                                              <div class="form-field-default">



                                                                <label>Habilitar quantidade?</label>
                                                                <div class="radios">
                                                                  <div class="spacer"></div>
                                                                  <div class="form-field-radio">
                                                                    <input type="radio" class="item-quantidade" name='' value="1"> Sim
                                                                  </div>
                                                                  <div class="form-field-radio">
                                                                    <input type="radio" class="item-quantidade" name='' value="2"> Não
                                                                  </div>
                                                                  <div class="clear"></div>
                                                                </div>

                                                              </div>
                                                            </div>
                                                            <div class='col col-md-12'>
                                                              <div class='form-field-default'>
                                                                <label>Estoque:</label>
                                                                <input type="number" class='item-estoque' name='' placeholder='Estoque' value=''>
                                                              </div>
                                                            </div>
                                                            <div class='col col-md-12'>
                                                              <div class='form-field-default'>
                                                                <label>Baixo estoque:</label>
                                                                <input type="number" class='item-baixo-estoque' name='' placeholder='Baixo estoque' value=''>
                                                              </div>
                                                            </div>
                                                            
                                                            
                                                            
                                                            </div>
                                                          </div>
                                                      </div>
                                                    </div>


  <!--<div class='col-md-4 col-item' variacao-id='' item-id='' style="display: none;">-->
  <!--  <div class='item'>-->
  <!--      <div class='title'>-->
  <!--        <div class='row'>-->
  <!--          <div class='col col-md-10 col-sm-10 col-xs-10'>-->
  <!--            <div class='form-field-default'>-->
  <!--                <label>Nome:</label>-->
  <!--                <input class='item-nome' type='text' name='' placeholder='Nome'/>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--          <div class='col col-md-2 col-sm-2 col-xs-2'>-->
  <!--            <div class='remover deletar-item'>-->
  <!--              <i class='lni lni-trash'></i>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--      <div class='content'>-->
  <!--        <div class='row'>-->
  <!--          <div class='col col-md-12'>-->
  <!--            <div class='form-field-default'>-->
  <!--                <label>Descrição:</label>-->
  <!--                <textarea rows='1' class='item-descricao' name='' placeholder='Descrição'></textarea>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--          <div class='col col-md-12'>-->
  <!--            <div class='form-field-default'>-->
  <!--                <label>Valor adicional:</label>-->
  <!--                <input class='item-valor maskmoney' type='text' name='' placeholder='Valor'/>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--  </div>-->
  <!--</div>-->

<?php }  




if ($modo == "html_items") {
    $html = isset($_POST['html']) ? stripslashes($_POST['html']) : '';
    
    if (!empty($html)) {
        // Criar um novo DOMDocument
        $dom = new DOMDocument();
        
        // Suprimir warnings do loadHTML
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Buscar todos os elementos que contêm os nomes dos itens
        $items = $xpath->query("//span[contains(@class, 'ui-pdp-dropdown-selector__item--label-small')]");
        
        $htmlOutput = '';
        if ($items->length > 0) {
            foreach ($items as $item) {
                $itemName = trim($item->nodeValue);
                $htmlOutput .= "<div class='col-md-4 col-item' variacao-id='' item-id=''>
                    <div class='item'>
                        <div class='title'>
                            <div class='row'>
                                <div class='col col-md-10 col-sm-10 col-xs-10'>
                                    <div class='form-field-default'>
                                        <label>Nome:</label>
                                        <input class='item-nome' type='text' name='' placeholder='Nome' value=\"" . htmlspecialchars($itemName, ENT_QUOTES) . "\"/>
                                    </div>
                                </div>
                                <div class='col col-md-2 col-sm-2 col-xs-2'>
                                    <div class='remover deletar-item'>
                                        <i class='lni lni-trash'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='content'>
                            <div class='row'>
                                <div class='col col-md-12'>
                                    <div class='form-field-default'>
                                        <label>Descrição:</label>
                                        <textarea rows='1' class='item-descricao' name='' placeholder='Descrição'></textarea>
                                    </div>
                                </div>
                                <div class='col col-md-12'>
                                    <div class='form-field-default'>
                                        <label>Valor adicional:</label>
                                        <input class='item-valor maskmoney' type='text' name='' placeholder='Valor' value=\"\"/>
                                    </div>
                                </div>
                                <div class='col col-md-12'>
                                    <div class=\"form-field-default\">
                                        <label>Habilitar quantidade?</label>
                                        <div class=\"radios\">
                                            <div class=\"spacer\"></div>
                                            <div class=\"form-field-radio\">
                                                <input type=\"radio\" class=\"item-quantidade\" name='' value=\"1\" checked> Sim
                                            </div>
                                            <div class=\"form-field-radio\">
                                                <input type=\"radio\" class=\"item-quantidade\" name='' value=\"2\"> Não
                                            </div>
                                            <div class=\"clear\"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class='col col-md-12'>
                                    <div class='form-field-default'>
                                        <label>Estoque:</label>
                                        <input type=\"number\" class='item-estoque' name='' placeholder='Estoque' value=''>
                                    </div>
                                </div>
                                <div class='col col-md-12'>
                                    <div class='form-field-default'>
                                        <label>Baixo estoque:</label>
                                        <input type=\"number\" class='item-baixo-estoque' name='' placeholder='Baixo estoque' value=''>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
            }
            echo json_encode(['success' => true, 'html' => $htmlOutput]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhum item encontrado no HTML fornecido.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'HTML não fornecido.']);
    }
    exit;
}
?>