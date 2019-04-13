<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\Game;
use App\Entity\User;
use App\Form\EditUserType;
use App\Form\EmailType;
use App\Form\NewUserType;
use App\Repository\FriendRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Services\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/dashboard", name="admin_dashboard")
     */
    public function dashboard()
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminInterfaceController',
        ]);
    }


    /* UTILISATEURS */

    /**
     * @Route("/users/{page}", name="admin_users", defaults={"page"=1})
     */
    public function showUsers(UserRepository $userRepo, GameRepository $gameRepo, $page)
    {
        $usersPerPage = 40;

        $users = $userRepo->getUsersPaginated($page, $usersPerPage);
        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($users) / $usersPerPage),
            'routeName' => 'admin_users',
            'routeParams' => array()
        );

        $usersArray = array();

        foreach ($users as $user) {
            if ($user->getRoles()[0] == "ROLE_USER") {
                $games = $gameRepo->count(['playerOne' => $user]);
                $games += $gameRepo->count(['playerTwo' => $user]);
                $wins = $gameRepo->count(['winner' => $user]);
                if ($games == 0) {
                    $rank = 0;
                } else {
                    $rank = round(($wins/$games)*100, 1);
                }
                $usersArray[] = array(
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'createdAt' => $user->getCreatedAt(),
                    'picture' => $user->getPicture(),
                    'email' => $user->getEmail(),
                    'games' => $games,
                    'wins' => $wins,
                    'status' => $user->getStatus(),
                    'rank' => $rank
                );
            };
        }

        $users = $usersArray;

        //dump($result); die();

        return $this->render('admin/user/users.html.twig', [
            'users' => $users,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/user/new", name="admin_user_new", methods={"GET","POST"})
     */
    public function newUser(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(NewUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setCreatedAt(new \DateTime());

            $user->setStatus(0);

            $user->setPicture("");
            $user->setRank(0);

            $user->setRoles(['ROLE_USER']);

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('admin_user_new');
        }

        return $this->render('admin/user/newUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}", name="admin_user_profile", defaults={"id"=0})
     */
    public function showUser(UserRepository $userRepo, FriendRepository $friendRepo, GameRepository $gameRepo, $id)
    {
        if ($id == 0) {
            $this->redirectToRoute('admin_users');
        }

        $user = $userRepo->findOneById($id);
        $friends = $friendRepo->getFriends($id);
        $friendsArray = array();
        foreach ($friends as $friend) {
            if ($friend->getSender()->getId() == $id) {
                $friendUsername = $friend->getRecipient()->getUsername();
                $friendId = $friend->getRecipient()->getId();
            } else {
                $friendUsername = $friend->getSender()->getUsername();
                $friendId = $friend->getSender()->getId();
            }
            $friendsArray[] = array(
                "id" => $friendId,
                "username" => $friendUsername
            );
        }
        $friends = $friendsArray;

        $friendRequests = $friendRepo->getFriendRequestsReceived($id);

        $games = $gameRepo->count(['playerOne' => $user]);
        $games += $gameRepo->count(['playerTwo' => $user]);
        $wins = $gameRepo->count(['winner' => $user]);
        if ($games == 0) {
            $rank = 0;
        } else {
            $rank = round(($wins/$games)*100, 1);
        };
        $user = array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt(),
            'picture' => $user->getPicture(),
            'email' => $user->getEmail(),
            'games' => $games,
            'wins' => $wins,
            'status' => $user->getStatus(),
            'rank' => $rank,
            'friends' => $friends,
            'friendRequests' =>$friendRequests
        );

        //dump($result); die();

        return $this->render('admin/user/user.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="admin_user_edit", methods={"GET","POST"})
     */
    public function edit(ObjectManager $manager, Request $request, User $user): Response
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

        }

        return $this->render('admin/user/editUser.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/user/{id}/status/{status}", name="admin_user_status")
     */
    public function changeUserStatus(Mailer $mailer,ObjectManager $manager, User $user, $status)
    {
        $user->setStatus($status);
        $manager->persist($user);
        $manager->flush();

        $admin = $this->getUser();
        $sender = "arletteetmaurice@gmail.com";
        $recipient = $user->getEmail();
        $subject = 'Arlette & Maurice - Message important';
        $body = "";

        if ($status == 3) {
           $body = "Votre compte a été supprimé.";
        } elseif ($status == 2) {
            $body = "Votre compte a été banni.";
        } elseif ($status == 0) {
            $body = "Bienvenue de retour parmi nous!";
        }

       $email = new Email();
       $email
           ->setSender($admin)
           ->setRecipient($user)
           ->setSubject($subject)
           ->setContent($body)
           ->setCreatedAt(new \DateTime())
           ->setStatus(0)
        ;
        $manager->persist($email);
        $manager->flush();

        /*$bodyMail = $mailer->createBodyMail('emails/status.html.twig', [
            'status' => $status
        ]);*/

        $mailer->sendMessage($sender, $recipient, $subject, $body);



        return $this->json([
            'code' => 200,
            'status' => $status
        ]);

    }

    /**
     * @Route("/user/{id}/warn", name="admin_user_warn")
     */
    public function warnUser(Mailer $mailer, ObjectManager $manager, User $user)
    {
        $admin = $this->getUser();
        $sender = "arletteetmaurice@gmail.com";
        $recipient = $user->getEmail();
        $subject = 'Arlette & Maurice - Avertissement';
        $body = "Ceci est un message d'avertissement";

        $email = new Email();
        $email
            ->setSender($admin)
            ->setRecipient($user)
            ->setSubject($subject)
            ->setContent($body)
            ->setCreatedAt(new \DateTime())
            ->setStatus(0)
        ;
        $manager->persist($email);
        $manager->flush();

        $mailer->sendMessage($sender, $recipient, $subject, $body);

        return $this->json([
            'code' => 200,
        ]);

    }


    /* PARTIES */

    /**
     * @Route("/games/{page}", name="admin_games", defaults={"page"=1})
     */
    public function games(GameRepository $gameRepo, $page)
    {
        $gamesPerPage = 40;

        /*$games = $gameRepo->findAll();*/
        $games = $gameRepo->getGamesPaginated($page, $gamesPerPage);

        $gamesArray = array();

        foreach ($games as $game) {

            if ($game->getWinner() !== null) {
                $winner = array(
                   'id' => $game->getWinner()->getId(),
                   'username' => $game->getWinner()->getUsername()
                );
            } else {
                $winner = null;
            }

            $gameArray = array(
                'id' => $game->getId(),
                'createdAt' => $game->getCreatedAt(),
                'endedAt' => $game->getEndedAt(),
                'playerOne' => array(
                    'id' => $game->getPlayerOne()->getId(),
                    'username' => $game->getPlayerOne()->getUsername()
                ),
                'winner' => $winner
            );

            if ($game->getPlayerTwo() !== null) {
                $gameArray['playerTwo'] = array(
                    'id' => $game->getPlayerTwo()->getId(),
                    'username' => $game->getPlayerTwo()->getUsername()
                );
            } else {
                $gameArray['playerTwo'] = null;
            }

            $gamesArray[] = $gameArray;
        }

        $games = $gamesArray;

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($games) / $gamesPerPage),
            'routeName' => 'admin_users',
            'routeParams' => array()
        );

        return $this->render('admin/game/games.html.twig', [
            'games' => $games,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/game/{id}/delete", name="admin_game_delete", defaults={"id"=0})
     */
    public function deleteGame(ObjectManager $manager, Game $game)
    {
        $manager->remove($game);
        $manager->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Partie supprimé'
        ]);
    }


    /* CARTES */

    /**
     * @Route("/cards", name="admin_cards")
     */
    public function showCards() {
        return $this->render('admin/card/cards.html.twig');
    }

    /* MAIL */

    /**
     * @Route("/email", name="admin_email_all")
     */
    public function emailAll(Request $request, Mailer $mailer, ObjectManager $manager, UserRepository $userRepo)
    {
        $email = new Email();

        $form = $this->createForm(EmailType::class, $email);

        $form->handleRequest($request);

        $users = $userRepo->findAll();
        $usersArray = array();
        foreach ($users as $user) {
            if ($user->getRoles()[0] == 'ROLE_USER') {
                $usersArray[] = $user;
            }
        }

        $users = $usersArray;

        if ($form->isSubmitted() && $form->isValid()) {

            $date = new \DateTime();
            $subject = "Arlette & Maurice - ".$form->get("subject")->getData();
            $body = $form->get("content")->getData();

            foreach ($users as $user) {

                $email = new Email();

                $email
                    ->setSender($this->getUser())
                    ->setRecipient($user)
                    ->setSubject($subject)
                    ->setContent($body)
                    ->setCreatedAt($date)
                    ->setStatus(0)
                ;

                $manager->persist($email);

                $sender = "arletteetmaurice@gmail.com";
                $recipient = $user->getEmail();


                $mailer->sendMessage($sender,$recipient, $subject, $body);

            }

            $manager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/email/email-all.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/email/{id}", name="admin_email_user")
     */
    public function emailUser(Request $request, Mailer $mailer, ObjectManager $manager, User $user)
    {
        $email = new Email();

        $form = $this->createForm(EmailType::class, $email);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = new \DateTime();
            $admin = $this->getUser();

            $sender = "arletteetmaurice@gmail.com";
            $recipient = $user->getEmail();
            $subject = "Arlette & Maurice - ";
            $subject .= $form->get("subject")->getData();
            $body = $form->get("content")->getData();

            $email
                ->setSender($admin)
                ->setRecipient($user)
                ->setSubject($subject)
                ->setContent($body)
                ->setCreatedAt($date)
                ->setStatus(0)
            ;

            $manager->persist($email);
            $manager->flush();



            $mailer->sendMessage($sender,$recipient, $subject, $body);

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/email/email-user.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);

    }

}
