<?php

namespace App\Command;

use App\Service\ShowsManager;
use DateTime;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateShowsInDevelopmentCommand extends Command
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
        $this->setName('update-shows-in-development')
            ->setDescription('Updates shows in development')
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
        $output->writeln([(new DateTime())->format('Y-m-d h:i:s') . ' Started']);

        $result = $this->showsManager->updateInDevelopment();
        $output->writeln('Updated Shows: ' . $result);

        $output->writeln([(new DateTime())->format('Y-m-d h:i:s') . ' Finished']);
    }
}
