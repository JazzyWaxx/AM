<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="playerOneGames")
     * @ORM\JoinColumn(nullable=false)
     */
    private $playerOne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="playerTwoGames")
     * @ORM\JoinColumn(nullable=true)
     */
    private $playerTwo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="winnerGames")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $winner;

    /**
     * @ORM\Column(type="json_array")
     */
    private $playerOneField;

    /**
     * @ORM\Column(type="json_array")
     */
    private $playerTwoField;

    /**
     * @ORM\Column(type="json_array")
     */
    private $dice;

    /**
     * @ORM\Column(type="json_array")
     */
    private $chat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPlayerOne(): ?User
    {
        return $this->playerOne;
    }

    public function setPlayerOne(?User $playerOne): self
    {
        $this->playerOne = $playerOne;

        return $this;
    }

    public function getPlayerTwo(): ?User
    {
        return $this->playerTwo;
    }

    public function setPlayerTwo(?User $playerTwo): self
    {
        $this->playerTwo = $playerTwo;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getPlayerOneField()
    {
        return $this->playerOneField;
    }

    public function setPlayerOneField($playerOneField): self
    {
        $this->playerOneField = $playerOneField;

        return $this;
    }

    public function getPlayerTwoField()
    {
        return $this->playerTwoField;
    }

    public function setPlayerTwoField($playerTwoField): self
    {
        $this->playerTwoField = $playerTwoField;

        return $this;
    }

    public function getDice()
    {
        return $this->dice;
    }

    public function setDice($dice): self
    {
        $this->dice = $dice;

        return $this;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function setChat($chat): self
    {
        $this->chat = $chat;

        return $this;
    }
}
