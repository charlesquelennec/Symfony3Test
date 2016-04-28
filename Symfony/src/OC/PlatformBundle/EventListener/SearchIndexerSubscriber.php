<?php
// src/OC/PlatformBundle/EventListener/SearchIndexerSubscriber.php
namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
// for Doctrine 2.4: Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use OC\PlatformBundle\Entity\Product;

class SearchIndexerSubscriber implements EventSubscriber
{
public function getSubscribedEvents()
{
return array(
'postPersist',
'postUpdate',
);
}

public function postUpdate(LifecycleEventArgs $args)
{
$this->index($args);
}

public function postPersist(LifecycleEventArgs $args)
{
$this->index($args);
}

public function index(LifecycleEventArgs $args)
{
$entity = $args->getEntity();

// perhaps you only want to act on some "Product" entity
if ($entity instanceof Product) {
$entityManager = $args->getEntityManager();

}
}
}