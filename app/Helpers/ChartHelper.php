<?php

namespace App\Helpers;

/**
 * ChartHelper - Generate chart images using PHP GD library
 * 
 * This helper provides methods to generate bar charts and pie charts
 * as PNG images that can be embedded in PDFs.
 */
class ChartHelper
{
    /**
     * Generate a horizontal bar chart
     *
     * @param array $data Array of ['label' => string, 'value' => int, 'color' => string]
     * @param array $options Chart options
     * @return string Base64 encoded PNG image
     */
    public static function generateBarChart($data, $options = [])
    {
        // Default options
        $width = $options['width'] ?? 800;
        $height = $options['height'] ?? 500;
        $title = $options['title'] ?? '';
        $bgColor = $options['bgColor'] ?? [255, 255, 255];
        $textColor = $options['textColor'] ?? [51, 51, 51];
        
        // Create image
        $image = imagecreatetruecolor($width, $height);
        
        // Allocate colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        $text = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
        $gridColor = imagecolorallocate($image, 220, 220, 220);
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        
        // Draw title
        if ($title) {
            $titleFont = 5;
            $titleWidth = imagefontwidth($titleFont) * strlen($title);
            imagestring($image, $titleFont, ($width - $titleWidth) / 2, 15, $title, $text);
        }
        
        // Calculate chart area
        $chartTop = $title ? 50 : 20;
        $chartBottom = $height - 40;
        $chartLeft = 200;
        $chartRight = $width - 100;
        $chartWidth = $chartRight - $chartLeft;
        $chartHeight = $chartBottom - $chartTop;
        
        // Find max value
        $maxValue = 0;
        foreach ($data as $item) {
            if ($item['value'] > $maxValue) {
                $maxValue = $item['value'];
            }
        }
        
        if ($maxValue == 0) $maxValue = 1; // Prevent division by zero
        
        // Calculate bar height
        $barHeight = ($chartHeight / count($data)) - 10;
        $barSpacing = 10;
        
        // Draw grid lines
        for ($i = 0; $i <= 5; $i++) {
            $x = $chartLeft + ($chartWidth / 5) * $i;
            imageline($image, $x, $chartTop, $x, $chartBottom, $gridColor);
            
            // Grid value labels
            $value = round(($maxValue / 5) * $i);
            imagestring($image, 3, $x - 15, $chartBottom + 5, $value, $text);
        }
        
        // Draw bars
        $y = $chartTop;
        foreach ($data as $item) {
            // Parse color
            $colorHex = ltrim($item['color'], '#');
            $r = hexdec(substr($colorHex, 0, 2));
            $g = hexdec(substr($colorHex, 2, 2));
            $b = hexdec(substr($colorHex, 4, 2));
            $barColor = imagecolorallocate($image, $r, $g, $b);
            
            // Calculate bar width
            $barWidth = ($item['value'] / $maxValue) * $chartWidth;
            
            // Draw bar
            imagefilledrectangle(
                $image,
                $chartLeft,
                $y,
                (int)($chartLeft + $barWidth),
                (int)($y + $barHeight),
                $barColor
            );
            
            // Draw label
            $label = $item['label'];
            imagestring($image, 3, 10, (int)($y + ($barHeight / 2) - 5), $label, $text);
            
            // Draw value on bar or next to it
            $valueText = $item['value'];
            if ($barWidth > 50) {
                imagestring($image, 3, (int)($chartLeft + $barWidth - 35), (int)($y + ($barHeight / 2) - 5), $valueText, $white);
            } else {
                imagestring($image, 3, (int)($chartLeft + $barWidth + 5), (int)($y + ($barHeight / 2) - 5), $valueText, $text);
            }
            
            $y += $barHeight + $barSpacing;
        }
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Generate a pie chart
     *
     * @param array $data Array of ['label' => string, 'value' => int, 'color' => string]
     * @param array $options Chart options
     * @return string Base64 encoded PNG image
     */
    public static function generatePieChart($data, $options = [])
    {
        // Default options
        $size = $options['size'] ?? 600;
        $title = $options['title'] ?? '';
        $bgColor = $options['bgColor'] ?? [255, 255, 255];
        $textColor = $options['textColor'] ?? [51, 51, 51];
        
        // Create image
        $width = $size + 400; // Extra space for legend
        $height = $size + 100;
        $image = imagecreatetruecolor($width, $height);
        
        // Enable anti-aliasing
        imageantialias($image, true);
        
        // Allocate colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        $text = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
        $border = imagecolorallocate($image, 0, 0, 0);
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        
        // Draw title
        if ($title) {
            $titleFont = 5;
            $titleWidth = imagefontwidth($titleFont) * strlen($title);
            imagestring($image, $titleFont, ($width - $titleWidth) / 2, 15, $title, $text);
        }
        
        // Calculate total
        $total = 0;
        foreach ($data as $item) {
            $total += $item['value'];
        }
        
        if ($total == 0) $total = 1; // Prevent division by zero
        
        // Pie chart center and radius
        $centerX = $size / 2 + 50;
        $centerY = ($height / 2) + ($title ? 20 : 0);
        $radius = ($size / 2) - 40;
        
        // Draw pie slices
        $startAngle = 0;
        $colors = [];
        
        foreach ($data as $item) {
            // Parse color
            $colorHex = ltrim($item['color'], '#');
            $r = hexdec(substr($colorHex, 0, 2));
            $g = hexdec(substr($colorHex, 2, 2));
            $b = hexdec(substr($colorHex, 4, 2));
            $sliceColor = imagecolorallocate($image, $r, $g, $b);
            $colors[] = $sliceColor;
            
            // Calculate angle
            $angle = ($item['value'] / $total) * 360;
            $endAngle = $startAngle + $angle;
            
            // Draw slice
            imagefilledarc(
                $image,
                $centerX,
                $centerY,
                (int)($radius * 2),
                (int)($radius * 2),
                (int)$startAngle,
                (int)$endAngle,
                $sliceColor,
                IMG_ARC_PIE
            );
            
            $startAngle = $endAngle;
        }
        
        // Draw border around pie
        imageellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $border);
        
        // Draw legend
        $legendX = $size + 80;
        $legendY = 80;
        $legendBoxSize = 20;
        $legendSpacing = 35;
        
        foreach ($data as $index => $item) {
            // Draw color box
            imagefilledrectangle(
                $image,
                $legendX,
                $legendY,
                $legendX + $legendBoxSize,
                $legendY + $legendBoxSize,
                $colors[$index]
            );
            imagerectangle(
                $image,
                $legendX,
                $legendY,
                $legendX + $legendBoxSize,
                $legendY + $legendBoxSize,
                $border
            );
            
            // Draw label
            $percentage = round(($item['value'] / $total) * 100, 1);
            $label = $item['label'];
            $valueText = $item['value'] . ' (' . $percentage . '%)';
            
            imagestring($image, 3, $legendX + $legendBoxSize + 10, $legendY, $label, $text);
            imagestring($image, 2, $legendX + $legendBoxSize + 10, $legendY + 12, $valueText, $text);
            
            $legendY += $legendSpacing;
        }
        
        // Total at bottom of legend
        $legendY += 10;
        imagestring($image, 4, $legendX, $legendY, 'Total: ' . $total, $text);
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
