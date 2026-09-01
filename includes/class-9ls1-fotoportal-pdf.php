<?php
if (!defined('ABSPATH')) exit;

class NLS1_Fotoportal_PDF {
    private $pages = [];
    private $width = 595.28;
    private $height = 841.89;
    private $current = -1;

    public function add_page() {
        $this->pages[] = ['ops' => [], 'images' => []];
        $this->current = count($this->pages) - 1;
    }

    public function text($x, $y, $text, $size = 10) {
        $this->text_rgb($x, $y, $text, $size, 0, 0, 0);
    }

    public function text_rgb($x, $y, $text, $size = 10, $r = 0, $g = 0, $b = 0) {
        if ($this->current < 0) $this->add_page();
        $safe = $this->escape_text($text);
        $pdf_y = $this->height - $y;
        $this->pages[$this->current]['ops'][] = sprintf('%.3f %.3f %.3f rg BT /F1 %d Tf %.2f %.2f Td (%s) Tj ET 0 0 0 rg', $r/255, $g/255, $b/255, $size, $x, $pdf_y, $safe);
    }

    public function line($x1, $y1, $x2, $y2, $r = 0, $g = 0, $b = 0) {
        if ($this->current < 0) $this->add_page();
        $py1 = $this->height - $y1;
        $py2 = $this->height - $y2;
        $this->pages[$this->current]['ops'][] = sprintf('%.3f %.3f %.3f RG %.2f %.2f m %.2f %.2f l S 0 0 0 RG', $r/255, $g/255, $b/255, $x1, $py1, $x2, $py2);
    }

    public function rect_fill($x, $y, $w, $h, $gray = 0.95) {
        if ($this->current < 0) $this->add_page();
        $py = $this->height - $y - $h;
        $this->pages[$this->current]['ops'][] = "{$gray} g {$x} {$py} {$w} {$h} re f 0 g";
    }

    public function rect_rgb($x, $y, $w, $h, $r, $g, $b) {
        if ($this->current < 0) $this->add_page();
        $py = $this->height - $y - $h;
        $this->pages[$this->current]['ops'][] = sprintf('%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f 0 0 0 rg', $r/255, $g/255, $b/255, $x, $py, $w, $h);
    }

    public function image($path, $x, $y, $w, $h) {
        if ($this->current < 0) $this->add_page();
        if (!file_exists($path)) return false;
        $info = @getimagesize($path);
        if (!$info || !in_array($info['mime'], ['image/jpeg','image/jpg'], true)) return false;
        $name = 'Im' . (count($this->pages[$this->current]['images']) + 1);
        $this->pages[$this->current]['images'][$name] = [
            'path' => $path,
            'width' => (int)$info[0],
            'height' => (int)$info[1],
            'colorspace' => (($info['channels'] ?? 3) == 1) ? '/DeviceGray' : '/DeviceRGB',
        ];
        $py = $this->height - $y - $h;
        $this->pages[$this->current]['ops'][] = "q {$w} 0 0 {$h} {$x} {$py} cm /{$name} Do Q";
        return true;
    }

    public function output($path) {
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $next_id = 4;
        $page_ids = [];
        foreach ($this->pages as $page) {
            $xobjects = [];
            foreach ($page['images'] as $name => $img) {
                $img_id = $next_id++;
                $data = file_get_contents($img['path']);
                $objects[$img_id] = "<< /Type /XObject /Subtype /Image /Width {$img['width']} /Height {$img['height']} /ColorSpace {$img['colorspace']} /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($data) . " >>\nstream\n" . $data . "\nendstream";
                $xobjects[] = "/{$name} {$img_id} 0 R";
            }
            $content = implode("\n", $page['ops']) . "\n";
            $content_id = $next_id++;
            $objects[$content_id] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}endstream";
            $page_id = $next_id++;
            $page_ids[] = $page_id;
            $xobject_dict = $xobjects ? "/XObject << " . implode(' ', $xobjects) . " >>" : "";
            $objects[$page_id] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->width} {$this->height}] /Resources << /Font << /F1 3 0 R >> {$xobject_dict} >> /Contents {$content_id} 0 R >>";
        }
        $kids = implode(' ', array_map(fn($id) => "{$id} 0 R", $page_ids));
        $objects[2] = "<< /Type /Pages /Kids [{$kids}] /Count " . count($page_ids) . " >>";
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $obj) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $count = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        wp_mkdir_p(dirname($path));
        return file_put_contents($path, $pdf) !== false;
    }

    private function escape_text($text) {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$text);
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        return $text ?: '';
    }
}
