<?php

namespace App\Command;

use App\Service\SitemapCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapCrawlerCommand extends Command
{
    private SitemapCrawler $sitemapCrawler;

    public function __construct(SitemapCrawler $sitemapCrawler)
    {
        parent::__construct('app:fetch-sitemap');
        $this->sitemapCrawler = $sitemapCrawler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Fetches a sitemap, checks all links, and outputs their status codes')
            ->addArgument('sitemapUrl', InputArgument::REQUIRED, 'The URL of the sitemap to fetch')
            ->addArgument('outputFile', InputArgument::OPTIONAL, 'The path to the output CSV file', 'sitemap_results.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sitemapUrl = $input->getArgument('sitemapUrl');
        $outputFile = $input->getArgument('outputFile');

        try {
            $output->writeln("<info>Fetching sitemap from:</info> $sitemapUrl");
            $urls = $this->sitemapCrawler->fetchSitemap($sitemapUrl);
            $output->writeln("\n<info>Checking URLs (".count($urls)."):</info>");
            $csvFile = fopen($outputFile, 'w');
            fputcsv($csvFile, ['URL', 'Status Code', 'Checked At']); // Kopfzeile schreiben

            foreach ($urls as $key => $url) {
                $status = $this->sitemapCrawler->checkUrlStatus($url);
                $dateTime = (new \DateTime())->format('Y-m-d H:i:s');
                $output->writeln($key." - <href>$url</href> [Status: $status]");
                fputcsv($csvFile, [
                    $url,
                    $status,
                    $dateTime
                ]);
            }

            fclose($csvFile);
            $output->writeln("\n<info>Results written to:</info> $outputFile");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error:</error> " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
