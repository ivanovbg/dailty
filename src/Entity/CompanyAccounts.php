<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyAccountsRepository")
 */
class CompanyAccounts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="companyAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="companyAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $staff_access;

    /**
     * @ORM\Column(type="boolean")
     */
    private $staff_manage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $service_access;

    /**
     * @ORM\Column(type="boolean")
     */
    private $service_manage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $client_access;

    /**
     * @ORM\Column(type="boolean")
     */
    private $client_manage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main_access;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $possition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $working_time;

    /**
     * @ORM\Column(type="boolean")
     */
    private $event_access;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getStaffAccess(): ?bool
    {
        return $this->staff_access;
    }

    public function setStaffAccess(bool $staff_access): self
    {
        $this->staff_access = $staff_access;

        return $this;
    }

    public function getStaffManage(): ?bool
    {
        return $this->staff_manage;
    }

    public function setStaffManage(bool $staff_manage): self
    {
        $this->staff_manage = $staff_manage;

        return $this;
    }

    public function getServiceAccess(): ?bool
    {
        return $this->service_access;
    }

    public function setServiceAccess(bool $service_access): self
    {
        $this->service_access = $service_access;

        return $this;
    }

    public function getServiceManage(): ?bool
    {
        return $this->service_manage;
    }

    public function setServiceManage(bool $service_manage): self
    {
        $this->service_manage = $service_manage;

        return $this;
    }

    public function getClientAccess(): ?bool
    {
        return $this->client_access;
    }

    public function setClientAccess(bool $client_access): self
    {
        $this->client_access = $client_access;

        return $this;
    }

    public function getClientManage(): ?bool
    {
        return $this->client_manage;
    }

    public function setClientManage(bool $client_manage): self
    {
        $this->client_manage = $client_manage;

        return $this;
    }

    public function getMainAccess(): ?bool
    {
        return $this->main_access;
    }

    public function setMainAccess(bool $main_access): self
    {
        $this->main_access = $main_access;

        return $this;
    }

    public function getPossition(): ?string
    {
        return $this->possition;
    }

    public function setPossition(?string $possition): self
    {
        $this->possition = $possition;

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


    public function getEventAccess()
    {
        return $this->event_access;
    }


    public function setEventAccess($event_access): void
    {
        $this->event_access = $event_access;
    }
}
