<?php
// src/OC/PlatformBundle/Beta/BetaListener.php

namespace OC\PlatformBundle\Beta;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BetaListener
{
    public function processBeta(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $remainingDays =(new \Datetime())->format('%d');

        // Si la date est dépassée, on ne fait rien
        if ($remainingDays <= 0) {
            return;
        }

        // On utilise notre BetaHTML
        $response = $this->betaHTML->displayBeta($event->getResponse(), $remainingDays);

        // On met à jour la réponse avec la nouvelle valeur
        $event->setResponse($response);
    }
    public  function ignoreBeta(){

    }
}