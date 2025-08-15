<?php
// Gerar ícones PWA básicos usando GD
function createIcon($size) {
    $img = imagecreatetruecolor($size, $size);
    
    // Cores
    $bg1 = imagecolorallocate($img, 102, 126, 234); // #667eea
    $bg2 = imagecolorallocate($img, 118, 75, 162);  // #764ba2
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Background gradient (simulado)
    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $r = 102 + ($ratio * (118 - 102));
        $g = 126 + ($ratio * (75 - 126));
        $b = 234 + ($ratio * (162 - 234));
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $size, $y, $color);
    }
    
    // Círculo branco (prato)
    $center = $size / 2;
    $radius = $size * 0.35;
    imagefilledellipse($img, $center, $center, $radius * 2, $radius * 2, $white);
    
    // Garfo e faca (simplificados)
    $fork_x = $center - $size * 0.15;
    $knife_x = $center + $size * 0.15;
    $utensil_y = $center - $size * 0.1;
    $utensil_height = $size * 0.2;
    
    // Desenhar utensílios
    imagefilledrectangle($img, $fork_x - 2, $utensil_y, $fork_x + 2, $utensil_y + $utensil_height, $bg1);
    imagefilledrectangle($img, $knife_x - 2, $utensil_y, $knife_x + 2, $utensil_y + $utensil_height, $bg1);
    
    return $img;
}

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

foreach ($sizes as $size) {
    $img = createIcon($size);
    imagepng($img, "icon-{$size}x{$size}.png");
    imagedestroy($img);
    echo "Criado: icon-{$size}x{$size}.png\n";
}

echo "Todos os ícones foram criados!\n";
?>
