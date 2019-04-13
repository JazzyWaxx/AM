<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/game")
 */
class GameController extends AbstractController
{
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

    private function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    private function getCardsInfo($playerField) {
        $cardRepository = $this->getDoctrine()->getRepository(Card::class);
        $playerFieldArray = array();

        foreach ($playerField as $key=>$card) {
            $cardObject = $cardRepository->findOneById($card["id"]);
            if ($card["status"] !== 1) {
                $card["name"] = $cardObject->getName();
                $card["type"] = $cardObject->getType();
                $card["strengh"] = $cardObject->getStrengh();
                $card["picture"] = $cardObject->getPicture();
                $playerFieldArray[] = $card;
            }

        }
        return $playerFieldArray;
    }

    /**
     * @Route("/{game}", name="game_field")
     */
    public function showGame(Game $game)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('security_login');
        }

        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie");
        }

        /*$playerOneFieldAll = $game->getPlayerOneField();
        $playerOneFieldRaw = end($playerOneFieldAll);
        $playerOneField = $this->getCardsInfo($playerOneFieldRaw);
        $playerTwoFieldAll = $game->getPlayerTwoField();
        $playerTwoFieldRaw = end($playerTwoFieldAll);
        $playerTwoField = $this->getCardsInfo($playerTwoFieldRaw);

        for ($i = 1; $i <= 11; $i++) {

            $playerOneColumn = array_values($this->array_sort(array_filter($playerOneField, function($v) use ($i) { return $v['column'] == $i; }, ARRAY_FILTER_USE_BOTH), "row", SORT_ASC));
            $playerTwoColumn = array_values($this->array_sort(array_filter($playerTwoField, function($v) use ($i) { return $v['column'] == 12 - $i; }, ARRAY_FILTER_USE_BOTH), "row", SORT_ASC));


            if (!empty($playerOneColumn) && !empty($playerTwoColumn)) {

                dump("Player1");
                dump($playerOneColumn);
                dump("Player2");
                dump($playerTwoColumn);

                while (!empty(end($playerOneColumn)) && !empty(end($playerTwoColumn))) {
                    $cardP1 = end($playerOneColumn);
                    $cardP2 = end($playerTwoColumn);
                    $typeDiff = $cardP1['type'] - $cardP2['type'];
                    if ($typeDiff == 0) { // same card type

                        $powerDiff = $cardP1['strengh'] - $cardP2['strengh'];

                        if ($powerDiff == 0) { // Reculer

                            // P1
                            foreach ($playerOneColumn as $card1) {
                                foreach ($playerOneFieldRaw as $card2) {
                                    if ($card2['id'] == $card1['id']) {

                                    }
                                }
                            }

                            // P2
                            foreach ($playerTwoColumn as $card1) {
                                foreach ($playerOneFieldRaw as $card2) {
                                    if ($card2['id'] == $card1['id']) {

                                    }
                                }
                            }

                        } elseif ($powerDiff == -1 || $powerDiff == -2) { // P2 gagne ((cardP1 = 1 && cardP2 = 2) || (cardP1 = 2 && cardP2 = 3)) || (cardP1 = 1 && cardP2 = 3)
                            $win = "P2";
                        } elseif ($powerDiff == 1 || $powerDiff == 2) { //P1 gagne ((cardP1 = 2 && cardP2 = 1) || (cardP1 = 3 && cardP2 = 2)) || (cardP1 = 3 && cardP2 = 1)
                            $win = "P1";
                        }

                    } elseif ($typeDiff == -1 || $typeDiff == -2) { // ((cardP1 = 1 && cardP2 = 2) || (cardP1 = 2 && cardP2 = 3)) || (cardP1 = 1 && cardP2 = 3)
                        if ($cardP1['type'] == 1 && $cardP2['type'] == 3) { // P1 Gagne
                            $win = "P1";
                        } else {  // P2 gagne
                            $win = "P2";
                        }
                    } elseif ($typeDiff == 1 || $typeDiff == 2) { // ((cardP1 = 2 && cardP2 = 1) || (cardP1 = 3 && cardP2 = 2)) || (cardP1 = 3 && cardP2 = 1)
                        if ($cardP1['type'] == 3 && $cardP2['type'] == 1) { // P2 Gagne
                            $win = "P2";
                        } else { // P1 gagne
                            $win = "P1";
                        }
                    }

                    if ($win == "P1") { // Supprimer carte player 2
                        $cardId = $cardP2['id'];
                        foreach ($playerTwoColumn as $key=>$card) {
                            if ($card['id'] == $cardId) {
                                unset($playerTwoColumn[$key]);
                            }
                        }
                        foreach ($playerTwoFieldRaw as $key=>$card) {
                            if ($card['id'] == $cardId) {
                                unset($playerTwoFieldRaw[$key]);
                            }
                        }
                    } else { // Supprimer carte player 2
                        dump("P2 gagne");
                        $cardId = $cardP1['id'];
                        foreach ($playerOneColumn as $key=>$card) {
                            if ($card['id'] == $cardId) {
                                unset($playerOneColumn[$key]);
                            }
                        }
                        foreach ($playerOneFieldRaw as $key=>$card) {
                            if ($card['id'] == $cardId) {
                                unset($playerOneFieldRaw[$key]);
                            }
                        }
                    }

                }

                dump("Player1");
                dump($playerOneColumn);
                dump("Player2");
                dump($playerTwoColumn);

                $playerOneField = $this->getCardsInfo($playerOneFieldRaw);
                $playerTwoField = $this->getCardsInfo($playerTwoFieldRaw);

            } else {

            }

        }

        $playerOneFieldAll[] = $playerOneFieldRaw;
        $playerTwoFieldAll[] = $playerTwoFieldRaw;

        $game->setPlayerOneField($playerOneFieldAll);
        $game->setPlayerTwoField($playerTwoFieldAll);
        $manager->flush();

        die();*/


        return $this->render('game/game.html.twig', [
            'gameId' => $game->getId()
        ]);
    }

    /**
     * @Route("/{game}/cardDelete/{userid}/{cardid}", name="game_card_delete", options={"expose"=true})
     */
    public function deleteCard(ObjectManager $manager,Game $game, $userid, $cardid)
    {
        if ($this->getUser() == null) {
            $code = 403;
        }

        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            $code = 403;
        } else {
            if ($game->getPlayerOne()->getId() == $userid ) {
                $userField = $game->getPlayerOneField();
            } else {
                $userField = $game->getPlayerTwoField();
            }

            $playerField = end($userField);

            foreach ($playerField as $key=>$card) {
                if ($card['id'] == $cardid) {
                    unset($playerField[$key]);
                }
            }

            $userField[] = $playerField;

            if ($game->getPlayerOne()->getId() == $userid ) {
                $game->setPlayerOneField($userField);
            } else {
                $game->setPlayerTwoField($userField);
            }

            $manager->flush();
            $code = 200;
        }

        return $this->json([
            'code' => $code
        ]);
    }

    /**
     * @Route("/{game}/reset", name="game_reset")
     */
    public function resetGame(ObjectManager $manager, UserRepository $userRepository, CardRepository $cardRepository, Game $game) {

        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie"); die();
        }

        if ($game->getPlayerOne() == $this->getUser()) {
            if ($game->getPlayerTwo() !== null) {
                $id = $game->getPlayerTwo()->getId();
            } else {
                $id = null;
            }

        } elseif ($game->getPlayerTwo() == $this->getUser()) {
            if ($game->getPlayerOne() !== null) {
                $id = $game->getPlayerOne()->getId();
            } else {
                $id = null;
            }
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

        $manager->flush();


        return $this->redirectToRoute('game_field', [
            'game' => $game->getId()
        ]);
    }

    /**
     * @Route("/{game}/data", name="game_data", options={"expose"=true})
     */
    public function gameData(Game $game)
    {
        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie");
        }

        if ($game->getPlayerOne() == $this->getUser()) {
            $myField = $game->getPlayerOneField();
            $opponentField = $game->getPlayerTwoField();
            if ($game->getPlayerTwo() !== null) {
                $opponentId = $game->getPlayerTwo()->getId();
            } else {
                $opponentId = null;
            }

        } else {
            $myField = $game->getPlayerTwoField();
            $opponentField = $game->getPlayerOneField();
            if ($game->getPlayerOne() !== null) {
                $opponentId = $game->getPlayerOne()->getId();
            } else {
                $opponentId = null;
            }
        }

        $dices = $game->getDice();
        $myField = end($myField);
        $opponentField = end($opponentField);

        $myField = $this->getCardsInfo($myField);
        $opponentField = $this->getCardsInfo($opponentField);

        $gameData = array(
            'gameId' => $game->getId(),
            'myId' => $this->getUser()->getId(),
            'myField' => $myField,
            'opponentId' => $opponentId,
            'opponentField' => $opponentField,
            'dices' => end($dices),
            'chat' => $game->getChat()
        );

        return $this->json($gameData);
    }

    /**
     * @Route("/{game}/dicestatus/{type}", name="game_change_dice_status", options={"expose"=true})
     */
    public function changeDiceStatus(ObjectManager $manager, Game $game, $type)
    {
        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie");
        }

        $allDices = $game->getDice();
        $key = count($allDices) - 1;
        $dices = end($allDices);
        $dicesPlayed = 0;

        if ($dices["turn"] == $this->getUser()->getId()) {
            $newDices = array();
            foreach ($dices["dices"] as $dice) {
                if ($dice["type"] == $type) {
                    $dice["status"] = 1;
                }
                if ($dice["status"] == 1) {
                    $dicesPlayed++;
                }
                $newDices[] = $dice;
            }
            $dices["dices"] = $newDices;
            $allDices[$key] = $dices;
            $game->setDice($allDices);
            $manager->flush();
            $code = 200;
        } else {
            $code = 403;
        }

        if ($dicesPlayed == 3) {
            if ($game->getPlayerOne() == $this->getUser()) {
                $opponentId = $game->getPlayerTwo()->getId();
            } else {
                $opponentId = $game->getPlayerOne()->getId();
            }

            $dice = array(
                'turn' => $opponentId,
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

            $allDices[] = $dice;
            $game->setDice($allDices);
            $manager->flush();
        }


        return $this->json([
            "code" => $code
        ]);
    }

    /**
     * @Route("/{game}/movecard/{user}/{card}/{value}", name="game_move_card", options={"expose"=true})
     */
    public function moveCard(ObjectManager $manager,Game $game, User $user, Card $card, $value)
    {
        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie");
        }

        if ($game->getPlayerOne() == $user) {
            $myField = $game->getPlayerOneField();
        } else {
            $myField = $game->getPlayerTwoField();
        }

        $cardInfo = null;
        $cardId = $card->getId();
        $myNewField = end($myField);
        foreach ($myNewField as $key=>$card) {
            if ($card["id"] == $cardId) {
                $cardInfo = $card;
                unset($myNewField[$key]);
            }
        }

        if ($cardInfo !== null) {
            $cardsToMove = array();
            $cardsToMove[] = $cardInfo;
            foreach ($myNewField as $key=>$card) {
                if ($card["column"] == $cardInfo["column"] && $card["row"] > $cardInfo["row"]) {
                    $cardsToMove[] = $card;
                    unset($myNewField[$key]);
                }
            }

            if (count($cardsToMove) <= 3) {

                $firstRowOfCurrentColumn = $cardInfo["row"];

                $nextColumn = $cardInfo["column"]+$value;
                if ($nextColumn > 11) {
                    $nextColumn = 11;
                }
                $cardsOnNextColumn = array();
                foreach ($myNewField as $key=>$card) {
                    if ($card["column"] == $nextColumn) {
                        $cardsOnNextColumn[] = $card;
                    }
                }

                $firstRowOfNextColumn = count($cardsOnNextColumn) + 1;
                $cardsMoved = array();
                foreach ($cardsToMove as $key=>$card) {
                    $card["column"] = $nextColumn;
                    $card["row"] = $card["row"] - $firstRowOfCurrentColumn + $firstRowOfNextColumn;
                    $cardsMoved[] = $card;
                }

                foreach ($cardsMoved as $card) {
                    $myNewField[] = $card;
                }

                $myField[] = $myNewField;

                if ($game->getPlayerOne() == $user) {
                    $game->setPlayerOneField($myField);
                } else {
                    $game->setPlayerTwoField($myField);
                }
                $manager->flush();

                $code = 200;
            } else {
                $code = 403;
            }


        } else {
            $code = 500;
        }

        return $this->json([
            "code" => $code
        ]);
    }

    /**
     * @Route("/{game}/next", name="game_next_turn", options={"expose"=true})
     */
    public function nextTurn(ObjectManager $manager, Game $game)
    {
        if ($game->getPlayerOne() !== $this->getUser() && $game->getPlayerTwo() !== $this->getUser()) {
            dump("Erreur: ce n'est pas votre partie");
        }

        $allDices = $game->getDice();
        $dices = end($allDices);
        $dicesPlayed = 0;
        foreach ($dices["dices"] as $dice) {
            if ($dice["status"] == 1) {
                $dicesPlayed++;
            }
        }


        if ($dices["turn"] == $this->getUser()->getId() && $dicesPlayed > 0) {
            if ($game->getPlayerOne() == $this->getUser()) {
                $opponentId = $game->getPlayerTwo()->getId();
            } else {
                $opponentId = $game->getPlayerOne()->getId();
            }

            $dice = array(
                'turn' => $opponentId,
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
            $allDices[] = $dice;
            $game->setDice($allDices);
            $manager->flush();
            $code = 200;
        } else {
            $code = 403;
        }


        return $this->json([
            "code" => $code
        ]);
    }

    /**
     * @Route("/{game}/message/{json}", name="game_message_send", options={"expose"=true})
     */
    public function sendMessage(ObjectManager $manager,Game $game, $json) {
        $json = json_decode($json, true);

        $chat = $game->getChat();
        $chat[] = $json;
        $game->setChat($chat);
        $manager->flush();

        return $this->json([
            'code' => 200
        ]);
    }

}
