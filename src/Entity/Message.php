<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: "App\Repository\MessageRepository")]
#[ORM\Table(indexes: [new ORM\Index(name: "created_at_index", columns: ["created_at"])])]
#[ORM\HasLifecycleCallbacks()]
class Message
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column(type: "text")]
    
    private $content;

    
    #[ORM\ManyToOne(targetEntity: "User", inversedBy: "messages")]
    
    private $user;

    
    #[ORM\ManyToOne(targetEntity: "Conversation", inversedBy: "messages")]
    
    private $conversation;

    private $mine;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }
        /**
     * @return mixed
     */
    public function getMine()
    {
        return $this->mine;
    }
    /**
     * @param mixed $mine
     */
    public function setMine($mine): void
    {
        $this->mine = $mine;
    }

}


