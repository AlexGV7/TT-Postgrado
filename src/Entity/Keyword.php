<?php

namespace App\Entity;

use App\Repository\KeywordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KeywordRepository::class)]
class Keyword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $word = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @var Collection<int, Professor>
     */
    #[ORM\ManyToMany(targetEntity: Professor::class, mappedBy: 'keywords')]
    private Collection $professors;

    public function __construct()
    {
        $this->professors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): static
    {
        $this->word = $word;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Professor>
     */
    public function getProfessors(): Collection
    {
        return $this->professors;
    }

    public function addProfessor(Professor $professor): static
    {
        if (!$this->professors->contains($professor)) {
            $this->professors->add($professor);
            $professor->addKeyword($this);
        }

        return $this;
    }

    public function removeProfessor(Professor $professor): static
    {
        if ($this->professors->removeElement($professor)) {
            $professor->removeKeyword($this);
        }

        return $this;
    }
}