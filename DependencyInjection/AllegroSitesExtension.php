<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AllegroSitesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admin.yml');

        $this->configToParameters($container, $config);
    }

    /**
     * tranforms the multidimensional config array into an equivalent parameter string
     * and sets it, e.g.:
     *  array(
     *      'emails' => array(
     *          'contact' => array(
     *              'format' => 'html'
     *          )
     *      )
     *  )
     * will set:
     *  'bundle_name.emails.contact.format' => 'html'
     */
    protected function configToParameters($container, $multiArray, $bundleName = 'allegro_sites') {
        $modif = false;
        foreach ($multiArray as $key => $value) {
            if (empty($value)) {
                unset($multiArray[$key]);
            }
            if (gettype($value) === 'array') {
                foreach ($value as $subKey => $subValue) {
                    if (0 === $subKey) {
                        ;
                        break;
                    }
                    $multiArray[$key . '.' . $subKey] = $subValue;
                    unset($multiArray[$key][$subKey]);
                    $modif = true;
                }
            }
        }

        if ($modif) {
            return $this->configToParameters($container, $multiArray);
        }

        foreach ($multiArray as $key => $value) {
            $container->setParameter($bundleName . '.' . $key, $value);
        }

        return true;
    }
}
