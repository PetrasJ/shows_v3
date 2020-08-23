<?php

namespace App\Command;

use App\Service\ShowManager;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateShowsCommand extends Command
{
    private $showManager;

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
        $this->setName('update-shows')
            ->setDescription('Updates shows')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update shows Started']);

        $result = $this->showManager->update();
        if (count($result['updated']) > 0) {
            $output->writeln(['Updated Shows: ' . count($result['updated'])]);
            $output->writeln(implode($result['updated'], ', '));
        }


        if (count($result['newShows']) > 0) {
            $output->writeln(['New Shows: ' . count($result['newShows'])]);
            $output->writeln(implode($result['newShows'], ', '));
        }

        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update shows Finished']);

        return 0;
    }
}
