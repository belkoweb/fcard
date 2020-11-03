<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *      "email",
 *      message="Hum, ce mail est déjà pris !"
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
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     * @Assert\Email(message = "Le mail renseigné n'est pas un mail valide")
     * @Assert\Length(
     *      max = 180,
     *      maxMessage = "Hum, votre mail est bien trop long !"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 10, 
     *      max = 180,
     *      minMessage = "Votre mot de passe doit faire plus de 10 caractères",
     *      maxMessage = "Outch ! Votre mot de passe doit être inférieur à 180 caractères"
     * )
     */
    private $password;

    /**
     * @Assert\NotBlank(
     *      allowNull = "true"
     * )
     * @Assert\EqualTo(
     *      propertyPath = "password",
     *      message = "Veuillez confirmer le même mot de passe"
     * )
     */
    private $confirm_password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(
     *      max = 180,
     *      maxMessage = "Outch ! Votre pseudo doit être inférieur à 180 caractères"
     * )
     */
    private $pseudo;

    /**
     * @ORM\Column(type="integer")
     */
    private $dailyLimit = 50;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="user", orphanRemoval=true)
     */
    private $cards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tag", mappedBy="user", orphanRemoval=true)
     */
    private $tags;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\LastConnection", mappedBy="user", cascade={"persist", "remove"})
     */
    private $lastConnection;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\DailyCount", mappedBy="user", cascade={"persist", "remove"})
     */
    private $dailyCount;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    // check for ['ROLE_ADMIN'], etc.
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    public function setDailyLimit(int $dailyLimit): self
    {
        $this->dailyLimit = $dailyLimit;

        return $this;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards[] = $card;
            $card->setUser($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->cards->contains($card)) {
            $this->cards->removeElement($card);
            // set the owning side to null (unless already changed)
            if ($card->getUser() === $this) {
                $card->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->setUser($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            // set the owning side to null (unless already changed)
            if ($tag->getUser() === $this) {
                $tag->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of confirm_password
     */ 
    public function getConfirmPassword()
    {
        return $this->confirm_password;
    }

    /**
     * Set the value of confirm_password
     *
     * @return  self
     */ 
    public function setConfirmPassword($confirm_password)
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }

    public function __toString()
    {
        return $this->pseudo;
    }

    public function getLastConnection(): ?LastConnection
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?LastConnection $lastConnection): self
    {
        $this->lastConnection = $lastConnection;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $lastConnection === null ? null : $this;
        if ($newUser !== $lastConnection->getUser()) {
            $lastConnection->setUser($newUser);
        }

        return $this;
    }

    public function getDailyCount(): ?DailyCount
    {
        return $this->dailyCount;
    }

    public function setDailyCount(?DailyCount $dailyCount): self
    {
        $this->dailyCount = $dailyCount;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $dailyCount === null ? null : $this;
        if ($newUser !== $dailyCount->getUser()) {
            $dailyCount->setUser($newUser);
        }

        return $this;
    }
}
