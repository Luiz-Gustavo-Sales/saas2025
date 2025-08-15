<?php
// Incluir configurações
include('../_core/_includes/config.php');

// Verificar se há sessão ativa
session_start();

// Buscar dados do estabelecimento pelo subdomínio
$host_parts = explode('.', $_SERVER['HTTP_HOST']);
$insubdominio = $host_parts[0];

$estabelecimento_query = mysqli_query($db_con, "SELECT * FROM estabelecimentos WHERE subdominio = '$insubdominio' AND status = '1'");

if (mysqli_num_rows($estabelecimento_query) == 0) {
    header("Location: login.php?error=estabelecimento_nao_encontrado");
    exit;
}

$estabelecimento_data = mysqli_fetch_array($estabelecimento_query);

// Se não está logado, redirecionar para login
if (!isset($_SESSION['garcom_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o garçom pertence a este estabelecimento
$garcom_check = mysqli_query($db_con, "SELECT * FROM garcons WHERE id = '".$_SESSION['garcom_id']."' AND rel_estabelecimentos_id = '".$estabelecimento_data['id']."' AND ativo = 1");

if (mysqli_num_rows($garcom_check) == 0) {
    session_destroy();
    header("Location: login.php?error=acesso_negado");
    exit;
}

$garcom_data = mysqli_fetch_array($garcom_check);

// Buscar produtos do estabelecimento (se a tabela existir)
$produtos_query = mysqli_query($db_con, "SELECT * FROM produtos WHERE rel_estabelecimentos_id = '".$estabelecimento_data['id']."' AND status = '1' AND visible = '1' ORDER BY rel_categorias_id, nome");

// Buscar categorias para organizar
$categorias_query = mysqli_query($db_con, "SELECT * FROM categorias WHERE rel_estabelecimentos_id = '".$estabelecimento_data['id']."' AND status = '1' ORDER BY nome");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - <?php echo $estabelecimento_data['nome']; ?></title>
    <meta name="theme-color" content="#667eea">
    
    <!-- Bootstrap CSS -->
    <link href="../_cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../_cdn/lineicons/LineIcons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .app-header {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        .app-content {
            padding: 1rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Barra de Pesquisa */
        .search-section {
            margin-bottom: 1.5rem;
        }
        
        .search-container {
            position: relative;
            background: white;
            border-radius: 25px;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .search-icon {
            color: #999;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 1rem 0;
            font-size: 1rem;
            background: transparent;
        }
        
        .clear-search {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .clear-search:hover {
            background: #f8f9fa;
            color: var(--danger-color);
        }
        
        /* Filtros de Categoria */
        .category-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .category-btn {
            background: white;
            border: 2px solid transparent;
            color: #666;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s ease;
            min-width: fit-content;
        }
        
        .category-btn:hover, .category-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Lista de Produtos */
        .produto-item {
            background: white;
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1rem;
            display: flex;
            gap: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .produto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .produto-image-small {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .produto-image-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-image {
            color: #dee2e6;
            font-size: 2rem;
        }
        
        .produto-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .produto-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        
        .produto-nome {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .produto-categoria {
            background: #f8f9fa;
            color: #666;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .produto-descricao {
            color: #666;
            font-size: 0.9rem;
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
        }
        
        .produto-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .produto-preco {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--success-color);
        }
        
        .btn-detalhes {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-detalhes:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: var(--primary-color);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: #f8f9fa;
            color: var(--danger-color);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-produto-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-produto-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .modal-produto-image .no-image {
            font-size: 4rem;
            color: #dee2e6;
        }
        
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .empty-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 2rem;
        }
        
        .empty-state h2 {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #999;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .produto-item {
                flex-direction: column;
                text-align: center;
            }
            
            .produto-image-small {
                width: 100%;
                height: 150px;
                margin: 0 auto 1rem;
            }
            
            .produto-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .produto-footer {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .category-filters {
                gap: 0.3rem;
            }
            
            .category-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .produto-preco {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--success-color);
        }
        
        .categoria-section {
            margin-bottom: 2rem;
        }
        
        .categoria-title {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .categoria-title h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.4rem;
        }
        
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .empty-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 2rem;
        }
        
        .empty-state h2 {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #999;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .produtos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-info">
            <h1><i class="lni lni-bookmark"></i> Cardápio - <?php echo $estabelecimento_data['nome']; ?></h1>
        </div>
        <a href="index.php" class="back-btn">
            <i class="lni lni-arrow-left"></i> Voltar
        </a>
    </header>
    
    <main class="app-content">
        <!-- Barra de Pesquisa -->
        <div class="search-section">
            <div class="search-container">
                <i class="lni lni-search-alt search-icon"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar produtos...">
                <button id="clearSearch" class="clear-search" style="display: none;">
                    <i class="lni lni-close"></i>
                </button>
            </div>
        </div>

        <!-- Filtros por Categoria -->
        <div class="category-filters">
            <button class="category-btn active" data-category="todos">Todos</button>
            <?php
            if (mysqli_num_rows($categorias_query) > 0) {
                mysqli_data_seek($categorias_query, 0); // Reset pointer
                while ($cat = mysqli_fetch_array($categorias_query)) {
                    echo '<button class="category-btn" data-category="'.$cat['id'].'">'.$cat['nome'].'</button>';
                }
            }
            ?>
        </div>

        <!-- Lista de Produtos -->
        <div id="produtosList">
            <?php
            if (mysqli_num_rows($produtos_query) > 0) {
                // Criar array de categorias
                $categorias_array = array();
                mysqli_data_seek($categorias_query, 0); // Reset pointer
                if (mysqli_num_rows($categorias_query) > 0) {
                    while ($cat = mysqli_fetch_array($categorias_query)) {
                        $categorias_array[$cat['id']] = $cat['nome'];
                    }
                }
                
                // Reset pointer dos produtos
                mysqli_data_seek($produtos_query, 0);
                
                while ($produto = mysqli_fetch_array($produtos_query)) {
                    $categoria_nome = isset($categorias_array[$produto['rel_categorias_id']]) ? $categorias_array[$produto['rel_categorias_id']] : 'Sem Categoria';
                    ?>
                    <div class="produto-item" 
                         data-categoria="<?php echo $produto['rel_categorias_id']; ?>" 
                         data-nome="<?php echo strtolower($produto['nome']); ?>"
                         data-descricao="<?php echo strtolower($produto['descricao'] ?? ''); ?>"
                         onclick="mostrarDetalhes(<?php echo $produto['id']; ?>)">
                        
                        <div class="produto-image-small">
                            <?php if (!empty($produto['imagem'])) { ?>
                                <img src="<?php echo thumber($produto['imagem'], 200); ?>" alt="<?php echo $produto['nome']; ?>">
                            <?php } else { ?>
                                <div class="no-image">
                                    <i class="lni lni-dinner"></i>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <div class="produto-details">
                            <div class="produto-header">
                                <h4 class="produto-nome"><?php echo $produto['nome']; ?></h4>
                                <span class="produto-categoria"><?php echo $categoria_nome; ?></span>
                            </div>
                            
                            <?php if (!empty($produto['descricao'])) { ?>
                                <p class="produto-descricao"><?php echo substr($produto['descricao'], 0, 80) . (strlen($produto['descricao']) > 80 ? '...' : ''); ?></p>
                            <?php } ?>
                            
                            <div class="produto-footer">
                                <span class="produto-preco">R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></span>
                                <button class="btn-detalhes">
                                    <i class="lni lni-eye"></i> Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="lni lni-dinner"></i>
                    </div>
                    <h2>Cardápio em preparação</h2>
                    <p>Os produtos ainda estão sendo cadastrados no sistema.<br>Em breve o cardápio completo estará disponível.</p>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Modal de Detalhes do Produto -->
        <div id="produtoModal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitulo">Detalhes do Produto</h3>
                    <button class="modal-close" onclick="fecharModal()">
                        <i class="lni lni-close"></i>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </main>
    
    <script src="../_cdn/jquery/jquery.min.js"></script>
    <script src="../_cdn/bootstrap/js/bootstrap.min.js"></script>
    
    <script>
        // Função de pesquisa
        function pesquisarProdutos() {
            const termo = document.getElementById('searchInput').value.toLowerCase();
            const produtos = document.querySelectorAll('.produto-item');
            let encontrou = false;
            
            produtos.forEach(produto => {
                const nome = produto.dataset.nome;
                const descricao = produto.dataset.descricao;
                
                if (nome.includes(termo) || descricao.includes(termo)) {
                    produto.style.display = 'flex';
                    encontrou = true;
                } else {
                    produto.style.display = 'none';
                }
            });
            
            // Mostrar/esconder botão de limpar
            const clearBtn = document.getElementById('clearSearch');
            if (termo.length > 0) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }
            
            // Mostrar mensagem se não encontrou nada
            mostrarMensagemVazia(!encontrou && termo.length > 0);
        }
        
        // Função para filtrar por categoria
        function filtrarCategoria(categoriaId) {
            const produtos = document.querySelectorAll('.produto-item');
            let encontrou = false;
            
            produtos.forEach(produto => {
                if (categoriaId === 'todos' || produto.dataset.categoria === categoriaId) {
                    produto.style.display = 'flex';
                    encontrou = true;
                } else {
                    produto.style.display = 'none';
                }
            });
            
            // Atualizar botões ativos
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-category="${categoriaId}"]`).classList.add('active');
            
            // Limpar pesquisa ao filtrar
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            
            // Remover mensagem de busca vazia
            const mensagem = document.getElementById('mensagemVazia');
            if (mensagem) {
                mensagem.remove();
            }
        }
        
        // Função para mostrar detalhes do produto via AJAX
        function mostrarDetalhes(produtoId) {
            const modal = document.getElementById('produtoModal');
            const modalBody = document.getElementById('modalBody');
            
            // Mostrar loading
            modalBody.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="lni lni-spinner" style="font-size: 2rem; color: var(--primary-color); animation: spin 1s linear infinite;"></i><br><br>Carregando...</div>';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Fazer requisição AJAX
            fetch(`produto_detalhes.php?id=${produtoId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Produto não encontrado');
                    }
                    return response.json();
                })
                .then(produto => {
                    let imagemHtml = '';
                    if (produto.thumb_url) {
                        imagemHtml = `<img src="${produto.thumb_url}" alt="${produto.nome}">`;
                    } else {
                        imagemHtml = '<div class="no-image"><i class="lni lni-dinner"></i></div>';
                    }
                    
                    modalBody.innerHTML = `
                        <div class="modal-produto-image">
                            ${imagemHtml}
                        </div>
                        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">${produto.nome}</h4>
                        <p style="background: #f8f9fa; padding: 0.5rem; border-radius: 8px; margin-bottom: 1rem; color: #666; font-size: 0.9rem;">
                            <i class="lni lni-tag"></i> ${produto.categoria}
                        </p>
                        ${produto.descricao ? `<p style="color: #666; line-height: 1.6; margin-bottom: 1.5rem;">${produto.descricao}</p>` : ''}
                        <div style="background: linear-gradient(135deg, var(--success-color), #229954); color: white; padding: 1rem; border-radius: 8px; text-align: center;">
                            <div style="font-size: 0.9rem; opacity: 0.9;">Preço</div>
                            <div style="font-size: 2rem; font-weight: bold;">R$ ${produto.valor.toFixed(2).replace('.', ',')}</div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    modalBody.innerHTML = `
                        <div style="text-align: center; padding: 2rem; color: var(--danger-color);">
                            <i class="lni lni-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h4>Erro ao carregar produto</h4>
                            <p>Não foi possível carregar os detalhes do produto.</p>
                        </div>
                    `;
                });
        }
        
        // Função para fechar modal
        function fecharModal() {
            document.getElementById('produtoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Função para mostrar mensagem vazia
        function mostrarMensagemVazia(mostrar) {
            let mensagem = document.getElementById('mensagemVazia');
            if (mostrar && !mensagem) {
                mensagem = document.createElement('div');
                mensagem.id = 'mensagemVazia';
                mensagem.className = 'empty-state';
                mensagem.innerHTML = `
                    <div class="empty-icon">
                        <i class="lni lni-search-alt"></i>
                    </div>
                    <h2>Nenhum produto encontrado</h2>
                    <p>Tente pesquisar com outros termos ou navegue pelas categorias.</p>
                `;
                document.getElementById('produtosList').appendChild(mensagem);
            } else if (!mostrar && mensagem) {
                mensagem.remove();
            }
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Pesquisa em tempo real
            document.getElementById('searchInput').addEventListener('input', pesquisarProdutos);
            
            // Limpar pesquisa
            document.getElementById('clearSearch').addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                pesquisarProdutos();
            });
            
            // Filtros de categoria
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    filtrarCategoria(this.dataset.category);
                });
            });
            
            // Fechar modal clicando fora
            document.getElementById('produtoModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModal();
                }
            });
            
            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    fecharModal();
                }
            });
        });
        
        // Adicionar animação de spin para loading
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
