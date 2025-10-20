<?php

namespace App\Entity;

use App\Repository\ProfessorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessorRepository::class)]
class Professor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $image = null;

    #[ORM\ManyToOne(inversedBy: 'professors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    /**
     * @var Collection<int, Offer>
     */
    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'professor')]
    private Collection $offers;

    /**
     * @var Collection<int, Area>
     */
    #[ORM\ManyToMany(targetEntity: Area::class)]
    private Collection $areas;

    /**
     * @var Collection<int, Keyword>
     */
    #[ORM\ManyToMany(targetEntity: Keyword::class, inversedBy: 'professors')]
    private Collection $keywords;

    /**
     * @var Collection<int, ResearchLine>
     */
    #[ORM\ManyToMany(targetEntity: ResearchLine::class)]
    private Collection $researchLines;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->researchLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
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

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setProfessor($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getProfessor() === $this) {
                $offer->setProfessor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    public function addArea(Area $area): static
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
        }

        return $this;
    }

    public function removeArea(Area $area): static
    {
        $this->areas->removeElement($area);

        return $this;
    }

    /**
     * @return Collection<int, Keyword>
     */
    public function getKeywords(): Collection
    {
        return $this->keywords;
    }

    public function addKeyword(Keyword $keyword): static
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
        }

        return $this;
    }

    public function removeKeyword(Keyword $keyword): static
    {
        $this->keywords->removeElement($keyword);

        return $this;
    }

    /**
     * @return Collection<int, ResearchLine>
     */
    public function getResearchLines(): Collection
    {
        return $this->researchLines;
    }

    public function addResearchLine(ResearchLine $researchLine): static
    {
        if (!$this->researchLines->contains($researchLine)) {
            $this->researchLines->add($researchLine);
        }

        return $this;
    }

    public function removeResearchLine(ResearchLine $researchLine): static
    {
        $this->researchLines->removeElement($researchLine);

        return $this;
    }
}
