<?php
// src/OC/PlatformBundle/Form/ArticleType.php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // …
            ->add('content', 'ckeditor')
        ;
    }

    // …
}