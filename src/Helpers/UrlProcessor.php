<?php

namespace SmsCatcher\Helpers;

class UrlProcessor
{
    /**
     * Convert plain text URLs to clickable links that open in a new window
     *
     * @param string $text
     * @return string
     */
    public static function linkify(string $text): string
    {
        // First escape the text to prevent XSS
        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        // Pattern to match URLs - supports http, https, and naked domain names
        $pattern = '/(https?:\/\/[^\s<>"{}|\\^`\[\]]+)|(\b(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}(?:\/[^\s<>"{}|\\^`\[\]]*)?)/i';
        
        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[0];
            
            // Add protocol if missing
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'http://' . $url;
            }
            
            // Create the link - URL is already escaped from the text
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $matches[0] . '</a>';
        }, $escapedText);
    }
}