<?php

namespace App\Command;

use App\Service\ShowManager;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportShowsCommand extends Command
{
    private ShowManager $showManager;

    public function __construct(ShowManager $showManager)
    {
        parent::__construct();
        $this->showManager = $showManager;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('import-shows')
            ->setDescription('Imports shows from TV Maze')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Import Shows Started']);
        $this->showManager->load();
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Import Shows Finished']);

        return Command::SUCCESS;
    }
}
