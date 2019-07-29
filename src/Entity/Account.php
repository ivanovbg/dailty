<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="accounts")
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_main;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Plan", inversedBy="accounts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $plan;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_register;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyAccounts", mappedBy="account")
     */
    private $companyAccounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Request", mappedBy="account")
     */
    private $requests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="provider")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="account")
     */
    private $notes;

    public function __construct()
    {
        $this->companyAccounts = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->notes = new ArrayCollection();
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIsMain(): ?bool
    {
        return $this->is_main;
    }

    public function setIsMain(bool $is_main): self
    {
        $this->is_main = $is_main;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getDateRegister(): ?\DateTimeInterface
    {
        return $this->date_register;
    }

    public function setDateRegister(\DateTimeInterface $date_register): self
    {
        $this->date_register = $date_register;

        return $this;
    }

    /**
     * @return Collection|CompanyAccounts[]
     */
    public function getCompanyAccounts(): Collection
    {
        return $this->companyAccounts;
    }

    public function addCompanyAccount(CompanyAccounts $companyAccount): self
    {
        if (!$this->companyAccounts->contains($companyAccount)) {
            $this->companyAccounts[] = $companyAccount;
            $companyAccount->setAccount($this);
        }

        return $this;
    }

    public function removeCompanyAccount(CompanyAccounts $companyAccount): self
    {
        if ($this->companyAccounts->contains($companyAccount)) {
            $this->companyAccounts->removeElement($companyAccount);
            // set the owning side to null (unless already changed)
            if ($companyAccount->getAccount() === $this) {
                $companyAccount->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Request[]
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests[] = $request;
            $request->setAccount($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->requests->contains($request)) {
            $this->requests->removeElement($request);
            // set the owning side to null (unless already changed)
            if ($request->getAccount() === $this) {
                $request->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setProvider($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getProvider() === $this) {
                $event->setProvider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setAccount($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getAccount() === $this) {
                $note->setAccount(null);
            }
        }

        return $this;
    }
}

