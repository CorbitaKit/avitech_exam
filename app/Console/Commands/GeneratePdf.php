<?php

namespace App\Console\Commands;

use App\Services\PdfGeneratorService;
use Illuminate\Console\Command;

class GeneratePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run simulation of generating PDF';

    public function __construct(protected PdfGeneratorService $pdfGeneratorService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->pdfGeneratorService->execute($this);
    }
}
