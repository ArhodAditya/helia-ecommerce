<?php
function renderStockBadge($stock)
{
    if ($stock > 5) {
        return '<span class="badge text-bg-success">In Stock</span>';
    }

    if ($stock >= 1) {
        return '<span class="badge text-bg-warning">Low Stock</span>';
    }

    return '<span class="badge text-bg-danger">Out of Stock</span>';
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

function getProductImage($image)
{
    if (!empty($image) && file_exists($image)) {
        return $image;
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
        . '<rect width="400" height="400" fill="#6c757d" fill-opacity="0.15"/>'
        . '<circle cx="165" cy="160" r="22" fill="#6c757d" fill-opacity="0.45"/>'
        . '<path d="M110 270 L170 200 L210 245 L245 210 L290 270 Z" fill="#6c757d" fill-opacity="0.45"/>'
        . '<text x="200" y="320" font-family="Arial, sans-serif" font-size="22" fill="#6c757d" text-anchor="middle">No Image</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
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
