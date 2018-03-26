<?php

namespace App\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeBundle extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:bundle';
    }

    /**
     * Configure the command: set description, input arguments, options, etc.     *
     * @param \Symfony\Component\Console\Command\Command     $command
     * @param \Symfony\Bundle\MakerBundle\InputConfiguration $inputConfig
     */
    public function configureCommand(\Symfony\Component\Console\Command\Command $command, \Symfony\Bundle\MakerBundle\InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creation d\'un bundle')
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Choisir un nom de bundle (e.g. <fg=yellow>app:%s</>)', Str::asCommand(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__ . '/../Resources/help/MakeBundle.txt'))
        ;
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param \Symfony\Bundle\MakerBundle\DependencyBuilder $dependencies
     */
    public function configureDependencies(\Symfony\Bundle\MakerBundle\DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
    }

    /**
     * Called after normal code generation: allows you to do anything.
     *
     * @param InputInterface $input
     * @param ConsoleStyle   $io
     * @param Generator      $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $bundleName = trim($input->getArgument('name'));

        $this->checkBundleSuffix($bundleName, $io);
        $this->checkFirstChar($bundleName, $io);

        $currentDir = __DIR__ . '../../Bundles/' . $bundleName . '/';

        $dirs = [
            'Controller', 'DependencyInjection', 'Resources/config', 'Resources/views', 'Resources/public', 'Tests',
        ];

        foreach ($dirs as $dir) {
            mkdir($currentDir . $dir, 0777, true);
        }

        $this->writeSuccessMessage($io);
        $io->text([
            sprintf('Voila vous avez créé la structure de votre bundle : %s ! Maintenant allez l\'éditer...', $bundleName),
        ]);
    }


    public function checkBundleSuffix($bundleName, ConsoleStyle $io)
    {
        if ('Bundle' != substr($bundleName, -6)) {
            $io->error(sprintf("Le bundle nommé %s n'est pas conforme.\nIl doit finir par 'Bundle'.", $bundleName));
            die;
        }
    }

    public function checkFirstChar($bundleName, ConsoleStyle $io)
    {
        $firstChar = mb_substr($bundleName, 0, 1, "UTF-8");
        if (mb_strtolower($firstChar, "UTF-8") == $firstChar) {
            $io->error(sprintf("Le bundle nommé '%s' n'est pas conforme.\nIl doit commencer par une majuscule. Exemple : '%s'.", $bundleName, ucfirst($bundleName)));
            die;
        }
    }
}