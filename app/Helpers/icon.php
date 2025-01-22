<?php

if (!function_exists('render_icon')) {
    function render_icon(string $name, string $color = 'currentColor', int $size = 12): string {
        try {
            return svg($name, ['w-5', 'h-5'], ['style' => "color: {$color}"])
                ->toHtml();
        } catch (\Exception $e) {
            return "<span style='color: {$color}; font-size: {$size}px'>[?]</span>";
        }
    }
}
