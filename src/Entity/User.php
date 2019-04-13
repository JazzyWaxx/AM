<?php

namespace App\Entity;

use App\Entity\Game;
use App\Repository\FriendRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Email déjà utilisé"
 * )
 */
class User implements UserInterface
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
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Votre mot de passe doit être identique")
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $picture;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="playerOne")
     */
    private $playerOneGames;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="playerTwo")
     */
    private $playerTwoGames;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="winner")
     */
    private $winnerGames;

    /**
     * @Recaptcha\IsTrue
     */
    public $recaptcha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPassword;

    public function getResetPassword(): ?string
    {
        return $this->resetPassword;
    }

    public function setResetPassword(string $resetPassword): self
    {
        $this->resetPassword = $resetPassword;

        return $this;
    }

    /**
     * @var string le token qui servira lors de l'oubli de mot de passe
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $resetToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="sender", orphanRemoval=true)
     */
    private $getSenderEmails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="recipient", orphanRemoval=true)
     */
    private $getRecipientEmails;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friend", mappedBy="sender")
     */
    private $getSenderFriends;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Friend", mappedBy="recipient")
     */
    private $getRecipientFriends;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Log", mappedBy="user")
     */
    private $getLogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="sender")
     */
    private $senderMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="recipient")
     */
    private $recipientMessages;

    /**
     * @return string
     */
    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    /**
     * @param string $resetToken
     */
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function __construct()
    {
        $this->playerOneGames = new ArrayCollection();
        $this->playerTwoGames = new ArrayCollection();
        $this->winnerGames = new ArrayCollection();
        $this->getSenderEmails = new ArrayCollection();
        $this->getRecipientEmails = new ArrayCollection();
        $this->getSenderFriends = new ArrayCollection();
        $this->getRecipientFriends = new ArrayCollection();
        $this->getLogs = new ArrayCollection();
        $this->senderMessages = new ArrayCollection();
        $this->recipientMessages = new ArrayCollection();
    }

    public function getUserFriends($userRepository, $friendRepository, $logRepository) {
        $friends = $friendRepository->getFriends($this->getId());
        $friendsArray = array();
        foreach ($friends as $friend) {
            if ($friend->getSender()->getId() == $this->getId()) {
                $friendId = $friend->getRecipient()->getId();
            } else {
                $friendId = $friend->getSender()->getId();
            }
            $friendObject = $userRepository->findOneBy(['id' => $friendId]);
            $lastConnected = $logRepository->lastConnected($friendObject->getId());
            if (!empty($lastConnected)) {
                $lastConnected = $lastConnected[0]->getLoggedAt();
                $timeDiff = $lastConnected->diff(New \DateTime());
                $days = $timeDiff->format("%d");
                $hours = $timeDiff->format("%h");
                $minutes = $timeDiff->format("%i");
                $timeDiffText = "";
                if ($days !== "0") {
                    $timeDiffText .= $days."j ";
                } else {
                    if ($hours !== "0") {
                        $timeDiffText .= $hours."h ";
                        if ($minutes !== "0") {
                            $timeDiffText .= $minutes."m ";
                        }
                    } else {
                        if ($minutes !== "0") {
                            $timeDiffText .= $minutes."m ";
                        }
                    }
                }
                $lastConnected = $timeDiffText;
            }
            $friendsArray[] = array(
                'id' => $friendId,
                'username' => $friendObject->getUsername(),
                'lastConnected' => $lastConnected
            );
        }
        $friends = $friendsArray;
        return $friends;
    }

    public function getFriendRequestsReceived($friendRepository) {
        $friendRequestsReceived = $friendRepository->getFriendRequestsReceived($this->getId());
        $friendRequestsReceivedArray = array();
        foreach ($friendRequestsReceived as $friendRequestReceived) {
            $friendRequestsReceivedArray[] = array(
                'id' => $friendRequestReceived->getSender()->getId(),
                'username' => $friendRequestReceived->getSender()->getUsername()
            );
        }
        $friendRequestsReceived = $friendRequestsReceivedArray;
        return $friendRequestsReceived;
    }

    public function getFriendRequestsSent($friendRepository) {
        $friendRequestsSent = $friendRepository->getFriendRequestsSent($this->getId());
        $friendRequestsSentArray = array();
        foreach ($friendRequestsSent as $friendRequestSent) {
            $friendRequestsSentArray[] = array(
                'id' => $friendRequestSent->getRecipient()->getId(),
                'username' => $friendRequestSent->getRecipient()->getUsername()
            );
        }
        $friendRequestsSent = $friendRequestsSentArray;
        return $friendRequestsSent;
    }

    public function getUserSuggestions($userRepository, $friendRepository, $logRepository) {
        $myRequests = $this->getFriendRequestsSent($friendRepository);
        $myFriends = $this->getUserFriends($userRepository, $friendRepository, $logRepository);
        $myArray = array_merge($myFriends, $myRequests);
        $myIdArray = array();
        $myIdArray[] = $this->getId();
        foreach ($myArray as $array) {
            $myIdArray[] = $array["id"];
        }
        $suggestions = $userRepository->suggestions();
        $mySuggestionsArray = array();
        foreach ($suggestions as $suggestion) {
            if (!in_array($suggestion->getId(), $myIdArray) && $suggestion->getRoles()[0] == "ROLE_USER" && $suggestion->getStatus() !== 3) {
                $mySuggestionsArray[] = $suggestion;
            }
        }
        shuffle($mySuggestionsArray);
        $suggestions = array_slice($mySuggestionsArray, 0, 20);
        return $suggestions;
    }

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getSalt()
    {
    }

    /**
     * @see UserInterface
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function __toString(){
        // to show the name of the Category in the select
        //return $this->username;
    }

    /**
     * @return Collection|Game[]
     */
    public function getPlayerOneGames(): Collection
    {
        return $this->playerOneGames;
    }

    public function addPlayerOneGame(Game $playerOneGame): self
    {
        if (!$this->playerOneGames->contains($playerOneGame)) {
            $this->playerOneGames[] = $playerOneGame;
            $playerOneGame->setPlayerOne($this);
        }

        return $this;
    }

    public function removePlayerOneGame(Game $playerOneGame): self
    {
        if ($this->playerOneGames->contains($playerOneGame)) {
            $this->playerOneGames->removeElement($playerOneGame);
            // set the owning side to null (unless already changed)
            if ($playerOneGame->getPlayerOne() === $this) {
                $playerOneGame->setPlayerOne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getPlayerTwoGames(): Collection
    {
        return $this->playerTwoGames;
    }

    public function addPlayerTwoGame(Game $playerTwoGame): self
    {
        if (!$this->playerTwoGames->contains($playerTwoGame)) {
            $this->playerTwoGames[] = $playerTwoGame;
            $playerTwoGame->setPlayerTwo($this);
        }

        return $this;
    }

    public function removePlayerTwoGame(Game $playerTwoGame): self
    {
        if ($this->playerTwoGames->contains($playerTwoGame)) {
            $this->playerTwoGames->removeElement($playerTwoGame);
            // set the owning side to null (unless already changed)
            if ($playerTwoGame->getPlayerTwo() === $this) {
                $playerTwoGame->setPlayerTwo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Game[]
     */
    public function getWinnerGames(): Collection
    {
        return $this->winnerGames;
    }

    public function addWinnerGame(Game $winnerGame): self
    {
        if (!$this->winnerGames->contains($winnerGame)) {
            $this->winnerGames[] = $winnerGame;
            $winnerGame->setWinner($this);
        }

        return $this;
    }

    public function removeWinnerGame(Game $winnerGame): self
    {
        if ($this->winnerGames->contains($winnerGame)) {
            $this->winnerGames->removeElement($winnerGame);
            // set the owning side to null (unless already changed)
            if ($winnerGame->getWinner() === $this) {
                $winnerGame->setWinner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getGetSenderEmails(): Collection
    {
        return $this->getSenderEmails;
    }

    public function addGetSenderEmail(Email $getSenderEmail): self
    {
        if (!$this->getSenderEmails->contains($getSenderEmail)) {
            $this->getSenderEmails[] = $getSenderEmail;
            $getSenderEmail->setSender($this);
        }

        return $this;
    }

    public function removeGetSenderEmail(Email $getSenderEmail): self
    {
        if ($this->getSenderEmails->contains($getSenderEmail)) {
            $this->getSenderEmails->removeElement($getSenderEmail);
            // set the owning side to null (unless already changed)
            if ($getSenderEmail->getSender() === $this) {
                $getSenderEmail->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getGetRecipientEmails(): Collection
    {
        return $this->getRecipientEmails;
    }

    public function addGetRecipientEmail(Email $getRecipientEmail): self
    {
        if (!$this->getRecipientEmails->contains($getRecipientEmail)) {
            $this->getRecipientEmails[] = $getRecipientEmail;
            $getRecipientEmail->setRecipient($this);
        }

        return $this;
    }

    public function removeGetRecipientEmail(Email $getRecipientEmail): self
    {
        if ($this->getRecipientEmails->contains($getRecipientEmail)) {
            $this->getRecipientEmails->removeElement($getRecipientEmail);
            // set the owning side to null (unless already changed)
            if ($getRecipientEmail->getRecipient() === $this) {
                $getRecipientEmail->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friend[]
     */
    public function getGetSenderFriends(): Collection
    {
        return $this->getSenderFriends;
    }

    public function addGetSenderFriend(Friend $getSenderFriend): self
    {
        if (!$this->getSenderFriends->contains($getSenderFriend)) {
            $this->getSenderFriends[] = $getSenderFriend;
            $getSenderFriend->setSender($this);
        }

        return $this;
    }

    public function removeGetSenderFriend(Friend $getSenderFriend): self
    {
        if ($this->getSenderFriends->contains($getSenderFriend)) {
            $this->getSenderFriends->removeElement($getSenderFriend);
            // set the owning side to null (unless already changed)
            if ($getSenderFriend->getSender() === $this) {
                $getSenderFriend->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friend[]
     */
    public function getGetRecipientFriends(): Collection
    {
        return $this->getRecipientFriends;
    }

    public function addGetRecipientFriend(Friend $getRecipientFriend): self
    {
        if (!$this->getRecipientFriends->contains($getRecipientFriend)) {
            $this->getRecipientFriends[] = $getRecipientFriend;
            $getRecipientFriend->setRecipient($this);
        }

        return $this;
    }

    public function removeGetRecipientFriend(Friend $getRecipientFriend): self
    {
        if ($this->getRecipientFriends->contains($getRecipientFriend)) {
            $this->getRecipientFriends->removeElement($getRecipientFriend);
            // set the owning side to null (unless already changed)
            if ($getRecipientFriend->getRecipient() === $this) {
                $getRecipientFriend->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Log[]
     */
    public function getGetLogs(): Collection
    {
        return $this->getLogs;
    }

    public function addGetLog(Log $getLog): self
    {
        if (!$this->getLogs->contains($getLog)) {
            $this->getLogs[] = $getLog;
            $getLog->setUser($this);
        }

        return $this;
    }

    public function removeGetLog(Log $getLog): self
    {
        if ($this->getLogs->contains($getLog)) {
            $this->getLogs->removeElement($getLog);
            // set the owning side to null (unless already changed)
            if ($getLog->getUser() === $this) {
                $getLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSenderMessages(): Collection
    {
        return $this->senderMessages;
    }

    public function addSenderMessage(Message $senderMessage): self
    {
        if (!$this->senderMessages->contains($senderMessage)) {
            $this->senderMessages[] = $senderMessage;
            $senderMessage->setSender($this);
        }

        return $this;
    }

    public function removeSenderMessage(Message $senderMessage): self
    {
        if ($this->senderMessages->contains($senderMessage)) {
            $this->senderMessages->removeElement($senderMessage);
            // set the owning side to null (unless already changed)
            if ($senderMessage->getSender() === $this) {
                $senderMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getRecipientMessages(): Collection
    {
        return $this->recipientMessages;
    }

    public function addRecipientMessage(Message $recipientMessage): self
    {
        if (!$this->recipientMessages->contains($recipientMessage)) {
            $this->recipientMessages[] = $recipientMessage;
            $recipientMessage->setRecipient($this);
        }

        return $this;
    }

    public function removeRecipientMessage(Message $recipientMessage): self
    {
        if ($this->recipientMessages->contains($recipientMessage)) {
            $this->recipientMessages->removeElement($recipientMessage);
            // set the owning side to null (unless already changed)
            if ($recipientMessage->getRecipient() === $this) {
                $recipientMessage->setRecipient(null);
            }
        }

        return $this;
    }

}
