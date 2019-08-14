<?php

namespace App\Command;

use App\Service\ShowsManager;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAllShowsCommand extends Command
{
    private $showsManager;

    public function __construct(ShowsManager $showsManager)
    {
        parent::__construct();
        $this->showsManager = $showsManager;
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update all shows Started']);
        $this->showsManager->load(true);
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update all shows Finished']);
    }
}
