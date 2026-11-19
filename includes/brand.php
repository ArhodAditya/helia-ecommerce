<?php
function heliaMark($class = '', $size = 36)
{
    $classes = trim('helia-mark ' . $class);
    $size = (int) $size;

    return '<svg class="' . $classes . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
        . '<rect class="hm-tile" width="48" height="48" rx="13" fill="#3038a6"/>'
        . '<path class="hm-sun" d="M16.5 24a7.5 7.5 0 0 1 15 0z" fill="#f6a019"/>'
        . '<path class="hm-ray" d="M24 11.5v2.75M18.4 13.6l1.3 2.1M29.6 13.6l-1.3 2.1" fill="none" stroke="#f6a019" stroke-width="1.9" stroke-linecap="round"/>'
        . '<path class="hm-glyph" d="M11 11h5.5v13h15V11H37v26h-5.5v-8h-15v8H11z" fill="#ffffff"/>'
        . '</svg>';
}

function heliaLogo($tag = '', $class = '', $size = 36)
{
    $html = '<span class="helia-logo ' . $class . '">';
    $html .= heliaMark('', $size);
    $html .= '<span class="helia-wordmark">Helia</span>';

    if ($tag !== '') {
        $html .= '<span class="helia-tag">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    $html .= '</span>';

    return $html;
}

function categoryIconKey($name)
{
    $name = strtolower((string) $name);

    $keywords = [
        'electronics' => ['electronic', 'gadget', 'mobile', 'phone', 'computer', 'tech'],
        'sports' => ['sport', 'fitness', 'cricket', 'football', 'gym', 'outdoor'],
        'fashion' => ['fashion', 'apparel', 'cloth', 'lifestyle', 'wear', 'shoe'],
        'home' => ['home', 'kitchen', 'living', 'furniture', 'appliance'],
        'books' => ['book', 'stationery', 'office', 'school'],
        'health' => ['health', 'beauty', 'personal care', 'care', 'cosmetic', 'wellness'],
    ];

    foreach ($keywords as $key => $words) {
        foreach ($words as $word) {
            if (strpos($name, $word) !== false) {
                return $key;
            }
        }
    }

    return 'default';
}

function categoryIconSvg($key, $size = 26)
{
    $paths = [
        'electronics' => '<path d="M4.5 15.5V12a7.5 7.5 0 0 1 15 0v3.5"/><rect x="3.25" y="13.75" width="4.5" height="7" rx="1.75"/><rect x="16.25" y="13.75" width="4.5" height="7" rx="1.75"/>',
        'sports' => '<g transform="rotate(45 11 11)"><rect x="8.25" y="8.25" width="5.5" height="12.75" rx="1.75"/><path d="M11 8.25V2.75"/></g><circle cx="18.25" cy="18.25" r="2.9"/><path d="M16.2 16.95c1.15.35 2.15 1.35 2.5 2.5"/>',
        'fashion' => '<path d="M8.75 3.5 4 6.1l1.9 4.3 2.35-1.05V20.5h7.5V9.35l2.35 1.05L20 6.1 15.25 3.5c-.55 1.5-1.75 2.4-3.25 2.4s-2.7-.9-3.25-2.4z"/><path d="M12 5.9v5.6"/>',
        'home' => '<path d="M6.75 11h10.5l1.5 9.5H5.25z"/><path d="M8.25 11V9.5a3.75 3.75 0 0 1 7.5 0V11"/><path d="M17.6 14 21 11.75"/><path d="M12 5.75V4.5"/>',
        'books' => '<path d="M12 6.75C10 5.1 7.1 4.6 3.75 5.1v13.4c3.35-.5 6.25 0 8.25 1.65 2-1.65 4.9-2.15 8.25-1.65V5.1C16.9 4.6 14 5.1 12 6.75z"/><path d="M12 6.75v13.4"/>',
        'health' => '<rect x="6.75" y="9.5" width="10.5" height="11.25" rx="2"/><path d="M10 9.5V7h4v2.5"/><path d="M12 7V3.75h4"/><path d="M12 12.75v4.5M9.75 15h4.5"/>',
        'all' => '<rect x="4" y="4" width="6.75" height="6.75" rx="1.75"/><rect x="13.25" y="4" width="6.75" height="6.75" rx="1.75"/><rect x="4" y="13.25" width="6.75" height="6.75" rx="1.75"/><rect x="13.25" y="13.25" width="6.75" height="6.75" rx="1.75"/>',
        'default' => '<path d="M3.75 12.6V4.75a1 1 0 0 1 1-1h7.85a1 1 0 0 1 .7.3l7.5 7.5a1 1 0 0 1 0 1.4l-7.85 7.85a1 1 0 0 1-1.4 0l-7.5-7.5a1 1 0 0 1-.3-.7z"/><circle cx="8.5" cy="8.5" r="1.4"/>',
    ];

    $inner = $paths[$key] ?? $paths['default'];
    $size = (int) $size;

    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

function categoryIcon($name, $key = null, $size = 26)
{
    $key = $key ?? categoryIconKey($name);

    return '<span class="category-icon cat-' . $key . '">' . categoryIconSvg($key, $size) . '</span>';
}
