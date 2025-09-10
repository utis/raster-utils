<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Grav\Common\Grav;

/**
 * Class HelloCommand
 *
 * @package Grav\Plugin\Console
 */
class SassCommand extends ConsoleCommand
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Greets a person with or without yelling
     */
    protected function configure()
    {
        $this
            ->setName("sass")
            // ->setDescription("Write sass code for grid or font to stdin.")
            // ->addArgument(
            //     'name',
            //     InputArgument::REQUIRED,
            //     'The name of the person that should be greeted'
            // )
            // ->addOption(
            //     'yell',
            //     'y',
            //     InputOption::VALUE_NONE,
            //     'Wheter the greetings should be yelled or quieter'
            // )
            ->setHelp('The <info>sass</info> outputs sass code.')
        ;
    }

    /**
     * @return int|null|void
     */
    protected function serve()
    {
        // Collects the arguments and options as defined
        $this->options = [
            // 'name' => $this->input->getArgument('name'),
            // 'yell' => $this->input->getOption('yell')
        ];

        // Prepare the strings we want to output and wraps the name into a cyan color
        // More colors available at:
        // https://github.com/getgrav/grav/blob/develop/system/src/Grav/Console/ConsoleTrait.php
        // $greetings = 'Greetings, dear <cyan>' . $this->options['name'] . '</cyan>!';

        // If the optional `--yell` or `-y` parameter are passed in, let's convert everything to uppercase
        // if ($this->options['yell']) {
        //     $greetings = strtoupper($greetings);
        // }

        // $grav = Grav::instance();
        // $myvar = $grav['config']['themes'];
        // $mytheme = new Basis($grav);
        // var_dump($mytheme);

        // var_dump($myvar);

        $grav = Grav::instance();
        // $all_theme_cfgs = $grav['config']['themes']; // theme_s_, plural
        // $themes_dir = $grav['locator']->findResource('themes://', false);
      
        // $var = Grav::instance()['config']->get('theme'); 

        $grav['config']->init();
        $grav['themes']->init();

        var_dump($grav['config']['theme']);

        $var = $grav['locator']->findResource('theme://', false);
        var_dump($var);

        // var_dump($themecfg);
        // var_dump($themedir);
        // print_r($typo);
        // $file = "themes://";
        // $file = Grav::instance()['locator']->findResource($file, false);
        // var_dump($file);

        $sass = "hello";


        // finally we write to the output the greetings
        $this->output->writeln($sass);
    }
}
