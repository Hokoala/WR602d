<?php

namespace App\Entity;

use App\Repository\PdfQueueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PdfQueueRepository::class)]
class PdfQueue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private string $token = '';

    #[ORM\Column(type: 'json')]
    private array $inputFiles = [];

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resultFile = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): static { $this->token = $token; return $this; }

    public function getInputFiles(): array { return $this->inputFiles; }
    public function setInputFiles(array $inputFiles): static { $this->inputFiles = $inputFiles; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getResultFile(): ?string { return $this->resultFile; }
    public function setResultFile(?string $resultFile): static { $this->resultFile = $resultFile; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getProcessedAt(): ?\DateTimeImmutable { return $this->processedAt; }
    public function setProcessedAt(?\DateTimeImmutable $processedAt): static { $this->processedAt = $processedAt; return $this; }
}
