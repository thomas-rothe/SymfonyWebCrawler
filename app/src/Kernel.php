<?php

namespace App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends BaseKernel
{
    public function registerBundles(): iterable
    {
        // Hier kannst du Bundles registrieren
        $bundles = [
            // z.B. Symfony Framework Bundle
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        ];

        foreach ($bundles as $bundle) {
            yield $bundle;
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // Lade Konfigurationsdateien
        $loader->load($this->getProjectDir() . '/config/services.yaml');
    }
}
