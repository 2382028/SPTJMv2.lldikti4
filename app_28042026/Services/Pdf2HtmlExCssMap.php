<?php

namespace App\Services;

class Pdf2HtmlExCssMap
{
    /** @var array<string,float> */
    private array $x = [];

    /** @var array<string,float> */
    private array $y = [];

    /** @var array<string,float> */
    private array $fs = [];

    private float $mScale = 1.0;

    private ?float $pageWidthPx = null;

    private ?float $pageWidthPt = null;

    private ?float $pageHeightPx = null;

    private ?float $pageHeightPt = null;

    public static function fromHtmlFile(string $path): self
    {
        $self = new self();
        $html = file_get_contents($path);
        if ($html === false) {
            return $self;
        }

        $self->parse($html);

        return $self;
    }

    public function x(string $class): float
    {
        return $this->x[$class] ?? 0.0;
    }

    public function y(string $class): float
    {
        return $this->y[$class] ?? 0.0;
    }

    public function fontSizePx(string $class): float
    {
        return $this->fs[$class] ?? 0.0;
    }

    /** @return array<string,float> */
    public function allX(): array
    {
        return $this->x;
    }

    /** @return array<string,float> */
    public function allY(): array
    {
        return $this->y;
    }

    public function scale(): float
    {
        return $this->mScale;
    }

    public function ptPerPx(): float
    {
        if ($this->pageWidthPx && $this->pageWidthPt && $this->pageWidthPx > 0) {
            return $this->pageWidthPt / $this->pageWidthPx;
        }

        if ($this->pageHeightPx && $this->pageHeightPt && $this->pageHeightPx > 0) {
            return $this->pageHeightPt / $this->pageHeightPx;
        }

        // Common pdf2htmlEX ratio when CSS provides both px and pt.
        return 1.0;
    }

    public function toPt(float $px): float
    {
        return $px * $this->ptPerPx();
    }

    public function xPt(string $class): float
    {
        return $this->toPt($this->x(strtolower($class)));
    }

    public function yPt(string $class): float
    {
        return $this->toPt($this->y(strtolower($class)));
    }

    public function fontPt(string $fsClass, ?float $fallbackPt = 8): float
    {
        $px = $this->fontSizePx($fsClass);
        if ($px <= 0) {
            return (float) ($fallbackPt ?? 8);
        }

        // pdf2htmlEX uses a transform matrix (m0) to scale glyphs.
        return $px * $this->mScale;
    }

    private function parse(string $html): void
    {
        // .x20{left:123.456px;}
        if (preg_match_all('/\\.(x[0-9a-z]+)\\{left:([0-9.]+)px;\\}/i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $this->x[strtolower($row[1])] = (float) $row[2];
            }
        }

        // .y37{bottom:123.456px;}
        if (preg_match_all('/\\.(y[0-9a-z]+)\\{bottom:([0-9.]+)px;\\}/i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $this->y[strtolower($row[1])] = (float) $row[2];
            }
        }

        // .fs2{font-size:32.000000px;}
        if (preg_match_all('/\\.(fs[0-9a-z]+)\\{font-size:([0-9.]+)px;\\}/i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $this->fs[strtolower($row[1])] = (float) $row[2];
            }
        }

        // .m0{transform:matrix(0.250000,0,...)}
        if (preg_match('/\\.m0\\{transform:matrix\\(([0-9.]+)/i', $html, $m)) {
            $this->mScale = (float) $m[1];
        }

        // Capture page size in both px and pt when available.
        // Example: .w0{width:612.000000px;} and later .w0{width:816.000000pt;}
        if (preg_match('/\\.w0\\{width:([0-9.]+)px;\\}/i', $html, $m)) {
            $this->pageWidthPx = (float) $m[1];
        }
        if (preg_match('/\\.w0\\{width:([0-9.]+)pt;\\}/i', $html, $m)) {
            $this->pageWidthPt = (float) $m[1];
        }
        if (preg_match('/\\.h0\\{height:([0-9.]+)px;\\}/i', $html, $m)) {
            $this->pageHeightPx = (float) $m[1];
        }
        if (preg_match('/\\.h0\\{height:([0-9.]+)pt;\\}/i', $html, $m)) {
            $this->pageHeightPt = (float) $m[1];
        }
    }
}
