<?php

/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\ContaoManager;

use con4gis\GroupsBundle\con4gisGroupsBundle;
use con4gis\ProjectsBundle\con4gisProjectsBundle;
use con4gis\ReservationBundle\con4gisReservationBundle;
use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Contao\System;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements RoutingPluginInterface, BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routing.yml')
            ->load(__DIR__.'/../Resources/config/routing.yml');
    }


    /**
     * Gets a list of autoload configurations for this bundle.
     *
     * @param ParserInterface $parser
     *
     * @return ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        $mapsExists = class_exists('con4gis\MapsBundle\con4gisMapsBundle');
        $exportExists = class_exists('con4gis\ExportBundle\con4gisExportBundle');

        $loadAfterArr = [ContaoCalendarBundle::class];
        if ($mapsExists) {
            $loadAfterArr[] = \con4gis\MapsBundle\con4gisMapsBundle::class;
        }
        if ($exportExists) {
            $loadAfterArr[] = \con4gis\ExportBundle\con4gisExportBundle::class;
        }

        $loadAfterArr[] = con4gisProjectsBundle::class;
        $loadAfterArr[] = con4gisGroupsBundle::class;

        return [
            BundleConfig::create(con4gisReservationBundle::class)
                ->setLoadAfter($loadAfterArr)
        ];
    }
}