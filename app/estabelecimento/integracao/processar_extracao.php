<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);



    if (!$url) {

        die('URL inválida. Por favor, insira um link válido.');

    }



    // Baixar o conteúdo da página

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $html = curl_exec($ch);



    if (curl_errno($ch)) {

        die('Erro ao acessar a URL: ' . curl_error($ch));

    }



    curl_close($ch);



    // Processar o HTML para extrair informações dos produtos

    $dom = new DOMDocument();

    @$dom->loadHTML($html);



    $xpath = new DOMXPath($dom);



    // Extrair informações do produto

    $titulo = $xpath->query('//h1[contains(@class, "ui-pdp-title")]')->item(0)->nodeValue ?? 'Título não encontrado';

    $descricao = $xpath->query('//p[contains(@class, "ui-pdp-description__content")]')->item(0)->nodeValue ?? 'Descrição não encontrada';



    // Lógica de extração de preço revisada

    $precoInteiroValor = null;

    $precoDecimalValor = '00'; // Padrão para centavos

    $precoFinal = 'Indisponível';



    // Tentativa 1: XPath mais específico para o contêiner principal do preço e o elemento andes-money-amount

    $mainPriceElementContainer = $xpath->query('//div[contains(@class, "ui-pdp-price__main-container")]/span[contains(@class, "andes-money-amount")]')->item(0);



    if ($mainPriceElementContainer) {

        $fractionNode = $xpath->query('.//span[contains(@class, "andes-money-amount__fraction")]', $mainPriceElementContainer)->item(0);

        $centsNode = $xpath->query('.//span[contains(@class, "andes-money-amount__cents")]', $mainPriceElementContainer)->item(0);



        if ($fractionNode) {

            $tempInteiro = preg_replace('/\D/', '', $fractionNode->nodeValue);

            if ($tempInteiro !== '') {

                $precoInteiroValor = $tempInteiro;

            }

        }



        if ($centsNode) {

            $tempDecimal = preg_replace('/\D/', '', $centsNode->nodeValue);

            if ($tempDecimal !== '') {

                $precoDecimalValor = $tempDecimal;

            }

        }

    } else {

        // Tentativa 2 (Fallback): Se a estrutura específica não for encontrada, tentar seletores mais gerais para andes-money-amount.

        // Pegar o primeiro par de fração/centavos que encontrar.

        $fallbackFractionNode = $xpath->query('(//span[contains(@class, "andes-money-amount__fraction")])[1]')->item(0);

        $fallbackCentsNode = $xpath->query('(//span[contains(@class, "andes-money-amount__cents")])[1]')->item(0);



        if ($fallbackFractionNode) {

            $tempInteiro = preg_replace('/\D/', '', $fallbackFractionNode->nodeValue);

            if ($tempInteiro !== '') {

                $precoInteiroValor = $tempInteiro;

            }

        }



        // Usar os centavos do fallback apenas se a fração do fallback foi encontrada

        if ($precoInteiroValor !== null && $fallbackFractionNode) {

            if ($fallbackCentsNode) {

                $tempDecimal = preg_replace('/\D/', '', $fallbackCentsNode->nodeValue);

                if ($tempDecimal !== '') {

                    $precoDecimalValor = $tempDecimal;

                }

            } else {

                $precoDecimalValor = '00'; // Se fração foi encontrada mas centavos não, assume 00

            }

        }

    }



    // Formatar os centavos para ter sempre dois dígitos (ex: "9" vira "90", "09" continua "09")

    // Se o valor extraído for, por exemplo, "9", significa 90 centavos. Se for "09", significa 9 centavos.

    // A lógica de str_pad aqui depende da interpretação. Para ",9" ser ",90", usamos STR_PAD_RIGHT.

    if (strlen($precoDecimalValor) == 1) {

        $precoDecimalValor = str_pad($precoDecimalValor, 2, '0', STR_PAD_RIGHT);

    } else {

        $precoDecimalValor = str_pad(substr($precoDecimalValor,0,2), 2, '0', STR_PAD_LEFT); // Garante 2 dígitos, ex: "1" -> "01"

    }





    if ($precoInteiroValor !== null) {

        $precoCompletoStr = $precoInteiroValor . '.' . $precoDecimalValor;

        if (is_numeric($precoCompletoStr)) {

            $precoFloat = (float)$precoCompletoStr;

            if ($precoFloat > 0) { // Considerar preço válido apenas se for maior que zero

                $precoFinal = number_format($precoFloat, 2, '.', '') . ' BRL';

            }

        }

    }

    

    $preco = $precoFinal; // Atribuir o resultado final à variável $preco usada no XML



    $imagemNode = $xpath->query('//img[contains(@class, "ui-pdp-image")]')->item(0);

    $imagem = ($imagemNode instanceof DOMElement) ? $imagemNode->getAttribute('src') : '';



    // Ajustar o link da imagem

    if (empty($imagem)) {

        $imagem = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Placeholder

    }



    // Criar o XML

    $xml = new SimpleXMLElement('<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0"></feed>');



    $entry = $xml->addChild('entry');

    $entry->addChild('g:id', uniqid());

    $entry->addChild('g:title', htmlspecialchars($titulo));

    $entry->addChild('g:description', htmlspecialchars($descricao));

    $entry->addChild('g:image_link', $imagem);

    $entry->addChild('g:price', $preco);



    // Adicionar campos obrigatórios ao XML

    $entry->addChild('g:condition', 'new');

    $entry->addChild('g:availability', 'in stock');

    $entry->addChild('g:brand', 'Marca Exemplo'); // Substitua pela lógica de extração, se aplicável

    $entry->addChild('g:gtin', '1234567890123'); // Substitua pela lógica de extração, se aplicável

    $entry->addChild('g:identifier_exists', 'true');

    $entry->addChild('g:google_product_category', 'Categoria Exemplo'); // Substitua pela lógica de extração, se aplicável



    // Salvar o XML gerado

    $xmlPath = __DIR__ . '/produtos.xml';

    $xml->asXML($xmlPath);



    echo "<p>XML gerado com sucesso! <a href='produtos.xml'>Clique aqui para baixar</a></p>";

} else {

    die('Método de requisição inválido.');

}

?>

