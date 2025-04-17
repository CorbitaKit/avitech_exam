<?php

namespace App\Traits;

trait HtmlGenerator
{
    public function generateHtml($emails): string
    {
        $html =  <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: DejaVu Sans, sans-serif; font-size: 14px; line-height: 1.6; }
                    .email { margin-bottom: 40px; page-break-inside: avoid; }
                    .header { font-weight: bold; margin-bottom: 5px; }
                    .timestamp { font-size: 12px; color: gray; }
                    .body { white-space: pre-wrap; margin-top: 10px; }
                </style>
            </head>
            <body>
            HTML;

            foreach ($emails as $email) {
                $html .= <<<EMAIL
                <div class="email">
                    <div class="header">{$email['from']} → {$email['to']}</div>
                    <div class="timestamp">{$email['timestamp']}</div>
                    <div class="body">{$email['body']}</div>
                </div>
            EMAIL;
            }

            $html .= '</body></html>';

            return $html;
    }
}
