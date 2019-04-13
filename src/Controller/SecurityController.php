<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailResetType;
use App\Services\Mailer;
use Faker\Factory;
use App\Form\SignUpType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     */
    public function logInUser(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()){
            return $this->redirectToRoute('security_logout');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginUser.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/signup", name="security_signup")
     */
    public function signUp(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $faker = Factory::create();
        if ($this->getUser()){
            return $this->redirectToRoute('security_logout');
        }

        $user = new User();

        $form = $this->createForm(SignUpType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setCreatedAt(new \DateTime());

            $user->setStatus(0);

            $user->setPicture($faker->imageUrl(400, 400, 'cats'));
            $user->setRank(0);

            $user->setRoles(['ROLE_USER']);

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            $message = (new \Swift_Message('Inscription réussie'))
                ->setFrom('arletteetmaurice@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Bienvenue dans l'aventure d'Arlette & Maurice",
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /*RESET PASSWORD*/

    /**
     * @Route("/forgotten-password", name="security_forgotten_password")
     */
    public function forgottenPassword(Request $request, ObjectManager $manager, UserRepository $userRepo, UserPasswordEncoderInterface $encoder, Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');

            $manager = $this->getDoctrine()->getManager();
            $user = $userRepo->findOneByEmail($email);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->redirectToRoute('home');
            }

            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $manager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('home');
            }

            $url = $this->generateUrl('security_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);


            $sender = "arletteetmaurice@gmail.com";
            $recipient = $email;
            $subject = 'Arlette & Maurice - Mot de passe oublié';
            $body = "Veuillez cliquer sur ce lien pour réinitialiser votre mot de passe : " . $url;
            $mailer->sendMessage($sender, $recipient, $subject, $body);


            $this->addFlash('notice', 'Mail envoyé');

            return $this->redirectToRoute('home');
        }

        return $this->render('security/forgotten_password.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="security_reset_password")
     */
    public function resetPassword(Request $request, ObjectManager $manager, UserRepository $userRepo,string $token, UserPasswordEncoderInterface $encoder)
    {

        if ($request->isMethod('POST')) {

            $user = $userRepo->findOneByResetToken($token);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }

            $user->setResetToken(null);
            $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
            $manager->flush();

            $this->addFlash('notice', 'Mot de passe mis à jour');

            return $this->redirectToRoute('security_login');
        }else {

            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }

    }


    /*LOGIN ADMIN*/

    /**
     * @Route("/login/admin", name="security_login_admin")
     */
    public function logInAdmin(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()){
            $userRole = $this->getUser()->getRoles()[0];
            if ($userRole == "ROLE_USER") {
                return $this->redirectToRoute('home');
            } else {
                return $this->redirectToRoute('admin_dashboard');
            }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginAdmin.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout() {}

}
