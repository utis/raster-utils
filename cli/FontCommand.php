<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;

define("FONTFACE_PREFIX", "_font-");
define("SASS_EXT", ".scss");
define("YAML_EXT", ".yaml");


/**
 * Class FontCommand
 *
 * @package Grav\Plugin\Console
 */
class FontCommand extends ConsoleCommand
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
            ->setName("font")
            ->setDescription("Output sass code for a font to stdin.")
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the font spec file without extension.'
            )
            // ->addOption(
            //     'yell',
            //     'y',
            //     InputOption::VALUE_NONE,
            //     'Wheter the greetings should be yelled or quieter'
            // )
            ->setHelp('The <info>font</info> outputs sass code.')
        ;
    }

    /**
     * @return int|null|void
     */
    protected function serve()
    {
        // Collects the arguments and options as defined
        $this->options = [
            'name' => $this->input->getArgument('name'),
            // 'yell' => $this->input->getOption('yell')
        ];

        $grav = Grav::instance();

        $grav['config']->init();
        $grav['themes']->init();

        $themeCfg = $grav['config']['theme'];
        $themeDir = $grav['locator']->findResource('theme://', false);
        $fontsDir = $themeDir . '/' . $themeCfg['typography']['dir'];

        $name = $this->options['name'];

        $sass = $this->generate_fontface_decl($fontsDir, $name);

        $this->output->writeln($sass);
    }


    // Generate the @fontface declaration and preface it with a
    // sass variable specifying necessary information.
    public function generate_fontface_decl ($dir, $name) {
        $ff_filename = $dir . '/' . $name . '/' . FONTFACE_PREFIX . $name . SASS_EXT;
        $spec_filename = $dir . '/' . $name . YAML_EXT;

        if (!file_exists($ff_filename))
            throw new \Exception('No such file: ' . $ff_filename);

        $sass_spec = '$font-' . $name . ': '
            . $this->fontspec_to_sass($spec_filename)
            . ";\n";
        $sass_fdir_decl = '$fonts-dir: \'' . $dir . '/' . $name . '/\';';
        
        return $sass_spec
            . "\n" . $sass_fdir_decl
            . "\n" . file_get_contents($ff_filename);
    }

    // Generate necessary font specs as a sass alist.
    public function fontspec_to_sass($spec_file) {
        if (!file_exists($spec_file))
            throw new \Exception('No such file: ' . $spec_file);     

        $spec = CompiledYamlFile::instance($spec_file)->content(null, true);
        $sass = '('
            . 'font-family: "' . $spec['font-family'] . '", '
            . 'ascender-ratio: ' . $spec['ascender-ratio'] . ', '
            . 'descender-ratio: ' . $spec['descender-ratio'] . ', '
            . 'baseline-ratio: ' . $spec['baseline-ratio']
        . ')';

        return $sass;
    }
}
