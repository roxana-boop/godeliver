<?php
/**
 * GoDeliver — SimplePdf
 *
 * A tiny, dependency-free PDF writer. It supports exactly what the
 * contract generator needs: left-aligned text lines with basic Helvetica
 * fonts (regular/bold), and one embedded JPEG image (used for the
 * courier's signature). No Composer, no external libraries — this keeps
 * the whole project deployable on plain shared PHP hosting.
 *
 * It is intentionally not a general-purpose PDF library (no word-wrap,
 * no multi-page flow beyond manual addPage() calls, no vector shapes
 * besides simple rectangles). That's fine for a one-to-two-page contract.
 */

class SimplePdf
{
    private array $objects = [];
    private int $nextId = 1;
    private array $pageIds = [];
    private array $currentOps = [];
    private int $fontRegularId;
    private int $fontBoldId;
    private float $pageWidth = 595.28;  // A4 in points
    private float $pageHeight = 841.89;
    private array $imageCache = []; // path => xobject id

    public function __construct()
    {
        $this->fontRegularId = $this->addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        $this->fontBoldId = $this->addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");
        $this->addPage();
    }

    private function addObject(string $body): int
    {
        $id = $this->nextId++;
        $this->objects[$id] = $body;
        return $id;
    }

    public function addPage(): void
    {
        if (!empty($this->currentOps)) {
            $this->flushPage();
        }
        $this->currentOps = [];
    }

    private function esc(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * The base PDF fonts (Helvetica etc.) only support WinAnsiEncoding, which
     * does not include ș/ț (comma-below) and renders ă/â/î unreliably across
     * viewers. Embedding a full Unicode TrueType font (with subsetting/CMap)
     * is a lot of complexity for a dependency-free PDF writer, so instead we
     * transliterate to the closest plain-ASCII letter. The contract stays
     * fully readable — this is standard practice for plain-text Romanian
     * documents on systems without Unicode font support.
     */
    private function stripDiacritics(string $text): string
    {
        static $map = [
            'ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ş'=>'s','ț'=>'t','ţ'=>'t',
            'Ă'=>'A','Â'=>'A','Î'=>'I','Ș'=>'S','Ş'=>'S','Ț'=>'T','Ţ'=>'T',
        ];
        $text = strtr($text, $map);

        // PDF's standard fonts expect single-byte WinAnsi/CP1252 text, not
        // UTF-8. Any other multi-byte character (curly quotes, en-dashes,
        // middle dots, etc.) must be transcoded — left as raw UTF-8 bytes,
        // each byte gets interpreted as its own (wrong) WinAnsi glyph.
        if (function_exists('mb_convert_encoding')) {
            return @mb_convert_encoding($text, 'Windows-1252', 'UTF-8') ?: $text;
        }
        if (function_exists('iconv')) {
            return @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
        }
        return preg_replace('/[^\x20-\x7E]/', '', $text); // last-resort: strip anything non-ASCII
    }

    /** Writes one line of text. Origin (0,0) is bottom-left, like standard PDF space. */
    public function text(float $x, float $y, string $text, int $size = 11, bool $bold = false, array $rgb = [0.06, 0.06, 0.07]): void
    {
        $font = $bold ? '/F2' : '/F1';
        [$r, $g, $bl] = $rgb;
        $this->currentOps[] = sprintf(
            'q %.3f %.3f %.3f rg BT %s %d Tf %.2f %.2f Td (%s) Tj ET Q',
            $r, $g, $bl, $font, $size, $x, $y, $this->esc($this->stripDiacritics($text))
        );
    }

    /** Draws a filled rectangle — handy for section dividers / boxes. */
    public function rect(float $x, float $y, float $w, float $h, array $rgb = [0.94, 0.94, 0.95]): void
    {
        [$r, $g, $bl] = $rgb;
        $this->currentOps[] = sprintf('q %.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f Q', $r, $g, $bl, $x, $y, $w, $h);
    }

    /**
     * Embeds a JPEG (raw bytes) at position (x,y) with the given box size.
     * Returns nothing; draws immediately into the current page's content.
     */
    public function image(string $jpegBytes, float $x, float $y, float $w, float $h): void
    {
        $info = @getimagesizefromstring($jpegBytes);
        $pxW = $info[0] ?? 1;
        $pxH = $info[1] ?? 1;

        $imgId = $this->addObject(sprintf(
            "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nSTREAM_PLACEHOLDER",
            $pxW, $pxH, strlen($jpegBytes)
        ));
        $this->objects[$imgId] = ['stream_header' => $this->objects[$imgId], 'stream_data' => $jpegBytes];

        $xobjName = 'Im' . $imgId;
        $this->currentOps[] = sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q', $w, $h, $x, $y, $xobjName);
        $this->pageXObjects[$xobjName] = $imgId; // collected per current page build
    }

    private array $pageXObjects = [];

    private function flushPage(): void
    {
        $stream = implode("\n", $this->currentOps);
        $contentId = $this->addObject("<< /Length " . strlen($stream) . " >>\nSTREAM_PLACEHOLDER");
        $this->objects[$contentId] = ['stream_header' => $this->objects[$contentId], 'stream_data' => $stream];

        $resources = "/Font << /F1 {$this->fontRegularId} 0 R /F2 {$this->fontBoldId} 0 R >>";
        if ($this->pageXObjects) {
            $xobjEntries = [];
            foreach ($this->pageXObjects as $name => $id) {
                $xobjEntries[] = "/$name $id 0 R";
            }
            $resources .= " /XObject << " . implode(' ', $xobjEntries) . " >>";
        }

        $pageId = $this->addObject(
            "<< /Type /Page /Parent PAGES_REF /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] " .
            "/Resources << $resources >> /Contents $contentId 0 R >>"
        );
        $this->pageIds[] = $pageId;
        $this->pageXObjects = [];
    }

    public function output(): string
    {
        $this->flushPage();

        $kids = implode(' ', array_map(fn($id) => "$id 0 R", $this->pageIds));
        $pagesId = $this->addObject("<< /Type /Pages /Kids [$kids] /Count " . count($this->pageIds) . " >>");
        $catalogId = $this->addObject("<< /Type /Catalog /Pages $pagesId 0 R >>");

        // Replace the PAGES_REF placeholder now that we know the Pages object id.
        foreach ($this->objects as $id => $body) {
            if (is_string($body) && str_contains($body, 'PAGES_REF')) {
                $this->objects[$id] = str_replace('PAGES_REF', "$pagesId 0 R", $body);
            }
        }

        $out = "%PDF-1.4\n";
        $offsets = [];
        foreach ($this->objects as $id => $body) {
            $offsets[$id] = strlen($out);
            if (is_array($body)) {
                $out .= "$id 0 obj\n" . str_replace('STREAM_PLACEHOLDER', "stream\n" . $body['stream_data'] . "\nendstream", $body['stream_header']) . "\nendobj\n";
            } else {
                $out .= "$id 0 obj\n$body\nendobj\n";
            }
        }

        $xrefStart = strlen($out);
        $count = $this->nextId;
        $out .= "xref\n0 $count\n0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $out .= "trailer\n<< /Size $count /Root $catalogId 0 R >>\nstartxref\n$xrefStart\n%%EOF";

        return $out;
    }

    public function save(string $path): bool
    {
        return file_put_contents($path, $this->output()) !== false;
    }
}
