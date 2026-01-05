<?php
/**
 * Documentation page class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Docs {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_docs_page'));
    }
    
    /**
     * Add documentation page to admin menu
     */
    public function add_docs_page() {
        add_submenu_page(
            'hs-crm-enquiries',
            'Documentation',
            'Docs',
            'manage_options',
            'hs-crm-docs',
            array($this, 'render_docs_page')
        );
    }
    
    /**
     * Render documentation page
     */
    public function render_docs_page() {
        if (!current_user_can('view_crm_dashboard')) {
            return;
        }
        
        // Load markdown content
        $docs_file = HS_CRM_PLUGIN_DIR . 'GRAVITY_FORMS_INTEGRATION.md';
        
        if (!file_exists($docs_file)) {
            echo '<div class="wrap"><h1>Documentation</h1><p>Documentation file not found.</p></div>';
            return;
        }
        
        $markdown_content = file_get_contents($docs_file);
        
        // Basic markdown to HTML conversion
        $html_content = $this->markdown_to_html($markdown_content);
        
        ?>
        <div class="wrap hs-crm-docs-wrap">
            <h1>Documentation - Gravity Forms Integration</h1>
            <div class="hs-crm-docs-content" style="max-width: 900px; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                <?php echo $html_content; ?>
            </div>
        </div>
        <style>
            .hs-crm-docs-content {
                line-height: 1.6;
            }
            .hs-crm-docs-content h1 {
                border-bottom: 2px solid #0073aa;
                padding-bottom: 10px;
                margin-top: 30px;
            }
            .hs-crm-docs-content h2 {
                border-bottom: 1px solid #ddd;
                padding-bottom: 8px;
                margin-top: 25px;
            }
            .hs-crm-docs-content h3 {
                margin-top: 20px;
                color: #23282d;
            }
            .hs-crm-docs-content code {
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: monospace;
                font-size: 13px;
            }
            .hs-crm-docs-content pre {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 4px;
                overflow-x: auto;
                border-left: 3px solid #0073aa;
            }
            .hs-crm-docs-content pre code {
                background: none;
                padding: 0;
            }
            .hs-crm-docs-content table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
            }
            .hs-crm-docs-content table th,
            .hs-crm-docs-content table td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
            }
            .hs-crm-docs-content table th {
                background: #f5f5f5;
                font-weight: 600;
            }
            .hs-crm-docs-content ul,
            .hs-crm-docs-content ol {
                margin: 10px 0;
                padding-left: 30px;
            }
            .hs-crm-docs-content li {
                margin: 5px 0;
            }
            .hs-crm-docs-content blockquote {
                border-left: 4px solid #ddd;
                padding-left: 15px;
                margin: 15px 0;
                color: #666;
            }
            .hs-crm-docs-content hr {
                border: none;
                border-top: 1px solid #ddd;
                margin: 30px 0;
            }
            .hs-crm-docs-content a {
                color: #0073aa;
                text-decoration: none;
            }
            .hs-crm-docs-content a:hover {
                text-decoration: underline;
            }
        </style>
        <?php
    }
    
    /**
     * Convert markdown to HTML
     * Basic conversion for common markdown syntax
     */
    private function markdown_to_html($markdown) {
        // Escape HTML first
        $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
        
        // Headers (must be done before other conversions)
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
        
        // Code blocks (```...```)
        $html = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $html);
        
        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank">$1</a>', $html);
        
        // Unordered lists - convert to li but don't wrap yet
        $html = preg_replace('/^\* (.+)$/m', ':::UL_ITEM:::$1', $html);
        $html = preg_replace('/^\- (.+)$/m', ':::UL_ITEM:::$1', $html);
        
        // Ordered lists - convert to li but don't wrap yet
        $html = preg_replace('/^\d+\. (.+)$/m', ':::OL_ITEM:::$1', $html);
        
        // Horizontal rule
        $html = preg_replace('/^---$/m', '<hr>', $html);
        
        // Tables (basic support)
        $html = preg_replace_callback('/(\|.+\|\n)+/m', array($this, 'convert_table'), $html);
        
        // Now wrap consecutive list items in proper tags
        $html = preg_replace_callback('/(:::UL_ITEM:::.+\n?)+/m', function($matches) {
            $items = preg_replace('/:::UL_ITEM:::(.+)/', '<li>$1</li>', $matches[0]);
            return '<ul>' . $items . '</ul>';
        }, $html);
        
        $html = preg_replace_callback('/(:::OL_ITEM:::.+\n?)+/m', function($matches) {
            $items = preg_replace('/:::OL_ITEM:::(.+)/', '<li>$1</li>', $matches[0]);
            return '<ol>' . $items . '</ol>';
        }, $html);
        
        // Split into lines for paragraph processing
        $lines = explode("\n", $html);
        $in_paragraph = false;
        $result = array();
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Skip wrapping for special elements
            $is_special = preg_match('/^<(h[1-6]|ul|ol|pre|table|hr|\/ul|\/ol|\/pre|\/table)/', $trimmed);
            
            if (empty($trimmed)) {
                // Empty line closes paragraph
                if ($in_paragraph) {
                    $result[] = '</p>';
                    $in_paragraph = false;
                }
                $result[] = '';
            } elseif ($is_special) {
                // Close paragraph if open
                if ($in_paragraph) {
                    $result[] = '</p>';
                    $in_paragraph = false;
                }
                $result[] = $line;
            } else {
                // Regular text line
                if (!$in_paragraph) {
                    $result[] = '<p>';
                    $in_paragraph = true;
                }
                $result[] = $line . '<br>';
            }
        }
        
        // Close final paragraph if open
        if ($in_paragraph) {
            $result[] = '</p>';
        }
        
        $html = implode("\n", $result);
        
        // Clean up
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        $html = preg_replace('/<br>\s*<\/p>/', '</p>', $html);
        
        return $html;
    }
    
    /**
     * Convert markdown table to HTML
     */
    private function convert_table($matches) {
        $table_content = $matches[0];
        $rows = explode("\n", trim($table_content));
        
        $html = '<table>';
        $is_header = true;
        
        foreach ($rows as $row) {
            $row = trim($row, '|');
            $cells = array_map('trim', explode('|', $row));
            
            // Skip separator rows
            if (preg_match('/^[\s\-|:]+$/', $row)) {
                continue;
            }
            
            if ($is_header) {
                $html .= '<thead><tr>';
                foreach ($cells as $cell) {
                    $html .= '<th>' . htmlspecialchars($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
                $is_header = false;
            } else {
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
}
