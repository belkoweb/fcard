<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\DailyCount;
use App\Entity\LastConnection;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        // the object bound to the form
        $user = new User();
        
        // creation of the form with the bound object
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            // add power to user (it's best to add ["ROLE_ADMIN"] directly in database)
            // if ($user->getEmail() == 'XXXXXXXXXXX') {
            //    $user->setRoles(['ROLE_ADMIN']);
            // }

            // add daily count to User parameters
            $userCount = new DailyCount();
            $userCount->setUser($user);
            $manager->persist($userCount);

            // add last connection to User parameters
            $userConnection = new LastConnection();
            $userConnection->setUser($user);
            $userConnection->setUpdatedAt(new \DateTime);
            $manager->persist($userConnection);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login'); // redirection after registration
        }


        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="security_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
