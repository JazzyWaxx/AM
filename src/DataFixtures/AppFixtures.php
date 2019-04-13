<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * Encodeur de mot de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $users = [];

        $user = new User();
        $user->setUsername("AM")
            ->setEmail("arletteetmaurice@gmail.com")
            ->setPassword($this->encoder->encodePassword($user, 'nevadas4'))
            ->setRank(0)
            ->setPicture($faker->imageUrl(400, 400, 'cats'))
            ->setStatus(0)
            ->setRoles(['ROLE_ADMIN'])
            ->setCreatedAt($faker->dateTime('now'))
        ;

        $manager->persist($user);

        $user = new User();
        $user->setUsername("NathanTrmn987")
            ->setEmail("nathantrmn@gmail.com")
            ->setPassword($this->encoder->encodePassword($user, 'password'))
            ->setRank(0)
            ->setPicture($faker->imageUrl(400, 400, 'cats'))
            ->setStatus(0)
            ->setRoles(['ROLE_ADMIN'])
            ->setCreatedAt($faker->dateTimeBetween('-4 week', 'now'))
        ;

        $manager->persist($user);
        $users[] = $user;

        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setUsername($faker->userName)
                ->setEmail($faker->email)
                ->setPassword($this->encoder->encodePassword($user, 'password'))
                ->setRank(0)
                ->setPicture($faker->imageUrl(400, 400, 'cats'))
                ->setStatus(mt_rand(0, 3))
                ->setRoles(['ROLE_USER'])
                ->setCreatedAt($faker->dateTimeBetween('-4 week', 'now'))
            ;
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->persist($user);

        for ($i = 0; $i < 300; $i++) {
            $game = new Game();
            $playerOne = $faker->randomElement($users);
            $playerTwo = $faker->randomElement($users);
            while ($playerOne == $playerTwo) {
                $playerOne = $faker->randomElement($users);
                $playerTwo = $faker->randomElement($users);
            }


            $dateStart = $faker->dateTime();
            $dateEnd = $faker->dateTime();
            /*while () {
                $dateStart = $faker->dateTime();
                $dateEnd = $faker->dateTime();
            }*/

            $random = mt_rand(0, 2);

            if ($random == 1) {
                $game->setWinner($playerOne);
            } elseif ($random == 2) {
                $game->setWinner($playerTwo);
            }

            $game->setCreatedAt($dateStart)
                ->setStatus(0)
                ->setEndedAt($dateEnd)
                ->setField("{}")
                ->setPlayerOne($playerOne)
                ->setPlayerTwo($playerTwo)
            ;

            $manager->persist($game);


        }



        $manager->flush();
    }
}
