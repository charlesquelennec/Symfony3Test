<?php
// src/OC/PlatformBundle/Antispam/OCAntispam.php

namespace OC\PlatformBundle\Antispam;

class OCAntispam extends \Twig_Extension
{
    protected $mailer;
    protected $locale;
    protected $nbForSpam;

    /**
     *
     * @param string $text
     * @return bool
     */
    // Dans le constructeur, on retire $locale des arguments

    public function __construct(\Swift_Mailer $mailer, $nbForSpam)
    {
        $this->mailer = $mailer;
        $this->nbForSpam = (int) $nbForSpam;
    }

    // Et on ajoute un setter
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    public function isSpam($text)
    {
        return strlen($text) < 50;
    }

    // Twig va exécuter cette méthode pour savoir quelle(s) fonction(s) ajoute notre service
    public function getFunctions()
    {
        return array(
            'checkIfSpam' => new \Twig_Function_Method($this, 'isSpam')
        );
    }

    // La méthode getName() identifie votre extension Twig, elle est obligatoire
    public function getName()
    {
        return 'OCAntispam';
    }
}