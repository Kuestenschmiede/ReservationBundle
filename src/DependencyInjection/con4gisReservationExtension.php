<?php
/**
 * This file belongs to gutes.io and is published exclusively for use
 * in gutes.io operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.io
 */
namespace con4gis\ReservationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Composer\InstalledVersions;

class con4gisReservationExtension extends Extension
{
    
    /**
     * Loads a specific configuration.
     * @param $configs
     * @param $container
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        // $version = intval(InstalledVersions::getVersion('contao/core-bundle'));
        // if ($version == 4){
        //     $loader->load('contao4_services.yml');
        // } else if ($version == 5) {
        //     $loader->load('contao5_services.yml');
        // }  

        $loader->load('services.yml');
    }
    
  /*  public function getAlias()
    {
        return "con4gis_reservation";
    }  */
}