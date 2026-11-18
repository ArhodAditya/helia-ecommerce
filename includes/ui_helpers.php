<?php
function renderEmptyState($title, $message, $buttonText = '', $buttonLink = '', $icon = 'bi-inbox')
{
    $html = '<div class="card border-0 shadow-sm">';
    $html .= '<div class="card-body text-center py-5 px-4">';
    $html .= '<span class="empty-state-icon mb-3"><i class="bi ' . e($icon) . '"></i></span>';
    $html .= '<h2 class="h5 fw-semibold">' . e($title) . '</h2>';
    $html .= '<p class="text-body-secondary mb-0">' . e($message) . '</p>';

    if ($buttonText !== '' && $buttonLink !== '') {
        $html .= '<a href="' . e($buttonLink) . '" class="btn btn-primary mt-3">' . e($buttonText) . '</a>';
    }

    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
