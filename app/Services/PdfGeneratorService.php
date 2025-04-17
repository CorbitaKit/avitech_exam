<?php

namespace App\Services;

use App\Traits\HtmlGenerator;
use App\Traits\PdfGenerator;
use Illuminate\Console\Command;

class PdfGeneratorService
{
    use HtmlGenerator, PdfGenerator;

    protected string $content;

    public function __construct()
    {
        $parser = new \Smalot\PdfParser\Parser();
        $this->content = $parser->parseFile(public_path('/Content.pdf'))->getText();
    }

    public function execute(Command $command): void
    {
        $startTime = microtime(true);
        $initialMemory = memory_get_usage();
        $rounds = 0;
        $tempCount = 1;
        $htmlContent = '';
        $size = 500;
        $step = 100;
        $totalRounds = 0;
        $maxSizeInBytes = 73400320;
        $emails = [fake()->email, fake()->email];

        while (true) {
            $this->content = preg_replace('/[\x00-\x1F\x7F\xAD\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2060}\x{2061}\x{2062}\x{2063}\x{2064}\x{206A}\x{206B}\x{206C}\x{206D}\x{FEFF}]/u', '', $this->content);
            $contentChunks = str_split($this->content, $size);

            if (count($contentChunks) == 1) {
                $size = 500;
                $this->content = (new \Smalot\PdfParser\Parser())->parseFile(public_path('/Content.pdf'))->getText();
                $contentChunks = str_split($this->content, $size);
            }


            $emailContents = $this->simulateEmailExchange($emails, [], $contentChunks);

            $html = $this->generateHtml($emailContents);

            $htmlContent .= $html;

            if ($rounds == 500) {
                $content = $htmlContent;
                $pdfPath = storage_path('app/simulated-thread-' . $tempCount . '.pdf');

                $pdf = $this->generatePDF($content);
                $pdf->save($pdfPath);
                $sizeInBytes = filesize($pdfPath);
                $sizeInMB = round($sizeInBytes / 1024 / 1024, 2);
                $command->info("PDF Size: {$sizeInBytes} bytes ({$sizeInMB} MB)");

                if (filesize($pdfPath) >= $maxSizeInBytes) {
                    break;
                }

                $rounds = 0;
                $tempCount++;
            }

            $size += $step;
            $rounds++;
            $totalRounds++;


            if ($rounds % 500 === 0) {
                $currentMemory = memory_get_usage();
                $peakMemory = memory_get_peak_usage();
                $cpuLoad = sys_getloadavg();
                $elapsedTime = microtime(true) - $startTime;

                $command->info("Round $totalRounds Performance:");
                $command->info(" - Memory Usage: " . round($currentMemory / 1024 / 1024, 2) . " MB");
                $command->info(" - Peak Memory Usage: " . round($peakMemory / 1024 / 1024, 2) . " MB");
                $command->info(" - CPU Load: " . implode(', ', $cpuLoad));
                $command->info(" - Elapsed Time: " . round($elapsedTime, 2) . " seconds");
                $command->info("Generating PDF.......................................................................");
            }
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);
        $finalMemory = round((memory_get_usage() - $initialMemory) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);

        $command->info("PDF Generated successfully!");
        $command->info("==== Summary ====");
        $command->info("Total Rounds: $totalRounds");
        $command->info("Total Execution Time: {$executionTime} seconds");
        $command->info("Final Memory Usage: {$finalMemory} MB");
        $command->info("Peak Memory Usage: {$peakMemory} MB");
    }



    protected function simulateEmailExchange(array $emails, array $emailContents, array $contentChunks): array
    {
        $chunkCount = count($contentChunks);

        for ($i = 0; $i < 25; $i++) {
            $body = $contentChunks[$i % $chunkCount] ?? 'No more content';

            $emailContents[] = [
                'from' => $i % 2 === 0 ? $emails[0] : $emails[1],
                'to' => $i % 2 === 0 ? $emails[1] : $emails[0],
                'subject' => 'Re: Project Discussion',
                'body' => $body,
                'timestamp' => now()->addMinutes($i * 10),
            ];
        }

        return $emailContents;
    }
}
