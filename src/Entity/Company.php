<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="companies")
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_added;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Service", mappedBy="company")
     */
    private $services;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Client", mappedBy="company")
     */
    private $clients;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyAccounts", mappedBy="company")
     */
    private $companyAccounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="company")
     */
    private $events;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $working_time;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->companyAccounts = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded(\DateTimeInterface $date_added): self
    {
        $this->date_added = $date_added;

        return $this;
    }

    /**
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setCompany($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
            // set the owning side to null (unless already changed)
            if ($service->getCompany() === $this) {
                $service->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setCompany($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
            // set the owning side to null (unless already changed)
            if ($client->getCompany() === $this) {
                $client->setCompany(null);
            }
        }

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
            $companyAccount->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyAccount(CompanyAccounts $companyAccount): self
    {
        if ($this->companyAccounts->contains($companyAccount)) {
            $this->companyAccounts->removeElement($companyAccount);
            // set the owning side to null (unless already changed)
            if ($companyAccount->getCompany() === $this) {
                $companyAccount->setCompany(null);
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
            $event->setCompany($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getCompany() === $this) {
                $event->setCompany(null);
            }
        }

        return $this;
    }

    public function getWorkingTime(): ?string
    {
        return $this->working_time;
    }


    public function setWorkingTime(?string $working_time): self
    {
        $this->working_time = $working_time;

        return $this;
    }
}
