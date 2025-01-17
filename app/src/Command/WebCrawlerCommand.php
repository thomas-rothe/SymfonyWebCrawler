<?php

namespace App\Command;

use App\Service\WebSiteCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebCrawlerCommand extends Command
{
    private WebSiteCrawler $webCrawler;

    public function __construct(WebSiteCrawler $webCrawler)
    {
        parent::__construct('app:fetch-website');
        $this->webCrawler = $webCrawler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Fetches the content of a website')
            ->addArgument('url', InputArgument::REQUIRED, 'The URL of the website to fetch')
            ->addArgument('outputFile', InputArgument::OPTIONAL, 'The path to the output CSV file', 'webcrawl_results.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        $outputFile = $input->getArgument('outputFile');

        try {
            $links = $this->webCrawler->catchUrl($url);
            $output->writeln("<info>Website urls count: ".count($links)."</info>\n");
            $csvFile = fopen($outputFile, 'w');
            fputcsv($csvFile, ['URL', 'Title', 'Type', 'Status Code', 'Checked At']);

            foreach ($links as $key => $link) {
                $dateTime = (new \DateTime())->format('Y-m-d H:i:s');
                $outputLine = sprintf(
                    '%d - %s - %d',
                    $key,
                    $link['href'],
                    $link['status']
                );
                $output->writeln($outputLine);
                fputcsv($csvFile, [
                    $link['href'],
                    $link['text'],
                    $link['type'],
                    $link['status'],
                    $dateTime
                ]);
            }

            #fclose($csvFile);
            $output->writeln("\n<info>Results written to:</info> $outputFile");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}
