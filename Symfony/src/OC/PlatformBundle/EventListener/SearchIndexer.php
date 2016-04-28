<?php
// src/OC/PlatformBundle/EventListener/SearchIndexer.php

namespace OC\PlatformBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OC\PlatformBundle\Entity\Product;

class SearchIndexer
{
    public function postPersist(LifecycleEventArgs $args)
    {
    $entity = $args->getEntity();

    // only act on some "Product" entity
    if (!$entity instanceof Product) {
    return;
    }

    $entityManager = $args->getEntityManager();
    }
}