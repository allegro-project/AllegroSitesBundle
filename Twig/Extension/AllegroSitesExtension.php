<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Twig\Extension;

use Allegro\SitesBundle\Util\RoutingHelper;
use Allegro\SitesBundle\Util\Twitter;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AllegroSitesExtension extends \Twig_Extension
{
    protected $environment;
    protected $routingHelper;

    protected $container;
    protected $templating;
    protected $session;
    protected $assetsHelper;

    public function __construct(RoutingHelper $routingHelper, ContainerInterface $container)
    {
        $this->routingHelper = $routingHelper;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->templating = $this->container->get('templating');
        $this->session = $this->container->get('session');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'allegro_sites_extension';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            'allg_page' => new \Twig_Filter_Method( $this, 'parsePage', array(
                'is_safe' => array('evaluate' => true),
            )),
            'allg_stripSlice' => new \Twig_Filter_Method($this, 'stripAndSlice'),
            'allg_preg_replace' => new \Twig_Filter_Method($this, 'pregReplace'),
            // debug filters
            'allg_class' => new \Twig_Filter_Method($this, 'getClass'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'allg_url' => new \Twig_Function_Method($this, 'generateUrl'),
            'allg_template' => new \Twig_Function_Method($this, 'getTemplate'),
            'allg_getUserTimelineTwits' => new \Twig_Function_Method($this, 'getUserTimelineTwits'),
            'allg_getHomeTimelineTwits' => new \Twig_Function_Method($this, 'getHomeTimelineTwits'),
            // debug functions
            'allg_dump' => new \Twig_Function_Method($this, 'objectDump'),
        );
    }

    /* * * * FILTERS * * * */

    public function parsePage($html) {
        // If initialization is in initRuntime it an exception may be thrown when a inactive scope
        // when running app/console commands
        if (null === $this->assetsHelper) {
            $this->assetsHelper = $this->container->get('templating.helper.assets');
        }
        $html = preg_replace_callback(
                '/(<\s*img[^>]*src\s*=\s*["\']?)([^"\']*)(["\']?[^>]*>)/i',
                function($matches) {
                    $url = $this->assetsHelper->getUrl('bundles/allegrosites/' . $matches[2]);
                    return $matches[1] . $url . $matches[3];
                },
                $html);

        return $html;
    }

    /**
     * Removes html tags from the text and slices it if its longer that the
     * specified length
     *
     * @param string $text   The text to be cleaned and sliced
     * @param int $maxLength The maximum length for the resulting string
     * @param boolean $addEllipsis = true add ellipsis? (replace last three chars)
     */
    public function stripAndSlice($text, $maxLength, $addEllipsis = true)
    {
        if (empty($text)) {
            return '';
        }

        // add a space when there are two adjacent tags
        $text = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $text);
        $text = trim(strip_tags($text));

        if (strlen($text) > $maxLength) {
            $text = $addEllipsis
                    ? substr($text, 0, $maxLength-3) . '...'
                    : substr($text, 0, $maxLength);
        }

        return $text;
    }

    public function pregReplace($string, $pattern, $replacement, $limit = -1) {
        return preg_replace($pattern, $replacement, $string, $limit);
    }

    public function getClass($object) {
        return gettype($object) == 'object' ? get_class($object) : '[Not an object / ' . gettype($object) . ']';
    }

    /* * * * FUNCTIONS * * * */

    /**
     * Generates the url for the specified entity | routing string | page id
     *
     * @param mixed $entity Page | Site | string (routing name) | int (Page.id)
     * @param array $params associative array of parameters, when $entity is a routing name
     *
     * @return string The routing URL for accesing the entity
     */
    public function generateUrl($entity, $params = null)
    {
        if (gettype($entity) === 'string') {
            $entity = "AllegroSites_$entity";
        }
        return $this->routingHelper->generateUrl($entity, $params);
    }

    /**
     * Finds the template corresponding checking first if there is a custom
     * template for it.
     * Custom templates are placed in same location as the default one and
     * with the site slug prepended, e.g. startupdigital_layout.html.twig when
     * overriding layout.html.twig
     * 
     * @param type $template
     * @return string
     */
    public function getTemplate($template)
    {
        $bundle = 'AllegroSitesBundle';
        $baseDir = 'base';
        $overrideDir = 'tpl_' . $this->session->get('site');
        $ext = '.twig';

        $templateName = (false === strpos($template, ':') ? ':' : '/') . $template;

        $template = "{$bundle}:{$overrideDir}{$templateName}{$ext}";

        if (!$this->templating->exists($template)) {
            $template = "{$bundle}:{$baseDir}{$templateName}{$ext}";
        }

        return $template;
    }

    public function getUserTimelineTwits($userName, $maxTwits = 25)
    {
        return (new Twitter())->getUserTimeline($userName, $maxTwits, array());
    }

    public function getHomeTimelineTwits($userName, $maxTwits = 25)
    {
        return (new Twitter())->getHomeTimeline($userName, $maxTwits, array());
    }

    public function objectDump($object)
    {
        $str = '';
        if (gettype($object) == 'array') {
            $str = 'array(';
            foreach ($object as $key => $value) {
                if (gettype($value) == 'array') {
                    $value = 'array()';
                }
                else if (gettype($value) == 'object') {
                    $value = get_class($value) . "(?)";
                }
                $str .= "[$key] => $value\n";
            }
        }

        else if (gettype($object) == 'object') {
            $str = 'object(';
            $class_methods = get_class_methods($object);
            foreach ($class_methods as $method_name) {
                $str .= "$method_name(?)\n";
            }
        }
        $str .= ')';

        return $str;
    }
}
