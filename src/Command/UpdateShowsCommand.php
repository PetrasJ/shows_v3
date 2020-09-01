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
        $this->setName('update-shows')
            ->setDescription('Updates shows')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update shows Started']);

        [$updated, $newShows] = $this->showManager->update();
        if ($updated) {
            $output->writeln(['Updated Shows: ' . count($updated)]);
            $output->writeln(implode($updated, ', '));
        }


        if (count($newShows) > 0) {
            $output->writeln(['New Shows: ' . count($newShows)]);
            $output->writeln(implode($newShows, ', '));
        }

        $output->writeln([(new DateTime())->format('Y-m-d H:i:s') . ' Update shows Finished']);

        return Command::SUCCESS;
    }
}
