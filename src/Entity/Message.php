<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="messages")
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_reply;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_read;

    /**
     * @ORM\Column(type="integer")
     */
    private $sender_delete;

    /**
     * @ORM\Column(type="integer")
     */
    private $receiver_delete;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_send;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceiver(): ?Account
    {
        return $this->receiver;
    }

    public function setReceiver(?Account $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getSender(): ?Account
    {
        return $this->sender;
    }

    public function setSender(?Account $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsReply()
    {
        return $this->is_reply;
    }

    /**
     * @param mixed $is_reply
     */
    public function setIsReply($is_reply): void
    {
        $this->is_reply = $is_reply;
    }

    /**
     * @return mixed
     */
    public function getIsRead()
    {
        return $this->is_read;
    }

    /**
     * @param mixed $is_read
     */
    public function setIsRead($is_read): void
    {
        $this->is_read = $is_read;
    }

    /**
     * @return mixed
     */
    public function getSenderDelete()
    {
        return $this->sender_delete;
    }

    /**
     * @param mixed $sender_delete
     */
    public function setSenderDelete($sender_delete): void
    {
        $this->sender_delete = $sender_delete;
    }

    /**
     * @return mixed
     */
    public function getReceiverDelete()
    {
        return $this->receiver_delete;
    }

    /**
     * @param mixed $receiver_delete
     */
    public function setReceiverDelete($receiver_delete): void
    {
        $this->receiver_delete = $receiver_delete;
    }



    public function getDateSend(): ?\DateTimeInterface
    {
        return $this->date_send;
    }

    public function setDateSend(\DateTimeInterface $date_send): self
    {
        $this->date_send = $date_send;

        return $this;
    }
}
