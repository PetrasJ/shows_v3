<?php

namespace App\Command;

use App\Service\ShowsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportShowsCommand extends Command
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
        $this->setName('import-shows')
            ->setDescription('Imports shows from TV Maze')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'start';
        $this->showsManager->load();
        echo 'finish';
    }
}
