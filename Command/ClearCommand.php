<?php

namespace noFlash\SupercacheBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCommand
 */
class ClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        //@formatter:off
        $this
            ->setName('supercache:clear')
            ->setDescription('Clears all cache entries')
            //->addOption(
            //    'unsafe',
            //    null,
            //    InputOption::VALUE_NONE,
            //    'Perform a possibly unsafe clear - it removes all files and folders from cache directory without verifying whatever these files were created by that bundle.'
            //)
        ;
        //@formatter:on
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('supercache.cache_manager');

        $output->writeln('Clearing cache directory');
        $output->writeln(($manager->clear()) ? '<info>Success</info>' : '<error>Failed - try manually clearing cache directory</error>');
    }
}
