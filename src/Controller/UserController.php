<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Friend;
use App\Entity\Game;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\EmailRepository;
use App\Repository\FriendRepository;
use App\Repository\GameRepository;
use App\Repository\LogRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{

    private function checkUser() {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }
    }

    private function randomDeck($playerCards) {

        shuffle($playerCards);
        $playerCardsArray = array();

        $column = 1;
        $row = 2;
        foreach ($playerCards as $key=>$card) {
            if ($card->getType() == 4) {
                $playerCardsArray[] = array(
                    'id' => $card->getId(),
                    'column' => 1,
                    'row' => 1,
                    'status' => 0
                );
                unset($playerCards[$key]);
            } else {
                if ($column == 1) {
                    if ($row == 4) {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $column++;
                        $row = 1;
                    } else {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $row++;
                    }
                } elseif ($column == 2) {
                    if ($row == 3) {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $column ++;
                        $row = 1;
                    } else {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $row++;
                    }
                } elseif ($column == 3) {
                    if ($row == 2) {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $column ++;
                        $row = 1;
                    } else {
                        $playerCardsArray[] = array(
                            'id' => $card->getId(),
                            'column' => $column,
                            'row' => $row,
                            'status' => 0
                        );
                        unset($playerCards[$key]);
                        $row++;
                    }
                } else {
                    $playerCardsArray[] = array(
                        'id' => $card->getId(),
                        'column' => $column,
                        'row' => $row,
                        'status' => 0
                    );
                    unset($playerCards[$key]);
                }

            }
        }

        return $playerCardsArray;
    }

    /* DASHBOARD */

    /**
     * @Route("/dashboard", name="user_dashboard", methods={"GET"})
     */
    public function dashboard(UserRepository $userRepository): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        return $this->render('user/dashboard.html.twig');
    }


    /* PLAY PLAY PLAY PLAY PLAY PLAY PLAY PLAY PLAY PLAY */

    /**
     * @Route("/play", name="user_play")
     */
    public function play(GameRepository $gameRepository): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $games1 = $gameRepository->findBy(['playerOne' => $this->getUser(), 'winner' => null]);
        $games2 = $gameRepository->findBy(['playerTwo' => $this->getUser(), 'winner' => null]);
        $games = array();

        foreach ($games1 as $game) {
            $playerTwo = $game->getPlayerTwo();
            $game = array(
                'id' => $game->getId(),
                'endedAt' => $game->getEndedAt()
            );

            if (!empty($playerTwo)) {
                $game['opponent'] = $playerTwo->getUsername();
            } else {
                $game['opponent'] = null;
            }

            $games[] = $game;
        }

        foreach ($games2 as $game) {
            $game = array(
                'id' => $game->getId(),
                'opponent' => $game->getPlayerOne()->getUsername(),
                'endedAt' => $game->getEndedAt()
            );

            $games[] = $game;
        }

        return $this->render('user/play/play.html.twig', [
            'games' => $games
        ]);
    }

    /**
     * @Route("/play/create", name="user_play_create")
     */
    public function playCreate(UserRepository $userRepository,FriendRepository $friendRepository, LogRepository $logRepository): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $friends = $this->getUser()->getUserFriends($userRepository, $friendRepository, $logRepository);

        $suggestions = $this->getUser()->getUserSuggestions($userRepository,$friendRepository, $logRepository);

        return $this->render('user/play/create.html.twig', [
            'friends' => $friends,
            'suggestions' => $suggestions
        ]);

    }

    /**
     * @Route("/play/create/game/{id}", name="user_play_create_game", defaults={"id"=null})
     */
    public function playCreateGame($id, ObjectManager $manager, CardRepository $cardRepository, UserRepository $userRepository)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $setTeam = mt_rand(1,2); // 1=Arlette  2=Maurice
        if ($setTeam == 1) {
            $playerOneCards = $cardRepository->findBy(['team' => 1]);
            $playerTwoCards = $cardRepository->findBy(['team' => 2]);
        } else {
            $playerOneCards = $cardRepository->findBy(['team' => 2]);
            $playerTwoCards = $cardRepository->findBy(['team' => 1]);
        }
        $playerOneCards = $this->randomDeck($playerOneCards);
        $playerTwoCards = $this->randomDeck($playerTwoCards);

        $game = New Game();
        $game
            ->setPlayerOne($this->getUser())
            ->setPlayerOneField(array($playerOneCards))
            ->setPlayerTwoField(array($playerTwoCards))
            ->setCreatedAt(new \DateTime())
            ->setEndedAt(new \DateTime())
            ->setChat(array())
            ->setStatus(0)
        ;



        if ($id == null) {

            $dice = array(
                'turn' => $this->getUser()->getId(),
                'dices' => array(
                    array(
                        'type' => 1,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                    array(
                        'type' => 2,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                    array(
                        'type' => 3,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                ),
            );

        } else {
            $turn = array($this->getUser()->getId(), $id);
            $turn = $turn[array_rand($turn)];
            $user = $userRepository->findOneById($id);
            $dice = array(
                'turn' => $turn,
                'dices' => array(
                    array(
                        'type' => 1,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                    array(
                        'type' => 2,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                    array(
                        'type' => 3,
                        'value' => mt_rand(1,3),
                        'status' => 0
                    ),
                ),
            );
            $game->setPlayerTwo($user);

        }

        $game->setDice(array($dice));

        $manager->persist($game);
        $manager->flush();
        return $this->redirectToRoute('game_field', ['game' => $game->getId()]);
        /*return $this->render('user/play/create.html.twig');*/

    }


    /* FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS FRIENDS */

    /**
     * @Route("/friends", name="user_friends")
     */
    public function friends(Request $request, FriendRepository $friendRepository, UserRepository $userRepository, LogRepository $logRepository): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $searchUser = null;
        $defaultData = ['user' => ''];
        $form = $this->createFormBuilder($defaultData)
            ->add('user', TextType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $searchUser = $form->getData()["user"];
            $searchUser = $userRepository->findOneByUsername($searchUser);

        }

        /* GET USER FRIENDS */

        $friends = $this->getUser()->getUserFriends($userRepository, $friendRepository, $logRepository);

        /* GET FRIEND REQUESTS RECEIVED */

        $friendRequestsReceived = $this->getUser()->getFriendRequestsReceived($friendRepository);

        /* GET FRIEND REQUESTS SENT*/

        $friendRequestsSent = $this->getUser()->getFriendRequestsSent($friendRepository);

        /* GET USER SUGGESTIONS */

        $suggestions = $this->getUser()->getUserSuggestions($userRepository,$friendRepository, $logRepository);

        /*RENDER*/

        return $this->render('user/friends.html.twig', [
            'friends' => $friends,
            'friendRequestsReceived' => $friendRequestsReceived,
            'friendRequestsSent' => $friendRequestsSent,
            'suggestions' => $suggestions,
            'form' => $form->createView(),
            'searchUser' => $searchUser
        ]);
    }

    /**
     * @Route("/friends/request/{id}/accept", name="user_friend_request_accept", methods={"GET"})
     */
    public function acceptRequest(ObjectManager $manager,FriendRepository $friendRepository, User $user): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $friendRequestReceived = $friendRepository->findOneBy(['sender' => $user->getId(), 'recipient' => $this->getUser()->getId(), 'status' => 0]);

        if ($friendRequestReceived == null) {
            return $this->redirectToRoute('user_friends');
        }

        $friendRequestReceived->setStatus(1);
        $manager->persist($friendRequestReceived);
        $manager->flush();
        return $this->redirectToRoute('user_friends');
    }

    /**
     * @Route("/friends/request/{id}/deny", name="user_friend_request_deny", methods={"GET"})
     */
    public function denyRequest(ObjectManager $manager,FriendRepository $friendRepository, User $user): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $friendRequestReceived = $friendRepository->findOneBy(['sender' => $user->getId(), 'recipient' => $this->getUser()->getId(), 'status' => 0]);

        if ($friendRequestReceived == null) {
            return $this->redirectToRoute('user_friends');
        }

        $manager->remove($friendRequestReceived);
        $manager->flush();
        return $this->redirectToRoute('user_friends');
    }

    /**
     * @Route("/friends/request/{id}/add", name="user_friend_request_add", methods={"GET"})
     */
    public function addRequest(ObjectManager $manager, UserRepository $userRepository,FriendRepository $friendRepository, User $user): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $friendRequestsSent = $friendRepository->findBy(['sender' => $this->getUser()->getId(), 'recipient' => $user->getId(), 'status' => 0]);
        $alreadyfriends = $friendRepository->findBy(['sender' => $this->getUser()->getId(), 'recipient' => $user->getId(), 'status' => 1]);
        $alreadyfriends2 = $friendRepository->findBy(['sender' => $user->getId(), 'recipient' => $this->getUser()->getId(), 'status' => 1]);
        if (!empty($friendRequestsSent) || !empty(array_merge($alreadyfriends, $alreadyfriends2)) || $user == $this->getUser() || $user->getStatus() == 3) {
            return $this->redirectToRoute('user_friends');
        }

        $friendRequestToSend = new Friend();
        $friendRequestToSend
            ->setSender($this->getUser())
            ->setRecipient($user)
            ->setStatus(0);

        $manager->persist($friendRequestToSend);
        $manager->flush();

        return $this->redirectToRoute('user_friends');

    }

    /**
     * @Route("/friends/request/{id}/delete", name="user_friend_request_delete", methods={"GET"})
     */
    public function deleteRequest(ObjectManager $manager, UserRepository $userRepository,FriendRepository $friendRepository, User $user): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }
        $friendRequestsSent = $friendRepository->findBy(['sender' => $this->getUser()->getId(), 'recipient' => $user->getId(), 'status' => 0]);
        if (empty($friendRequestsSent)) {
            return $this->redirectToRoute('user_friends');
        }

        foreach ($friendRequestsSent as $friendRequestSent) {
            $manager->remove($friendRequestSent);
        }

        $manager->flush();
        return $this->redirectToRoute('user_friends');

    }

    /**
     * @Route("/friend/{id}/delete", name="user_friend_delete", methods={"GET"})
     */
    public function deleteFriend(ObjectManager $manager, UserRepository $userRepository,FriendRepository $friendRepository, User $user): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $friend = $friendRepository->findBy(['sender' => $this->getUser()->getId(), 'recipient' => $user->getId(), 'status' => 1]);
        $friend2 = $friendRepository->findBy(['sender' => $user->getId(), 'recipient' => $this->getUser()->getId(), 'status' => 1]);
        $friendAll = array_merge($friend, $friend2);
        if (empty($friendAll)) {
            return $this->redirectToRoute('user_friends');
        }

        foreach ($friendAll as $friend) {
            $manager->remove($friend);
        }

        $manager->flush();
        return $this->redirectToRoute('user_friends');

    }


    /* MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES MESSAGES */

    /**
     * @Route("/messages/{id}/delete", name="user_message_delete", defaults={"id"=0})
     */
    public function deleteConversation(ObjectManager $manager,UserRepository $userRepository, MessageRepository $messageRepository,$id)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        if ($id !== 0) {
            $user = $userRepository->findOneById($id);
            $message = $messageRepository->findOneBy(['sender' => $this->getUser(), 'recipient' => $user]);
            $message2 = $messageRepository->findOneBy(['sender' => $user, 'recipient' => $this->getUser()]);

            if ($message !== null) {
                $manager->remove($message);
                $manager->flush();
                return $this->redirectToRoute('user_messages');
            } elseif ($message2 !== null) {
                $manager->remove($message2);
                $manager->flush();
                return $this->redirectToRoute('user_messages');
            }

            return $this->redirectToRoute('user_messages');
        }
    }

    /**
     * @Route("/messages/{id}", name="user_messages", defaults={"id"=0})
     */
    public function messages(Request $request, ObjectManager $manager, MessageRepository $messageRepository, UserRepository $userRepository, $id)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $message = null;
        $conversationInfo = null;

        $defaultData = ['message' => ''];
        $form = $this->createFormBuilder($defaultData)
            ->add('message', TextType::class)
            ->getForm();

        if ($id !== 0) {

            $user = $userRepository->findOneById($id);
            $message = $messageRepository->findOneBy(['sender' => $this->getUser(), 'recipient' => $user]);
            $message2 = $messageRepository->findOneBy(['sender' => $user, 'recipient' => $this->getUser()]);

            if ($message == null && $message2 == null) {
                $message = new Message();
                $content = array();
                $content[] = array();

                $message
                    ->setSender($this->getUser())
                    ->setRecipient($user)
                    ->setCreatedAt(new \DateTime())
                    ->setEndedAt(new \DateTime())
                    ->setContent($content);

                $manager->persist($message);
                $manager->flush();

            } else {
                if ($message2 !== null) {
                    $message = $message2;
                }
            }

            $conversationContent = $message->getContent();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $msg = $form->getData()["message"];
                $conversationContent[] = array(
                    'id' => $this->getUser()->getId(),
                    'message' => $msg,
                    'createdAt' => new \DateTime()
                );

                $message->setContent($conversationContent);
                $manager->flush();
                return $this->redirect($request->getUri());
            }


            $conversation = array();

            foreach ($conversationContent as $msg) {
                if (!empty($msg)) {
                    $conversation[] = array(
                        'id' => $msg['id'],
                        'username' => $userRepository->findOneById($msg['id'])->getUsername(),
                        'message' => $msg['message'],
                        'createdAt' => $msg['createdAt']
                    );
                }
            }

            $conversationInfo = array(
                'id' => $id,
                'username' => $userRepository->findOneById($id)->getUsername(),
                'endedAt' => $message->getEndedAt(),
                'messages' => $conversation
            );

        } else {
            $message = null;
        }


        $allMessages = $messageRepository->getAllMessages($this->getUser()->getId());
        $allMessagesArray = array();

        foreach ($allMessages as $message) {

            if ($message->getSender()->getId() == $this->getUser()->getId()) {
                $friendId = $message->getRecipient()->getId();
                $friendUsername = $message->getRecipient()->getUsername();
            } else {
                $friendId = $message->getSender()->getId();
                $friendUsername = $message->getSender()->getUsername();
            }

            $content = $message->getContent();
            $preview = end($content);

            if ($message->getSender()->getId() !== $this->getUser()->getId() && empty(end($content))) {

            } else {
                $allMessagesArray[] = array(
                    'id' => $friendId,
                    'username' => $friendUsername,
                    'preview' => $preview,
                    'endedAt' => $message->getEndedAt()
                );
            }

        }

        $allMessages = $allMessagesArray;

        return $this->render('user/messages.html.twig', [
            'user' => $this->getUser(),
            'allMessages' => $allMessages,
            'conversationInfo' => $conversationInfo,
            'form' => $form->createView()
        ]);

    }


    /* NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS NOTIFICATIONS */

    /**
     * @Route("/notifications/{id}", name="user_notifications", defaults={"id"=0})
     */
    public function notifications(EmailRepository $emailRepository, $id)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $notification = null;

        if ($id !== 0) {
            $notification = $emailRepository->findOneBy(['id' => $id, 'recipient' => $this->getUser()]);
        }

        /* GET ALL NOTIFICATIONS */

        $notifications = $emailRepository->findBy(['recipient' => $this->getUser()], ['id' => 'DESC']);

        return $this->render('user/notifications.html.twig', [
            'notifications' => $notifications,
            'notification' => $notification
        ]);
    }

    /**
     * @Route("/notification/{id}/delete", name="user_notification_delete", defaults={"id"=0})
     */
    public function deleteNotification(ObjectManager $manager,EmailRepository $emailRepository, $id)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        if ($id !== 0) {
            $notification = $emailRepository->findOneBy(['id' => $id, 'recipient' => $this->getUser()]);
            if ($notification !== null) {
                $manager->remove($notification);
                $manager->flush();
                return $this->redirectToRoute('user_notifications');
            }
        }

        return $this->redirectToRoute('user_notifications');

    }

    /**
     * @Route("/leaderboard", name="user_leaderboard")
     */
    public function leaderboard(ObjectManager $manager, UserRepository $userRepository)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $wins = count($this->getUser()->getWinnerGames());

        $games = count($this->getUser()->getPlayerOneGames()) + count($this->getUser()->getPlayerTwoGames());


        return $this->render('user/leaderboard.html.twig', [
            "wins" => $wins,
            "games" => $games
        ]);

    }

    /**
     * @Route("/profile", name="user_pofile")
     */
    public function profile(ObjectManager $manager, Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        } elseif(in_array($this->getUser()->getStatus(), array(2,3))) {
            return $this->redirectToRoute('security_logout');
        }

        $user = $this->getUser();

        $defaultData = ['message' => 'Update User'];
        $form = $this->createFormBuilder($defaultData)
            ->add('username', TextType::class)
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            if ($data['username'] !== "") {
                $checkUsername = $userRepository->findBy(['username' => $data['username']]);
                if (count($checkUsername) == 0) {
                    $user->setUsername($data['username']);
                }
                if ($data['password'] !== "" && $data['confirm_password'] !== "" && $data['password'] == $data['confirm_password']) {
                    $hash = $encoder->encodePassword($user, $data['password']);
                    $user->setPassword($hash);
                }
                $manager->flush();
            }
        }

        $user = $this->getUser();


        return $this->render('user/profile.html.twig', [
            "username" => $user->getUsername(),
            "picture" => $user->getPicture(),
            'form' => $form->createView(),
        ]);

    }



}
