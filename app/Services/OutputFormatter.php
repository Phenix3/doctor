<?php

namespace App\Services;

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Enums\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Exception;

class OutputFormatter
{
    /**
     * Default PDF configuration options
     */
    private const DEFAULT_PDF_OPTIONS = [
        'format' => Format::A4,
        'orientation' => Orientation::Portrait,
        'margins' => [
            'top' => 25,
            'right' => 20,
            'bottom' => 25,
            'left' => 20,
            'unit' => Unit::Millimeter
        ],
        'font' => [
            'family' => 'DejaVu Sans',
            'size' => 12
        ],
        'header' => [
            'enabled' => false
        ],
        'footer' => [
            'enabled' => false
        ]
    ];

    /**
     * Generate PDF from document content
     * 
     * Requirements: 1.5, 7.1, 7.2, 7.3
     */
    public function generatePDF(string $content, array $options = []): string
    {
        try {
            // Sanitize content for PDF export (Requirement 7.1, 7.2)
            $sanitizedContent = $this->sanitizeForExport($content);
            
            // Validate content is not empty
            if (empty(trim($sanitizedContent))) {
                throw new InvalidArgumentException('Le contenu du document ne peut pas être vide');
            }

            // Merge options with defaults
            $pdfOptions = array_merge(self::DEFAULT_PDF_OPTIONS, $options);
            
            // Create HTML structure for PDF
            $htmlContent = $this->createHtmlStructure($sanitizedContent, $pdfOptions);
            
            // Generate PDF using Spatie Laravel PDF
            $pdf = Pdf::view('pdf.document', ['content' => $htmlContent])
                ->format($pdfOptions['format'])
                ->orientation($pdfOptions['orientation'])
                ->margins(
                    $pdfOptions['margins']['top'],
                    $pdfOptions['margins']['right'],
                    $pdfOptions['margins']['bottom'],
                    $pdfOptions['margins']['left'],
                    $pdfOptions['margins']['unit']
                );

            // Add header if enabled
            if ($pdfOptions['header']['enabled'] ?? false) {
                $pdf->headerView('pdf.header', $pdfOptions['header']['data'] ?? []);
            }

            // Add footer if enabled
            if ($pdfOptions['footer']['enabled'] ?? false) {
                $pdf->footerView('pdf.footer', $pdfOptions['footer']['data'] ?? []);
            }

            // Generate and return PDF content
            $pdfContent = $pdf->string();
            
            Log::info('PDF generated successfully', [
                'content_length' => strlen($sanitizedContent),
                'pdf_size' => strlen($pdfContent),
                'options' => $pdfOptions
            ]);

            return $pdfContent;

        } catch (InvalidArgumentException $e) {
            Log::warning('PDF generation validation error', [
                'error' => $e->getMessage(),
                'content_length' => strlen($content)
            ]);
            throw $e;

        } catch (Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'content_length' => strlen($content),
                'options' => $options
            ]);
            throw new Exception('Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize content for export (Requirements 7.1, 7.2)
     * Remove HTML tags, formatting codes, and prepare for PDF
     */
    public function sanitizeForExport(string $content): string
    {
        // Remove HTML tags and comments (Requirement 7.1)
        $content = strip_tags($content);
        
        // Remove HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove markdown formatting codes (Requirement 7.1)
        $content = preg_replace('/\*\*(.*?)\*\*/', '$1', $content); // Bold
        $content = preg_replace('/\*(.*?)\*/', '$1', $content); // Italic
        $content = preg_replace('/`(.*?)`/', '$1', $content); // Code
        $content = preg_replace('/#{1,6}\s*/', '', $content); // Headers
        
        // Remove formatting codes and comments (Requirement 7.1)
        $content = preg_replace('/<!--.*?-->/s', '', $content); // HTML comments
        $content = preg_replace('/\[comment\].*?\[\/comment\]/s', '', $content); // Custom comments
        
        // Normalize whitespace for proper spacing (Requirement 7.3)
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content); // Multiple line breaks
        $content = preg_replace('/[ \t]+/', ' ', $content); // Multiple spaces/tabs
        
        // Clean up line breaks and ensure proper structure
        $lines = explode("\n", $content);
        $lines = array_map('trim', $lines);
        
        // Remove empty lines at start and end
        while (!empty($lines) && $lines[0] === '') {
            array_shift($lines);
        }
        while (!empty($lines) && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }
        
        // Ensure proper paragraph spacing for official presentation (Requirement 7.3)
        $cleanedContent = implode("\n", $lines);
        
        // Add proper spacing between sections
        $cleanedContent = preg_replace('/\n([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ][^.!?]*[.!?])\n/', "\n\n$1\n\n", $cleanedContent);
        
        return trim($cleanedContent);
    }

    /**
     * Create HTML structure for PDF generation
     */
    private function createHtmlStructure(string $content, array $options): string
    {
        $fontFamily = $options['font']['family'] ?? 'DejaVu Sans';
        $fontSize = $options['font']['size'] ?? 12;
        
        // Convert plain text to HTML with proper formatting
        $htmlContent = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        
        return "
        <div style='font-family: {$fontFamily}; font-size: {$fontSize}px; line-height: 1.6; color: #000;'>
            {$htmlContent}
        </div>";
    }

    /**
     * Save PDF to storage and return path
     */
    public function savePDF(string $pdfContent, string $filename, string $disk = 'local'): string
    {
        try {
            $path = 'documents/' . date('Y/m/d') . '/' . $filename;
            
            Storage::disk($disk)->put($path, $pdfContent);
            
            Log::info('PDF saved to storage', [
                'path' => $path,
                'size' => strlen($pdfContent),
                'disk' => $disk
            ]);
            
            return $path;

        } catch (Exception $e) {
            Log::error('Failed to save PDF', [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'disk' => $disk
            ]);
            throw new Exception('Erreur lors de la sauvegarde du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF with watermark
     */
    public function generatePDFWithWatermark(string $content, string $watermarkText, array $options = []): string
    {
        $options['watermark'] = [
            'text' => $watermarkText,
            'opacity' => 0.1,
            'font_size' => 48,
            'color' => '#cccccc'
        ];
        
        return $this->generatePDF($content, $options);
    }

    /**
     * Get supported PDF formats
     */
    public function getSupportedFormats(): array
    {
        return [
            'A4' => Format::A4,
            'A3' => Format::A3,
            'A5' => Format::A5,
            'Letter' => Format::Letter,
            'Legal' => Format::Legal
        ];
    }

    /**
     * Get supported orientations
     */
    public function getSupportedOrientations(): array
    {
        return [
            'Portrait' => Orientation::Portrait,
            'Landscape' => Orientation::Landscape
        ];
    }

    /**
     * Validate PDF options
     */
    public function validatePDFOptions(array $options): array
    {
        $errors = [];
        
        // Validate format
        if (isset($options['format']) && !in_array($options['format'], Format::cases())) {
            $errors[] = 'Format PDF invalide';
        }
        
        // Validate orientation
        if (isset($options['orientation']) && !in_array($options['orientation'], Orientation::cases())) {
            $errors[] = 'Orientation PDF invalide';
        }
        
        // Validate margins
        if (isset($options['margins'])) {
            $margins = $options['margins'];
            $requiredMargins = ['top', 'right', 'bottom', 'left'];
            
            foreach ($requiredMargins as $margin) {
                if (!isset($margins[$margin]) || !is_numeric($margins[$margin]) || $margins[$margin] < 0) {
                    $errors[] = "Marge {$margin} invalide";
                }
            }
        }
        
        // Validate font
        if (isset($options['font'])) {
            $font = $options['font'];
            
            if (isset($font['size']) && (!is_numeric($font['size']) || $font['size'] < 6 || $font['size'] > 72)) {
                $errors[] = 'Taille de police invalide (doit être entre 6 et 72)';
            }
        }
        
        return $errors;
    }

    /**
     * Create default PDF options for administrative documents
     */
    public function getAdministrativeDocumentOptions(): array
    {
        return [
            'format' => Format::A4,
            'orientation' => Orientation::Portrait,
            'margins' => [
                'top' => 30,
                'right' => 25,
                'bottom' => 30,
                'left' => 25,
                'unit' => Unit::Millimeter
            ],
            'font' => [
                'family' => 'DejaVu Sans',
                'size' => 12
            ],
            'header' => [
                'enabled' => false
            ],
            'footer' => [
                'enabled' => true,
                'data' => [
                    'text' => 'Document généré automatiquement',
                    'font_size' => 8
                ]
            ]
        ];
    }
}