<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $yes = null;

    /**
     * @var Collection<int, Professor>
     */
    #[ORM\OneToMany(targetEntity: Professor::class, mappedBy: 'department')]
    private Collection $professors;

    public function __construct()
    {
        $this->professors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getYes(): ?string
    {
        return $this->yes;
    }

    public function setYes(string $yes): static
    {
        $this->yes = $yes;

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
            $professor->setDepartment($this);
        }

        return $this;
    }

    public function removeProfessor(Professor $professor): static
    {
        if ($this->professors->removeElement($professor)) {
            // set the owning side to null (unless already changed)
            if ($professor->getDepartment() === $this) {
                $professor->setDepartment(null);
            }
        }

        return $this;
    }
}
