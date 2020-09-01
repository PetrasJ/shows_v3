<?php

namespace App\Command;

use App\Service\ShowManager;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAllShowsCommand extends Command
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
        $this->setName('update-all-shows')
            ->setDescription('Updates all shows from TV Maze')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update all shows Started']);
        $this->showManager->load(true);
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update all shows Finished']);

        return Command::SUCCESS;
    }
}
