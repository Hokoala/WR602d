<?php

namespace App\Service;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YourGotenbergService
{
    private string $gotenbergUrl;

    public function __construct(
        private HttpClientInterface $client,
        string $gotenbergUrl,
    ) {
        $this->gotenbergUrl = $gotenbergUrl;
    }

    public function generatePdfFromUrl(string $url): string
    {
        $formData = new FormDataPart([
            'url' => $url,
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromHtml(string $html): string
    {
        $formData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromMarkdown(string $markdown): string
    {
        // Gotenberg requires an index.html wrapper + the .md file
        $wrapper = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>{{ toHTML "index.md" }}</body></html>';

        $formData = new FormDataPart([
            'files' => [
                new DataPart($wrapper,  'index.html', 'text/html'),
                new DataPart($markdown, 'index.md',   'text/markdown'),
            ],
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/markdown', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromOffice(string $fileContent, string $filename): string
    {
        $formData = new FormDataPart([
            'files' => new DataPart($fileContent, $filename),
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromMerge(array $files, array $filenames): string
    {
        $dataParts = [];
        foreach ($files as $i => $fileContent) {
            $dataParts[] = new DataPart($fileContent, $filenames[$i] ?? ('file_' . $i . '.pdf'), 'application/pdf');
        }

        $formData = new FormDataPart(['files' => $dataParts]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/pdfengines/merge', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generateScreenshotFromUrl(string $url): string
    {
        // Step 1: take screenshot → PNG
        $formData = new FormDataPart(['url' => $url]);

        $screenshotResponse = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/screenshot/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        $pngContent = $screenshotResponse->getContent();

        // Step 2: wrap the PNG in an HTML page and convert it to PDF
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">
            <style>body{margin:0;padding:0;} img{width:100%;display:block;}</style>
            </head><body><img src="data:image/png;base64,' . base64_encode($pngContent) . '"/></body></html>';

        $pdfFormData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
        ]);

        $pdfResponse = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $pdfFormData->getPreparedHeaders()->toArray(),
            'body'    => $pdfFormData->bodyToIterable(),
        ]);

        return $pdfResponse->getContent();
    }

    public function splitPdf(string $fileContent, string $filename): string
    {
        $formData = new FormDataPart([
            'files'     => new DataPart($fileContent, $filename, 'application/pdf'),
            'splitMode' => 'intervals',
            'splitSpan' => '1',
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/pdfengines/split', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function compressPdf(string $fileContent, string $filename): string
    {
        $formData = new FormDataPart([
            'files' => new DataPart($fileContent, $filename, 'application/pdf'),
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function imageToPdf(string $imageContent, string $mimeType): string
    {
        $b64  = base64_encode($imageContent);
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
            . '*{margin:0;padding:0;box-sizing:border-box;}'
            . 'body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fff;}'
            . 'img{max-width:100%;max-height:100vh;object-fit:contain;display:block;}'
            . '</style></head><body>'
            . '<img src="data:' . $mimeType . ';base64,' . $b64 . '"/>'
            . '</body></html>';

        $formData = new FormDataPart([
            'files' => new DataPart($html, 'index.html', 'text/html'),
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body'    => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }
}
