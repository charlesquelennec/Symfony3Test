<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Form\AdvertType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;



class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 3;

        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts)/$nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('OCPlatformBundle:Advert:templatepage.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages'     => $nbPages,
            'page'        => $page
        ));
    }

    public function viewAction()
    {
        if( !isset($id)){
            $id=1;
        }
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
        ;

        // On récupère l'entité correspondante à l'id $id
        $advert = $repository->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert
        ));
    }

    public function add1Action(Request $request)
    {
        // Création de l'entité Image

        $image = new Image();

        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');

        $image->setAlt('Job de rêve');

        // Création de l'entité Advert

        $advert = new Advert();

        $advert->setTitle('Recherche développeur Symfony2.');

        $advert->setAuthor('Alexandre');

        $advert->setContent("Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…");

        // On lie l'image à l'annonce

        $advert->setImage($image);

        // Création d'une première candidature

        $application1 = new Application();

        $application1->setAuthor('Marine');

        $application1->setContent("J'ai toutes les qualités requises.");


        // Création d'une deuxième candidature par exemple

        $application2 = new Application();

        $application2->setAuthor('Pierre');

        $application2->setContent("Je suis très motivé.");


        // On lie les candidatures à l'annonce

        $application1->setAdvert($advert);

        $application2->setAdvert($advert);


        // On récupère l'EntityManager

        $em = $this->getDoctrine()->getManager();


        // Étape 1 : On « persiste » l'entité

        $em->persist($advert);


        // Étape 1 bis : pour cette relation pas de cascade lorsqu'on persiste Advert, car la relation est

        // définie dans l'entité Application et non Advert. On doit donc tout persister à la main ici.

        $em->persist($application1);

        $em->persist($application2);


        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        // Here, "getMyFile" returns the "UploadedFile" instance that the form bound in your $myFile property
        $uploadableManager->markEntityToUpload($document, $document->getMyFile());
        $em->flush();

        // Reste de la méthode qu'on avait déjà écrit
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig',array('advert' => $advert));

    }
// Methode 1 : pour limiter l'accès au formulaire que pour un certain rôle
//    /**
//     * @Security("has_role('ROLE_AUTEUR')")
//     */
    public function addAction(Request $request)
    {
        //Selectionne dans la base de donnée l'élément souhaité
        $conn = $this->get('database_connection');
        $adverts = $conn->fetchAll('SELECT * FROM advert');

// Methode 2 : pour limiter l'accès au formulaire que pour un certain rôle
// On vérifie que l'utilisateur dispose bien du rôle ROLE_AUTEUR
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR')) {
//
//            // Sinon on déclenche une exception « Accès interdit »
//
//            throw new AccessDeniedException('Accès limité aux auteurs.');
//
//        }
        // On crée un objet Advert
        //$advert = new Advert();

        // Récupération d'une annonce en base déjà existante, d'id $id.
//        $advert1 = $this->getDoctrine()
//            ->getManager()
//            ->getRepository('OCPlatformBundle:Advert')
//            ->find(1)
//        ;
//        $formBuilder = $this->createFormBuilder($advert1);
        $advert = new Advert();

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $form = $this->createForm(AdvertType::class, $advert);

        //Definir que le champ n'est pas obligatoire
        $form->add('published', CheckboxType::class, array('required' => false));

        //Gère la requete envoyée par le formulaire
        $form->handleRequest($request);

        // On récupère le service validator
        $validator = $this->get('validator');

        // On déclenche la validation sur notre object
        $listErrors = $validator->validate($advert);
        $errorsString = (string) $listErrors;

        if ($form->isSubmitted() && $form->isValid()) {
//            //Création de l'image associée à l'annonce
//            $image = new Image();
//            $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
//            $image->setAlt('Ligne de metro');
//
//            //Image est associée à l'annonce
//            $advert->setImage($image);
            if(count($listErrors) > 0) {
                return new Response($errorsString);
            }
            else {

                //Envoie des données remplies par l'utilisateur
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
                $slug =$advert->getSlug().$advert->getId();
                //Redirection vers la route précisée
                return $this->redirectToRoute('oc_platform_view', array(
                    'slug' => $slug));
                return new Response('Le formulaire est valide');

            }
        }

        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        return $this->render('OCPlatformBundle:Advert:form.html.twig', array(
            'form' => $form->createView(),
            'errorsString' => $errorsString,
            'adverts' => $adverts
        ));

    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // La méthode findAll retourne toutes les catégories de la base de données
        $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        // On boucle sur les catégories pour les lier à l'annonce
        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }


        // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
        // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        // Étape 2 : On déclenche l'enregistrement
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));
    }

    public function menuAction()
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 2, 'title' => 'Recherche développeur Symfony2'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }
    public function serviceAction(Request $request)
    {
        // On récupère le service
        $antispam = $this->container->get('oc_platform.antispam');

        // Je pars du principe que $text contient le texte d'un message quelconque
        $text = '...';
        if ($antispam->isSpam($text)) {
            throw new \Exception('Votre message a été détecté comme spam !');
        }

        // Ici le message n'est pas un spam
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On boucle sur les catégories de l'annonce pour les supprimer
        foreach ($advert->getCategories() as $category) {
            $advert->removeCategory($category);
        }

        // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
        // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        // On déclenche la modification
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));
    }

    //Update de l'élément ayant pour id $id
    public function update1Action($id, Advert $advert)
    {
//        $advert = new Advert();

        $advert->setTitle('title');

        $advert->setAuthor('hermes');

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);

        // Étape 2 : On déclenche l'enregistrement
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));

    }
    //Update de l'élément ayant pour id $id
    public function updateAction()
    {
        $image = new Image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('Job de rêve');

        $advert = new Advert();
        $advert->setTitle('Recherche Emploi');
        $advert->setContent('Je recherche activement un emploi');
        $advert->setAuthor('Pile');

        $advert->setImage($image);

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);

        // Étape 2 : On déclenche l'enregistrement
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));

    }
    public function listenAction()
    {
        // On instancie notre listener
        $betaListener = new BetaListener('2014-10-20');

        // On récupère le gestionnaire d'évènements, qui heureusement est un service !
        $dispatcher = $this->get('event_dispatcher');

        // On dit au gestionnaire d'exécuter la méthode onKernelResponse de notre listener
        // Lorsque l'évènement kernel.response est déclenché
        $dispatcher->addListener(
            'kernel.response',
            array($betaListener, 'processBeta')
        );
    }

}