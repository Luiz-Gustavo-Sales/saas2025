<?php 
include('../_core/_includes/config.php'); 

// Headers de segurança e performance
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

global $plano_default;

// Consulta SQL para selecionar o link do video_landing
$query_video_landing = mysqli_query($db_con, "SELECT link FROM link WHERE nome='video_landing'");
$datalink_video_landing = mysqli_fetch_array($query_video_landing);
$link_video_landing = $datalink_video_landing['link'];
    
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo $nome_loja; ?> - O seu catálogo Online de produtos e serviços. A melhor escolha.</title>
<meta name="description" content="Crie seu catálogo online de produtos com pedidos via WhatsApp. Simples, rápido e integrado com WhatsApp, Facebook e Instagram. Aumente suas vendas hoje mesmo!" />
<meta name="keywords" content="catalogo online, catalogo digital, cardapio online, catalogo via whatsapp, cardapios online, app de cardapio, loja virtual, vendas online, whatsapp business, e-commerce" />
<meta name="resource-type" content="document" />
<meta name="revisit-after" content="1 day" />
<meta name="distribution" content="Global" />
<meta name="rating" content="General" />
<meta name="author" content="<?php echo $nome_loja; ?> - Catálogo Online de Produtos" />
<meta name="language" content="pt-br" />
<meta name="doc-class" content="Completed" />
<meta name="doc-rights" content="Public" />
<meta name="Subject" content="Crie seu catálogo online de produtos com pedidos via WhatsApp." />
<meta name="audience" content="all" />
<meta name="robots" content="index,follow" />
<link rel="canonical" href="https://conheca.<?php echo $simple_url; ?>/" />
<meta name="googlebot" content="all" />
<meta name="copyright" content="<?php echo $nome_loja; ?> - Catálogo Online de Produtos" />
<meta name="url" content="https://<?php echo $simple_url; ?>" />
<!-- Open Graph / Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="https://conheca.<?php echo $simple_url; ?>/" />
<meta property="og:title" content="<?php echo $nome_loja; ?> - O seu catálogo Online de produtos e serviços. Crie o seu agora mesmo." />
<meta property="og:description" content="Crie seu catálogo online de produtos com pedidos via WhatsApp. Simples, rápido e integrado com WhatsApp, Facebook e Instagram. Aumente suas vendas hoje mesmo!" />
<meta property="og:image" content="https://conheca.<?php echo $simple_url; ?>/assets/img/favicon.png" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:site_name" content="<?php echo $nome_loja; ?>" />
<meta property="og:locale" content="pt_BR" />
<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:url" content="https://conheca.<?php echo $simple_url; ?>/" />
<meta property="twitter:title" content="<?php echo $nome_loja; ?> - O seu catálogo Online de produtos e serviços" />
<meta property="twitter:description" content="Crie seu catálogo online de produtos com pedidos via WhatsApp. Simples, rápido e integrado." />
<meta property="twitter:image" content="https://conheca.<?php echo $simple_url; ?>/assets/img/favicon.png" />
<!-- Schema.org markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "<?php echo $nome_loja; ?>",
  "description": "Plataforma completa para criar catálogos online com integração WhatsApp, Facebook e Instagram",
  "url": "https://conheca.<?php echo $simple_url; ?>/",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "BRL",
    "priceValidUntil": "2025-12-31"
  },
  "provider": {
    "@type": "Organization",
    "name": "<?php echo $nome_loja; ?>",
    "url": "https://<?php echo $simple_url; ?>"
  }
}
</script>
<link href="https://conheca.<?php echo $simple_url; ?>/assets/img/favicon.png" rel="icon">
<link href="https://conheca.<?php echo $simple_url; ?>/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
<link href="assets/vendor/aos/aos.css" rel="stylesheet">
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">   
<style>
    /* Estilos adicionais mantendo a estrutura original */
    .testimonials {
        padding: 80px 0;
    }
    .testimonial-item {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .testimonial-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .stars {
        color: #ffc107;
        margin-bottom: 15px;
    }
    .testimonial-img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
    }
    /* Melhorias de performance e loading */
    .img-fluid {
        transition: opacity 0.3s ease;
    }
    .img-fluid[loading="lazy"] {
        opacity: 0;
    }
    .img-fluid[loading="lazy"].loaded {
        opacity: 1;
    }
    /* Hover effects para demo boxes */
    .count-box {
        transition: transform 0.3s ease;
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .count-box:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    .count-box img {
        max-width: 150px;
        height: 150px;
        object-fit: contain;
        border-radius: 8px;
        margin-bottom: 15px;
        background: #f8f9fa;
        padding: 10px;
    }
    .count-box h4 {
        color: #333;
        font-weight: 600;
        margin: 0;
    }
    /* Melhor responsividade */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 1.8rem;
        }
        .hero h2 {
            font-size: 1.1rem;
        }
        .section-title h2 {
            font-size: 1.8rem;
        }
    }
    /* Indicador de carregamento de vídeo */
    .video-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #FF6E41;
        font-size: 2rem;
    }
    /* Placeholder para imagens que não carregam */
    .img-placeholder {
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        border-radius: 8px;
        margin: 0 auto 15px;
    }
</style>
</head>
<body>
<header id="header" class="fixed-top d-flex align-items-center header-transparent">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo">
            <a href="index.html">
                <img src="assets/img/logowhite.png" alt="" class="img-fluid">
            </a>
        </div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto active" href="#hero">Inicial</a></li>
                <li><a class="nav-link scrollto" href="#about">Passo a Passo</a></li>
                <li><a class="nav-link scrollto" href="#features">Funcionalidades</a></li>
                <li><a class="nav-link scrollto" href="#testimonials">Depoimentos</a></li>
                <li><a class="nav-link scrollto" href="#faq">Dúvidas</a></li>
                <li><a class="nav-link scrollto" href="#pricing">Contrate</a></li>
                <li><a class="nav-link scrollto" href="#contact">Contato</a></li>
            </ul> 
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
    </div>
</header>

<section id="hero">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-7 pt-5 pt-lg-0 order-2 order-lg-1 d-flex align-items-center">
                <div data-aos="zoom-out">
                    <h1>Transforme seus seguidores em <span>clientes pagantes</span></h1>
                    <h2>Crie seu catálogo digital profissional em minutos e venda 24h por dia no WhatsApp, Instagram e Facebook. Mais de 30 funcionalidades para impulsionar suas vendas automaticamente!</h2>
                    <div class="text-center text-lg-start"> 
                        <a href="https://<?php echo $simple_url; ?>/comece" class="btn-get-started scrollto">Começar Agora Grátis</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="300">
                <img src="assets/img/hero-img.png" class="img-fluid animated" alt="Ilustração de catálogo digital conectado às redes sociais" loading="lazy">
            </div>
        </div>
    </div>
    <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
        <defs>
            <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
        </defs>
        <g class="wave1">
            <use xlink:href="#wave-path" x="50" y="3" fill="rgba(255,255,255, .1)"></use>
        </g>
        <g class="wave2">
            <use xlink:href="#wave-path" x="50" y="0" fill="rgba(255,255,255, .2)"></use>
        </g>
        <g class="wave3">
            <use xlink:href="#wave-path" x="50" y="9" fill="#fff"></use>
        </g>
    </svg>
</section>



<main id="main">
    <section id="about" class="about">
        <div class="container-fluid">
            <div class="row">
                <?php
                // Tratamento do vídeo
                $video_embed = '';
                $link = $link_video_landing;

                if (strpos($link, 'youtube.com/watch') !== false || strpos($link, 'youtu.be') !== false) {
                    parse_str(parse_url($link, PHP_URL_QUERY), $yt_params);
                    $video_id = isset($yt_params['v']) ? $yt_params['v'] : basename(parse_url($link, PHP_URL_PATH));
                    $video_embed = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $video_id . '?autoplay=1" frameborder="0" allowfullscreen></iframe>';
                } elseif (strpos($link, 'youtube.com/embed') !== false) {
                    $video_embed = '<iframe width="100%" height="315" src="' . $link . '?autoplay=1" frameborder="0" allowfullscreen></iframe>';
                } elseif (preg_match('/\.(mp4|webm|ogg)$/', $link)) {
                    $ext = pathinfo($link, PATHINFO_EXTENSION);
                    $video_embed = '<video width="100%" height="315" controls autoplay><source src="' . $link . '" type="video/' . $ext . '">Seu navegador não suporta vídeo HTML5.</video>';
                } else {
                    $video_embed = '<p style="color: red;">Formato de vídeo não suportado.</p>';
                }

                // Caminho da imagem de miniatura padrão
                $thumbnail_path = 'assets/img/miniatura.png';
                ?>

                <div class="col-xl-5 col-lg-6 video-box d-flex justify-content-center align-items-center" data-aos="fade-right">
                    <div id="video-container" style="width: 100%; cursor: pointer; position: relative;">
                        <img src="<?php echo $thumbnail_path; ?>" alt="Miniatura do Vídeo - Como funciona o <?php echo $nome_loja; ?>" style="width: 100%; height: auto; border-radius: 8px;" loading="lazy">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(255, 255, 255, 0.9); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-play-fill" style="font-size: 2rem; color: #FF6E41; margin-left: 5px;"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7 col-lg-6 icon-boxes d-flex flex-column align-items-stretch justify-content-center py-5 px-lg-5" data-aos="fade-left">
                    <h3>3 passos simples para automatizar suas vendas e multiplicar seu faturamento</h3>
                    <p>O catálogo digital mais completo do Brasil. Venda mais, trabalhe menos e conquiste a liberdade financeira que você merece!</p>

                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon"><i class='bx bx-store'></i></div>
                        <h4 class="title"><a href="#">1. Configure em minutos</a></h4>
                        <p class="description">Personalize completamente seu catálogo com suas cores, logo e identidade. Tenha um link exclusivo e profissional em menos de 5 minutos.</p>
                    </div>

                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="200">
                        <div class="icon"><i class='bx bxs-t-shirt'></i></div>
                        <h4 class="title"><a href="#">2. Adicione seus produtos</a></h4>
                        <p class="description">Cadastre produtos, categorias, preços e fotos de forma super fácil. Sistema inteligente que otimiza tudo automaticamente.</p>
                    </div>

                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="300">
                        <div class="icon"><i class='bx bx-link-alt'></i></div>
                        <h4 class="title"><a href="#">3. Comece a lucrar</a></h4>
                        <p class="description">Compartilhe seu link e venda automaticamente 24h por dia. Pedidos chegam direto no seu WhatsApp organizados e prontos!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Função para carregar vídeo
    document.getElementById('video-container').addEventListener('click', function() {
        // Adiciona loading state
        this.innerHTML = '<div class="video-loading"><i class="bi bi-hourglass-split"></i></div>';
        
        // Simula um pequeno delay para melhor UX
        setTimeout(() => {
            this.innerHTML = `<?php echo $video_embed; ?>`;
        }, 500);
    });

    // Lazy loading para imagens
    document.addEventListener('DOMContentLoaded', function() {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.addEventListener('load', () => {
                            img.classList.add('loaded');
                        });
                        observer.unobserve(img);
                    }
                });
            });

            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback para navegadores antigos
            lazyImages.forEach(img => img.classList.add('loaded'));
        }

        // Gerenciar erro de carregamento de imagens das demos
        const demoImages = document.querySelectorAll('.count-box img');
        demoImages.forEach(img => {
            img.addEventListener('error', function() {
                // Cria um placeholder CSS se a imagem não carregar
                const placeholder = document.createElement('div');
                placeholder.className = 'img-placeholder';
                placeholder.textContent = this.alt.split(' ')[0] || 'Demo';
                this.parentNode.replaceChild(placeholder, this);
            });
        });
    });

    // Smooth scroll melhorado
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    </script>
</main>


    
    

    <section id="features" class="features">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Por que escolher o <?php echo $nome_loja; ?>?</h2>
                <p>A plataforma mais completa e fácil para vender online</p>
            </div>
            <div class="row" data-aos="fade-left">
                <div class="col-lg-3 col-md-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="50"> 
                        <i class='bx bxl-whatsapp' style="color:#009900;"></i>
                        <h3><a href="">Link no WhatsApp</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4 mt-md-0">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="100"> 
                        <i class='bx bxl-facebook' style="color: #5578ff;"></i>
                        <h3><a href="">No Facebook</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4 mt-md-0">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="150"> 
                        <i class='bx bxl-instagram' style="color: #e80368;"></i>
                        <h3><a href="">No Instagram</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4 mt-lg-0">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="200"> 
                        <i class="ri-paint-brush-line" style="color: #e361ff;"></i>
                        <h3><a href="">Cores Personalizadas</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="250"> 
                        <i class='bx bx-link' style="color:#CC3300;"></i>
                        <h3><a href="">URL exclusiva</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="300"> 
                        <i class='bx bx-shape-square' style="color: #ffa76e;"></i>
                        <h3><a href="">Produtos e Variações</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="350"> 
                        <i class='bx bxs-cart-add' style="color: #11dbcf;"></i>
                        <h3><a href="">Cesta de Compras</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="400"> 
                        <i class='bx bxs-file-image' style="color: #4233ff;"></i>
                        <h3><a href="">Galeria de Fotos</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="450"> 
                        <i class='bx bxs-devices' style="color: #b2904f;"></i>
                        <h3><a href="">100% responsivo</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="500"> 
                        <i class='bx bxs-offer' style="color: #b20969;"></i>
                        <h3><a href="">Produtos em Oferta</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="550"> 
                        <i class="ri-base-station-line" style="color: #ff5828;"></i>
                        <h3><a href="">Pedido no Whats</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bxs-shopping-bags' style="color: #29cc61;"></i>
                        <h3><a href="">PWA Automático</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-qr' style="color: #FF0000;"></i>
                        <h3><a href="">QR-Code Empresa</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-qr-scan' style="color: #9966CC;"></i>
                        <h3><a href="">QR-Code de Atendimento</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-checkbox-checked' style="color: #CCCC33;"></i>
                        <h3><a href="">Painel de Pedidos</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-health' style="color: #666666;"></i>
                        <h3><a href="">Pagamento via PIX</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-bell' style="color: #FFA500;"></i>
                        <h3><a href="">Alerta de Novos Pedidos</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-credit-card' style="color: #0099CC;"></i>
                        <h3><a href="">Pagamento com Mercado Pago</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-store' style="color: #663399;"></i>
                        <h3><a href="">PDV Nativo</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-truck' style="color: #339933;"></i>
                        <h3><a href="">Cálculo de Frete</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-bike' style="color: #FF6600;"></i>
                        <h3><a href="">Sistema de Delivery</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-map' style="color: #0066CC;"></i>
                        <h3><a href="">Rastreamento de Encomendas</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-gift' style="color: #FF3399;"></i>
                        <h3><a href="">Cupons</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-stats' style="color: #993366;"></i>
                        <h3><a href="">Relatórios</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="600"> 
                        <i class='bx bx-support' style="color: #333333;"></i>
                        <h3><a href="">Suporte Técnico</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="650"> 
                        <i class='bx bx-table' style="color: #4CAF50;"></i>
                        <h3><a href="">Gestão de Mesas</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="700"> 
                        <i class='bx bxs-user-badge' style="color: #2196F3;"></i>
                        <h3><a href="">Gestão de Garçons</a></h3>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mt-4">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="750"> 
                        <i class='bx bx-link-external' style="color: #E91E63;"></i>
                        <h3><a href="">Vendas Com Link Externo</a></h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="counts" class="counts">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Exemplos Reais de Sucesso</h2>
                <p>Veja como nossos clientes estão faturando mais com seus catálogos digitais</p>
            </div>
            <div class="row" data-aos="fade-up">
                <div class="col-lg-3 col-md-6">
                    <a href="https://shopburger.<?php echo $simple_url; ?>" target="_blank" rel="noopener" aria-label="Ver demo da Hamburgueria">
                        <div class="count-box">
                            <img src="https://shopburger.<?php echo $simple_url; ?>/_core/_uploads/186/2023/02/10012702235j4bejba4k_thumb.png" width="150" class="img-fluid" alt="Logo da Hamburgueria - Demo do catálogo" onerror="this.src='assets/img/placeholder-demo.png'; this.onerror=null;">
                            <h4>Hamburgueria</h4>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mt-5 mt-md-0">
                    <a href="https://demo2.<?php echo $simple_url; ?>" target="_blank" rel="noopener" aria-label="Ver demo da Beaut Boutique">
                        <div class="count-box">
                            <img src="https://demo2.<?php echo $simple_url; ?>/_core/_uploads/28/2020/09/0058190920dedg383f0b_thumb.jpg" width="150" class="img-fluid" alt="Logo da Beaut Boutique - Demo do catálogo" onerror="this.src='assets/img/placeholder-demo.png'; this.onerror=null;">
                            <h4>Beaut Boutique</h4>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0">
                    <a href="https://motorcycle.<?php echo $simple_url; ?>" target="_blank" rel="noopener" aria-label="Ver demo da Motorcycle">
                        <div class="count-box">
                            <img src="https://motorcycle.<?php echo $simple_url; ?>/_core/_uploads/153/2023/02/142621022308efh6813k_thumb.png" width="150" class="img-fluid" alt="Logo da Motorcycle - Demo do catálogo" onerror="this.src='assets/img/placeholder-demo.png'; this.onerror=null;">
                            <h4>Motorcycle</h4>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mt-5 mt-lg-0">
                    <a href="https://demo4.<?php echo $simple_url; ?>" target="_blank" rel="noopener" aria-label="Ver demo do PetShop">
                        <div class="count-box">
                            <img src="https://demo4.<?php echo $simple_url; ?>/_core/_uploads/39/2021/11/1453271121bhhke2bgkg_thumb.jpg" width="150" class="img-fluid" alt="Logo do PetShop - Demo do catálogo" onerror="this.src='assets/img/placeholder-demo.png'; this.onerror=null;">
                            <h4>O PetShop</h4>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Nova Seção: Depoimentos -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>O que nossos clientes estão dizendo</h2>
                <p>Histórias reais de quem transformou seu negócio</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-item">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p>
                            "Incrível! Em apenas 3 meses minhas vendas online aumentaram 85%. O sistema é tão fácil que até minha mãe consegue usar!"
                        </p>
                        <div class="profile mt-auto">
                            <img src="assets/img/ana.png" class="testimonial-img" alt="Ana Silva">
                            <h3>Ana Silva</h3>
                            <h4>Boutique de Moda</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-item">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p>
                            "Revolucionou meu restaurante! Em 20 minutos já estava recebendo pedidos. Meus clientes amam a praticidade!"
                        </p>
                        <div class="profile mt-auto">
                            <img src="assets/img/carlos.png" class="testimonial-img" alt="Carlos Oliveira">
                            <h3>Carlos Oliveira</h3>
                            <h4>Restaurante</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-item">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star"></i>
                        </div>
                        <p>
                            "Fantástico! A integração com Instagram foi um divisor de águas. Agora vendo direto pelos Stories e posts. Recomendo de olhos fechados!"
                        </p>
                        <div class="profile mt-auto">
                            <img src="assets/img/mariano.png" class="testimonial-img" alt="Mariana Costa">
                            <h3>Mariano Costa</h3>
                            <h4>Loja de Cosméticos</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="pricing">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Escolha Seu Plano</h2>
                <p>Comece grátis e escale conforme seu negócio cresce</p>
            </div>
            <div class="row" data-aos="fade-left">
                <?php
                $query = "";
                $query .= "SELECT * FROM planos ";
                $query .= "WHERE 1=1 ";
                $query .= "AND status = '1' AND visible = '1' ";
                $query_full = $query;
                $query .= "ORDER BY ordem ASC";

                $sql = mysqli_query( $db_con, $query );
                $total_results = mysqli_num_rows( $sql );

                $firstPlan = true; // Variável de controle para o primeiro plano

                while ( $data = mysqli_fetch_array( $sql ) ) {
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="box" data-aos="zoom-in" data-aos-delay="100">
                        <span class="advanced <?php echo $firstPlan ? '' : 'd-none'; ?>"><?php echo $data['nome']; ?></span>
                        <h3><?php echo $data['nome']; ?></h3>
                        <h4 class="<?php echo $firstPlan ? 'd-none' : ''; ?>"><sup>R$</sup><?php echo dinheiro( $data['valor_mensal'], "BR" ); ?> <sub>/mês</sub></h4>
                        <h4 class="<?php echo $firstPlan ? '' : 'd-none'; ?>"><?php echo data_info( "planos",$plano_default,"duracao_dias" ); ?> <sub>dias grátis</sub></h4>
                        
                        <h6 class="align-left <?php echo $firstPlan ? 'd-none' : ''; ?>">R$<?php echo dinheiro( $data['valor_total'], "BR" ); ?> <sub>no total</sub></h6>
                        <br/>

                        <ul>
                            <?php
                            $descricao = $data['descricao'];
                            $linhas = explode("\n", $descricao);

                            foreach($linhas as $linha) {
                                echo "<li>" . $linha . "</li>";
                            }
                            ?>
                        </ul>
                        <div class="btn-wrap">
                            <?php if($firstPlan) { ?>
                                <a href="https://<?php echo $simple_url; ?>/comece" class="btn-buy">Começar Grátis Agora</a>
                            <?php } else { ?>
                                Comece pelo plano gratuito
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
                    $firstPlan = false;
                }
                ?>
            </div>
        </div>
    </section>

    <section id="faq" class="faq section-bg">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Dúvidas Frequentes</h2>
                <p>Tire todas suas dúvidas antes de começar</p>
            </div>
            <div class="faq-list">
                <ul>
                    <li data-aos="fade-up"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" class="collapse" data-bs-target="#faq-list-1">
                            Por que começar com o teste gratuito? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-1" class="collapse show" data-bs-parent=".faq-list">
                            <p>
                                Simples: queremos que você comprove na prática como nossa plataforma vai transformar seu negócio! Você terá <strong><?php echo data_info( "planos",$plano_default,"duracao_dias" ); ?> dias</strong> para testar TODAS as funcionalidades gratuitamente. Só depois decida se quer continuar.
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="100"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-2" class="collapsed">
                            Quanto tempo leva para começar a vender? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-2" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Seu acesso é liberado INSTANTANEAMENTE após o cadastro! Em menos de 30 minutos você já pode estar recebendo seus primeiros pedidos. É só cadastrar, personalizar e compartilhar!
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="200"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-3" class="collapsed">
                            Qual a forma de pagamento? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-3" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Ao contratar um de nossos planos você será redirecionado ao MERCADOPAGO onde poderá escolher o pagamento via: CARTÃO DE CRÉDITO, CARTÃO DE DÉBITO OU BOLETO.
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="300"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-4" class="collapsed">
                            Como vou receber e organizar meus pedidos? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-4" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Muito simples! Todos os pedidos chegam automaticamente no seu WhatsApp, já formatados e prontos para impressão. Além disso, você tem um painel online GRATUITO para gerenciar tudo de forma profissional.
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="400"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-5" class="collapsed">
                            Preciso de conhecimento técnico ou aplicativos? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-5" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Zero conhecimento técnico necessário! Funciona direto no navegador do celular ou computador. Seus clientes também podem instalar como um app nativo no celular (PWA) para acesso ainda mais rápido.
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="400"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-6" class="collapsed">
                            O que é PWA? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-6" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                O nosso sistema conta com a tecnologia PWA que oferece ao seu cliente a possibilidade de instalar um WEBAPP direto no celular e assim ter o seu catálogo instalado direto no celular sem a necessidade de acessar pelo link.
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="400"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-7" class="collapsed">
                            Funciona com a lojinha do Instagram? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-7" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Perfeitamente! Nossa equipe te ajuda a configurar a integração completa com Instagram Shopping. Seus seguidores compram sem sair da rede social e você vende mais sem esforço!
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="400"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-8" class="collapsed">
                            Funciona para qualquer tipo de negócio? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-8" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Sim! Restaurantes, lojas de roupas, cosméticos, serviços, imobiliárias, produtos digitais... Qualquer negócio que vende pode usar o <?php echo $nome_loja; ?> para aumentar suas vendas online!
                            </p>
                        </div>
                    </li>
                    <li data-aos="fade-up" data-aos-delay="400"> 
                        <i class="bx bx-help-circle icon-help"></i>  
                        <a data-bs-toggle="collapse" data-bs-target="#faq-list-9" class="collapsed">
                            E se eu não gostar? Posso cancelar? 
                            <i class="bx bx-chevron-down icon-show"></i>
                            <i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-9" class="collapse" data-bs-parent=".faq-list">
                            <p>
                                Claro! Você tem 7 dias de garantia total. Se não ficar 100% satisfeito, devolvemos todo seu dinheiro sem perguntas. Depois desse prazo, pode cancelar quando quiser sem multas.
                            </p>
                        </div>
                    </li>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section id="contact" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Pronto para Começar?</h2>
            </div>
            <div class="row" align="center">
                <div class="col-lg-6 col-md-6" style="margin-bottom:10px;">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="50"> 
                        <i class='bx bxl-whatsapp' style="color:#009900;"></i>
                        <h3><a class="box btn-wrap" href="https://wa.me/<?php echo $whatsapp; ?>">Tire suas dúvidas agora</a></h3>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6" style="margin-bottom:10px;">
                    <div class="icon-box" data-aos="zoom-in" data-aos-delay="50"> 
                        <i class='bx bx-mail-send' style="color:#FF6600;"></i>
                        <h3><a class="box btn-wrap" href="mailto:contato@<?php echo $simple_url; ?>">Ou envie um e-mail</a></h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer id="footer">
    <div class="container">
        <div class="copyright">
            &copy; Copyright <strong><span><?php echo $nome_loja; ?></span></strong>
            <br/>Todos os direitos reservados
        </div>
    </div>
</footer>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<div id="preloader"></div>

<script src="assets/vendor/purecounter/purecounter.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/js/main.js"></script>

<!-- Google Analytics (substitua GA_MEASUREMENT_ID pelo seu ID) -->
<!--
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
-->

</body>
</html>