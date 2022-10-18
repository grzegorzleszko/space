<?php

namespace App\Command;

use App\Service\SpaceTweets;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'space:tweets',
    description: 'Get NASA, SpaceX, BoeingSpace tweets from Tweeter API',
)]
class SpaceTweetsCommand extends Command
{
    /** @var SpaceTweets */
    private SpaceTweets $spaceTweets;

    public function __construct(SpaceTweets $spaceTweets)
    {
        $this->spaceTweets = $spaceTweets;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'users',
                InputArgument::OPTIONAL,
                'Tweeter users separated by a comma',
                "NASA,SpeceX,BoeingSpace"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $input->getArgument('users');

        if ($users) {
            $io->note(sprintf('You passed an argument: %s', $users));
        }

        $this->spaceTweets->updateTweets($users);

        $io->success("OK");

        return Command::SUCCESS;
    }
}
