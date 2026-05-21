<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SptPdfOverlayService
{
    public function isAvailable(): bool
    {
        return class_exists('setasign\\Fpdi\\Fpdi');
    }

    /**
     * @param  array{template_pdf_path:string, page_size?:array{0:float,1:float}, fields:array<string,array{x:float,y:float,font:float}>, signature?:array{x:float,y:float,w:float,h:float}, cap?:array{x:float,y:float,w:float,h:float}}  $layout
     * @param  array<string,string|null>  $values
     */
    public function renderFromTemplate(string $downloadName, array $layout, array $values, ?string $signaturePublicDiskPath = null, ?string $capPublicDiskPath = null)
    {
        if (!$this->isAvailable()) {
            Log::warning('FPDI not installed; returning original template PDF');

            return response()->download(public_path($layout['template_pdf_path']), $downloadName);
        }

        $templatePath = public_path($layout['template_pdf_path']);

        $fpdiClass = '\\setasign\\Fpdi\\Fpdi';
        /** @var object $pdf */
        $pdf = new $fpdiClass('P', 'pt');
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            $this->writeFields($pdf, (float) $size['height'], $layout['fields'], $values);

            if ($signaturePublicDiskPath && isset($layout['signature'])) {
                $sig = $layout['signature'];
                $sigPath = Storage::disk('public')->path($signaturePublicDiskPath);
                if (is_file($sigPath)) {
                    $pdf->Image($sigPath, $sig['x'], $this->yFromBottom((float) $size['height'], $sig['y']) - $sig['h'], $sig['w'], $sig['h']);
                }
            }

            if ($capPublicDiskPath && isset($layout['cap'])) {
                $cap = $layout['cap'];
                $capPath = Storage::disk('public')->path($capPublicDiskPath);
                if (is_file($capPath)) {
                    $pdf->Image($capPath, $cap['x'], $this->yFromBottom((float) $size['height'], $cap['y']) - $cap['h'], $cap['w'], $cap['h']);
                }
            }
        }

        $content = $pdf->Output('S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
        ]);
    }

    /**
     * @param  array<string,array{x:float,y:float,font:float}>  $fields
     * @param  array<string,string|null>  $values
     */
    private function writeFields($pdf, float $pageHeight, array $fields, array $values): void
    {
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', '', 8);

        foreach ($fields as $key => $pos) {
            $value = $values[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $font = (float) ($pos['font'] ?? 8);
            if ($font <= 0) {
                $font = 8;
            }

            $pdf->SetFont('Helvetica', '', $font);
            $pdf->Text((float) $pos['x'], $this->yFromBottom($pageHeight, (float) $pos['y']), (string) $value);
        }
    }

    private function yFromBottom(float $pageHeight, float $bottom): float
    {
        return $pageHeight - $bottom;
    }
}
