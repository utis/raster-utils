<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;

// define("FONTFACE_PREFIX", "_font-");
// define("SASS_EXT", ".scss");
// define("YAML_EXT", ".yaml");
define("RPLUGIN_NAME", "raster-utils");

/**
 * Class FontCommand
 *
 * @package Grav\Plugin\Console
 */
class gridCommand extends ConsoleCommand
{
    /**
     * @var array
     */
    protected $options = [];

    protected function configure()
    {
        $this
            ->setName("grid")
            ->setDescription("Output sass code for a font to stdin.")
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The name of the font spec file without extension.'
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'Wheter the greetings should be yelled or quieter'
            )
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
            'list' => $this->input->getOption('list')
        ];

        $grav = Grav::instance();

        $grav['config']->init();
        $grav['themes']->init();

        // var_dump("HeLLO");
        // var_dump($grav['config']['plugins']);

        $typo_cfg = $grav['config']['theme']['typography'];
        // $themeDir = $grav['locator']->findResource('theme://', false);
        // $fontsDir = $themeDir . '/' . $themeCfg['typography']['dir'];
        // $sass = $themeDir . '/' . $fontsDir;

        if ($this->options['list']) {
            $r = array();
            $i = 0;
            foreach ($typo_cfg['grids'] as $key => $v){
                $r[$i++] = $key;
            }
            $out = implode(' ', $r);
        } else {
            $name = $this->options['name'];
            $spec = $typo_cfg['grids'][$name];

            
            $plugin_tpls =
                $grav["config"]->get('plugins.' . RPLUGIN_NAME)['templates'];
            $theme_tpls = $typo_cfg['templates'];

            $spec = $this->resolve_spec($spec, $theme_tpls, $plugin_tpls);
            
            $out = "@import 'font-${spec['face']}';\n";
            
            $out = $out . "\$grid-${name}: " . $this->generate_grid_decl($spec) . ';';
        }

        $this->output->writeln($out);
    }


    protected $spc_keywords = ['breakpoints', 'root-size', 'font-size',
                               'column-width', 'row-height'];

    protected function resolve_spec($spc, $theme_tpls, $plugin_tpls) {
        $tpl_name = $spc['template'];
        if ($tpl_name) {
            $tpl = $theme_tpls[$tpl_name] ?? $plugin_tpls[$tpl_name];

            if(!$tpl)
                throw new \Exception('No such template: ' . $tpl_name);

            foreach ($this->spc_keywords as $k) {
                $spc[$k] = $spc[$k] ?? $tpl[$k];
            }
        }
        return $spc;
    }

    protected function generate_grid_decl($spec) {
        $face = $spec['face'];
        $out = '('
            . "face: \$font-${spec['face']},"
            . "breakpoints: ${spec['breakpoints']},"
            . "root-size: ${spec['root-size']},"
            . "font-size: ${spec['font-size']},"
            . "column-width: ${spec['column-width']},"
            . "row-height: ${spec['row-height']},"
            . "line-height: 1," // FIXME?
            . "gutter-width: 1," // FIXME!
            . "gutter-height: 1," // FIXME!
            . "min-h-margins: 1" // FIXME
            . ')';
        return $out;
    }

    // // Generate the @fontface declaration and preface it with a
    // // sass variable specifying necessary information.
    // public function generate_fontface_decl ($dir, $name) {
    //     $ff_filename = $dir . '/' . $name . '/' . FONTFACE_PREFIX . $name . SASS_EXT;
    //     $spec_filename = $dir . '/' . $name . YAML_EXT;

    //     if (!file_exists($ff_filename))
    //         throw new \Exception('No such file: ' . $ff_filename);

    //     $sass_spec = '$font-' . $name . ': '
    //         . $this->fontspec_to_sass($spec_filename)
    //         . ";\n";
    //     $sass_fdir_decl = '$fonts-dir: \'' . $dir . '/' . $name . '/\';';
        
    //     return $sass_spec
    //         . "\n" . $sass_fdir_decl
    //         . "\n" . file_get_contents($ff_filename);
    // }

    // // Generate necessary font specs as a sass alist.
    // public function fontspec_to_sass($spec_file) {
    //     if (!file_exists($spec_file))
    //         throw new \Exception('No such file: ' . $spec_file);     

    //     $spec = CompiledYamlFile::instance($spec_file)->content(null, true);
    //     $sass = '('
    //         . 'font-family: "' . $spec['font-family'] . '", '
    //         . 'ascender-ratio: ' . $spec['ascender-ratio'] . ', '
    //         . 'descender-ratio: ' . $spec['descender-ratio'] . ', '
    //         . 'baseline-ratio: ' . $spec['baseline-ratio']
    //     . ')';

    //     return $sass;
    // }
}
