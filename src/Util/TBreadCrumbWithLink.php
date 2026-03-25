<?php
namespace Lopescte\UtilitiesForAdianti\Util;

use Adianti\Widget\Util\TBreadCrumb;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TStyle;
use Adianti\Widget\Menu\TMenuParser;
use Adianti\Core\AdiantiCoreTranslator;
use SimpleXMLElement;
use Exception;

/**
 * Class TBreadCrumbWithLink
 *
 * @category   library
 * @package    lopescte\utilities-for-adianti
 * @url        https://github.com/lopescte/utilities-for-adianti
 * @author     Marcelo Lopes <lopes.cte@gmail.com>
 * @copyright  Copyright (c) 2026 Reis & Lopes Assessoria e Sistemas. (https://www.reiselopes.com.br)
 * @license    http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license    https://opensource.org/licenses/MIT MIT
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3+
 */
class TBreadCrumbWithLink extends TBreadCrumb
{
    protected static $homeController;
    protected $container;
    protected $items;
    
    private $parser;
    
    /**
     * Handle paths from a XML file
     * @param $xml_file path for the file
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->{'id'} = 'div_breadcrumbs';
        
        $this->container = new TElement('ol');
        $this->container->{'class'} = 'tbreadcrumbwithlink';
        parent::add( $this->container );
        
        TStyle::importFromFile(__DIR__ .'/../../css/style.css');              
    }
    
    /**
     * Static constructor
     */
    public static function create( $options, $home = true)
    {
        $breadcrumb = new TBreadCrumbWithLink;
        if ($home)
        {
            $breadcrumb->addHome();
        }
        foreach ($options as $option)
        {
            $breadcrumb->addItem( $option );
        }
        return $breadcrumb;
    }
    
    /**
     * Add the home icon
     */
    public function addHome()
    {
        $li = new TElement('li');
        $li->{'class'} = 'home';
        $a = new TElement('a');
        $a->generator = 'adianti';
        
        if (self::$homeController)
        {
            $a->{'href'} = 'engine.php?class='.self::$homeController;
        }
        else
        {
            $a->{'href'} = 'engine.php';
        }
        
        $a->{'title'} = 'Home';
        
        $li->add( $a );
        $this->container->add( $li );
    }
    
    /**
     * Add an item
     * @param $path Path to be shown
     * @param $last If the item is the last one
     */
    public function addItem($path, $link=NULL, $last = FALSE)
    {
        $li = new TElement('li');
        $this->container->add( $li );
        
        $span = new TElement('span');
        $span->add( $path );
        
        $this->items[$path] = $span;
        if( $last )
        {
            $li->add( $span );
            $this->select($path);
        }
        else
        {
            $a = new TElement('a');
            $a->generator = 'adianti';
            
            if( $link )
            {
                $a->{'href'} = 'engine.php?class='.$link;
            }
            
            $li->add( $a );
            $a->add( $span );
        }
            
    }
    
    /**
     * Define the home controller
     * @param $class Home controller class
     */
    public static function setHomeController($className)
    {
        self::$homeController = $className;
    }
    
    /**
     * Render from a XML File
     * @param $xml_file XML file menu
     * @param $controller controller class
     */
    public function renderFromXML($xml_file, $controller, $noNext=TRUE)
    {
        if($xml_file)
        {
            $this->parser = new TMenuParser($xml_file);
            $paths = $this->parser->getPath($controller);
                                                               
            if (!empty($paths))
            {               
                $count = 1;
                foreach ($paths as $path)
                {                    
                    if (!empty($path))
                    {
                        if($noNext)
                        {
                            $module = NULL;
                            $end = $count == count($paths); 
                        }else{ 
                            $module = ($count == count($paths)) ? $controller : NULL;
                            $end = $noNext; 
                        }
                        
                        $this->addItem($path, $module, $end);
                        $count++;
                    }
                }
            }
        }
    }  
}
