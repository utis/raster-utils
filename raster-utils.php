<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\File\CompiledYamlFile;
// use Grav\Common\Yaml;

define("FACESDIR", "theme://fonts/");
define("FACEPREFIX", "font-");
define("YEXTENSION", "yaml");

/**
 * Class RasterUtilsPlugin
 * @package Grav\Plugin
 */
class RasterUtilsPlugin extends Plugin
{
    public $gridCfg = false;
    public $face = false;
    
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                // Uncomment following line when plugin requires Grav < 1.7
                // ['autoload', 100000],
                ['onPluginsInitialized', 0],
            ]
        ];
    }


    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload()
    {
        return require __DIR__ . '/vendor/autoload.php';
    }


    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            // Put your main events here
            'onThemeInitialized' => ['onThemeInitialized', 0],
            'onTwigInitialized' => ['onTwigInitialized', 0],
            'onTwigSiteVariables'  => ['onTwigSiteVariables', 0]
        ]);
    }

    public function onThemeInitialized (): void {
        // Initialize current grid and face.
        // dump($this->grav['config']['theme']);
        $allCfg = $this->grav['config']['theme']['typography'] ?? false;
        // dump($allCfg);
        if ($allCfg) {
            $this->gridCfg = $this->activeGrid($allCfg['grids']);
            
            $this->face = $this->loadFaceCfg($this->gridCfg['face']);
        }
    }

    protected function loadFaceCfg($name) {
        $file = FACESDIR . FACEPREFIX . $name . "." . YEXTENSION; 
        $file = Grav::instance()['locator']->findResource($file, true, true);
        $face = CompiledYamlFile::instance($file)->content(null, true);
        // dump($file);
        return reset($face);
    }

    protected function activeGrid ($cfgs) {
        // Find a grid spec named 'default'. Otherwise use the first
        // one.
        $r = false;
        foreach ($cfgs as $name => $spec) {
            if ($name === 'default') {
                $r = $spec;
                $spec['name'] = $name;
                break;
            }
        }
        if (!$r) {
            $r = $cfgs[0];
            $r['name'] = array_key_first($cfgs);
        }
        return $r;
    }

    private function getFaceCfg ($name) {
    }

    private function faceNameCheckSanity ($name, $cfg) {
    }


    // private function img_default_dimensions($img_spc) {
    //     $grid_config = $this->grav['config']['theme']['grid'];
    //     $gwidth = $grid_config['width'];
    //     $gheight = $grid_config['height'];
    //     $gutter_width = $grid_config['gutter_width'];
    //     $gutter_height = $grid_config['gutter_height'];
    //     $correction = $grid_config['correction'];

    //     $break = array_keys($img_spc)[0];
    //     $spc = array_values($img_spc)[0];
    //     $columns = $spc['columns'];
    //     $rows = $spc['rows'];

    //     $rsize = $grid_config['breakpoints'][$break]['rootsize'];

    //     $w_rem = $gwidth * $columns + ($columns - 1) * $gutter_width;
    //     $h_rem = ($gheight * $rows + ($rows - 1) * $gutter_height) - $correction;
    //     $return = array();

    //     $return['width'] = intval(ceil($w_rem * $rsize));
    //     $return['height'] = intval(ceil($h_rem * $rsize));
    //     return $return;
    // }

    // private function img_sizes_str($img_spc) {
    //     $grid_config = $this->grav['config']['theme']['grid'];
    //     $gwidth = $grid_config['width'];
    //     $gheight = $grid_config['height'];
    //     $gutter_width = $grid_config['gutter_width'];
    //     $gutter_height = $grid_config['gutter_height'];
    //     $breakpoints = $grid_config['breakpoints'];
    //     $str = "";
    //     $default = null;

    //     foreach ($img_spc as $break => $spec) {
    //         $rsize = $breakpoints[$break]['rootsize'];
    //         $min_width = $breakpoints[$break]['min-width'];
    //         $columns = $spec['columns'];

    //         $rem = $gwidth * $columns + ($columns - 1) * $gutter_width;
    //         $px = ceil($rsize * $rem);
    //         $str = $str."(min-width: {$min_width}px) {$px}px,";
    //         if (!$default) {
    //             $default = "{$px}px";
    //         }
    //         return $str." $default";
    //     }
    // }
    // public function onTwigInitialized() {
    //     if ($this->isAdmin()) {
    //         return;
    //     }


    public function onTwigInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $twig = $this->grav['twig']->twig();

        $twig->addFunction(
            new \Twig_SimpleFunction('grid_width', [$this, 'gridWidth'])
        );

        $twig->addFunction(
            new \Twig_SimpleFunction('page_width', [$this, 'pageWidth'])
        );

        $twig->addFunction(
            new \Twig_SimpleFunction('grid_height', [$this, 'gridHeight'])
        );
    }


    public function gridWidth ($n, $px = false) {
        $r = $this->gridWidthInternal($this->gridCfg, $n);

        if ($px) {
            $r = $r * $this->gridCfg['root-size'];
        }

        return $r;
    }

    public function pageWidth ($n, $px = false) {
        $w = $this->gridWidthInternal($this->gridCfg, $n);
        $w = $w + 2 * $this->gridCfg['min-h-margins'];

        if ($px) {
            $w = $w * $this->gridCfg['root-size'];
        }

        return $w;
    }

    protected function gridWidthInternal($cfg, $n) {
        return $cfg['column-width'] * $n + $cfg['gutter-width'] * ($n - 1);
    }

    protected function typographyCorrection($fsize, $lheight, $ratio) {
        // Return the amount of typographical correction in unitless rem.
        // Ratio is the ratio of the amount of whitespace to be
        // corrected in relation to 'font-size'.
        return ($fsize * $ratio + $lheight - $fsize) / 2;
    }
        
    public function gridHeight ($n, $px = false) {
        $cfg = $this->gridCfg;
        // dump($cfg);

        $totalHeight = $cfg['row-height'] + $cfg['gutter-height'];
        // dump($totalHeight);
        $asc = $this->face['ascender-ratio'];
        $desc = $this->face['descender-ratio'];
        
        $adjHeight = $cfg['row-height'];


        $tmp = $this->typographyCorrection($cfg['font-size'],
                                           $cfg['line-height'],
                                           $asc);
        if ($asc) {
            $adjHeight = $adjHeight - $this->typographyCorrection($cfg['font-size'],
                                                                  $cfg['line-height'],
                                                                  $asc);
        }

        if ($desc) {
            $adjHeight = $adjHeight - $this->typographyCorrection($cfg['font-size'],
                                                                  $cfg['line-height'],
                                                                  $desc);
        }

        $height = $totalHeight * ($n - 1) + $adjHeight;

        if ($px) {
            $height = $height * $cfg['root-size'];
        }
        // $height = 100;
        return $height;
    }

        // $function = new \Twig_SimpleFunction
        //           ('auto_img',
        //            function (\Grav\Common\Page\Medium\ImageMedium $img, $spc,
        //                      $classes = false, $cfg = false) {
        //     $sizes = $this->img_sizes_str($spc);
        //     // $my_var = $img->lightbox()->cropZoom(200,200)->sizes($sizes)->html();
            
        //     $d = $this->img_default_dimensions($spc);

        //     if ($cfg['lightbox']) {
        //         $img = $img->lightbox();
        //     }

        //     if ($cfg['grayscale']) {
        //         $img = $img->grayscale();
        //     }

        //     if ($cfg['contrast']) {
        //         $img = $img->contrast($cfg['contrast']);
        //     }
                
        //     $img = $img->cropZoom($d['width'], $d['height']);

        //     $html = $img->sizes($sizes)->html(false, false, $classes);
        //     // $html = $img->html(false, false, $classes);
        //     return $html;
        // });
        // $this->grav['twig']->twig->addFunction($function);


    public function onTwigSiteVariables() {
        $this->grav['twig']->twig_vars['grid_cfg'] = $this->gridCfg;
    }
}
