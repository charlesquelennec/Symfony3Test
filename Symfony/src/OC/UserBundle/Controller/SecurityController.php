<?php
// src/OC/UserBundle/Controller/SecurityController.php;

namespace OC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('oc_platform_accueil');
        }

        // Le service authentication_utils permet de récupérer le nom d'utilisateur
        // et l'erreur dans le cas où le formulaire a déjà été soumis mais était invalide
        // (mauvais mot de passe par exemple)
        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('OCUserBundle:Security:login.html.twig', array(
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ));
    }

    public function login_checkAction(Request $request)
    {
        $user = $this->getUser();

        if (null === $user) {
            // Ici, l'utilisateur est anonyme ou l'URL n'est pas derrière un pare-feu
        } else {
            // Ici, $user est une instance de notre classe User
        }

        return $this->render('OCUserBundle:Security:login_check.html.twig', array('user' => $user
        ));
    }
    public  function  user_managerAction()
    {
        // Dans un contrôleur :

        // Pour récupérer le service UserManager du bundle
        $userManager = $this->get('fos_user.user_manager');

        // Pour charger un utilisateur
        $user = $userManager->findUserBy(array('username' => 'winzou'));

        // Pour modifier un utilisateur
        $user->setEmail('cetemail@nexiste.pas');
        $userManager->updateUser($user); // Pas besoin de faire un flush avec l'EntityManager, cette méthode le fait toute seule !

        // Pour supprimer un utilisateur
        $userManager->deleteUser($user);

        // Pour récupérer la liste de tous les utilisateurs
        $users = $userManager->findUsers();

        //pour plus de fonction faire:
        $this->getDoctrine()->getManager()->getRepository('OCUserBundle:User');

    }

}