<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="requests")
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 */
class Request
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="requests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $request_from;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $request_key;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_expired;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getRequestFrom(): ?Account
    {
        return $this->request_from;
    }

    public function setRequestFrom(?Account $request_from): self
    {
        $this->request_from = $request_from;

        return $this;
    }

    public function getRequestKey(): ?string
    {
        return $this->request_key;
    }

    public function setRequestKey(string $request_key): self
    {
        $this->request_key = $request_key;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateExpired(): ?\DateTimeInterface
    {
        return $this->date_expired;
    }

    public function setDateExpired(\DateTimeInterface $date_expired): self
    {
        $this->date_expired = $date_expired;

        return $this;
    }
}
