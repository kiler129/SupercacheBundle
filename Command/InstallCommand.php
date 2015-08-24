<?php

namespace noFlash\SupercacheBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs all necessary installation tasks to integrate bundle.
 */
class InstallCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        //@formatter:off
        $this
            ->setName('supercache:install')
            ->setDescription('Performs changes necessary to use bundle. This command is just a stub at this moment.')
        ;
        //@formatter:on
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<error>This command is not implemented right now - perform manual installation described in readme</error>');
    }
}
