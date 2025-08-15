<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Extrair Produtos</title>

    <link rel="stylesheet" href="styles.css">

</head>

<body>

    <div class="container">

        <h1>Extrair Produtos de uma Página</h1>

        <form action="processar_extracao.php" method="POST">

            <label for="url">Insira o link da página HTML:</label>

            <input type="url" id="url" name="url" placeholder="https://exemplo.com" required>

            <button type="submit">Extrair e Gerar XML</button>

        </form>

    </div>

</body>

</html>