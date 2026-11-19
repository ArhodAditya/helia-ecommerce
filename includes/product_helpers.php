<?php
function renderStockBadge($stock)
{
    if ($stock > 5) {
        return '<span class="badge stock-badge stock-in"><span class="stock-dot"></span>In Stock</span>';
    }

    if ($stock >= 1) {
        return '<span class="badge stock-badge stock-low"><span class="stock-dot"></span>Low Stock</span>';
    }

    return '<span class="badge stock-badge stock-out"><span class="stock-dot"></span>Out of Stock</span>';
}

function getColorList($colorVariants)
{
    $colors = [];

    foreach (explode(',', (string) $colorVariants) as $color) {
        $color = trim($color);

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $colors[] = strtoupper($color);
        }
    }

    return $colors;
}

function renderColorSwatches($colorVariants)
{
    $html = '';

    foreach (getColorList($colorVariants) as $color) {
        $html .= '<span class="color-dot" style="background-color: ' . $color . ';" title="' . $color . '"></span>';
    }

    return $html;
}

function productImageExists($image)
{
    $image = trim((string) $image);

    if ($image === '' || strpos($image, 'uploads/') !== 0 || strpos($image, '..') !== false) {
        return false;
    }

    return is_file(dirname(__DIR__) . '/' . $image);
}

function getProductImage($image)
{
    if (productImageExists($image)) {
        return trim((string) $image);
    }

    return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
}

function renderProductImage($image, $alt = '', $class = '', $size = 'md', $pathPrefix = '', $lazy = true)
{
    $class = trim((string) $class);
    $alt = (string) $alt;

    if (productImageExists($image)) {
        return '<img src="' . e($pathPrefix . trim((string) $image)) . '" alt="' . e($alt) . '"'
            . ($class !== '' ? ' class="' . e($class) . '"' : '')
            . ($lazy ? ' loading="lazy"' : '') . '>';
    }

    $classes = 'photo-empty photo-empty-' . $size . ($class !== '' ? ' ' . $class : '');
    $label = $alt !== '' ? ' role="img" aria-label="' . e('No photo of ' . $alt) . '"' : ' aria-hidden="true"';

    return '<div class="' . e($classes) . '"' . $label . '>'
        . '<i class="bi bi-image" aria-hidden="true"></i>'
        . '<span>No Photo</span>'
        . '</div>';
}

function renderStars($rating)
{
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }

    return $html;
}
