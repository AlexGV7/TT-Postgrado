<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professor $professor = null;
    private ?Professor $professor2 = null;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'offer')]
    private Collection $applications;

    /**
     * @var Collection<int, Area>
     */
    #[ORM\ManyToMany(targetEntity: Area::class, inversedBy: 'offers')]
    private Collection $areas;

    /**
     * @var Collection<int, Keyword>
     */
    #[ORM\ManyToMany(targetEntity: Keyword::class)]
    private Collection $keywords;

    /**
     * @var Collection<int, ResearchLine>
     */
    #[ORM\ManyToMany(targetEntity: ResearchLine::class)]
    private Collection $researchLines;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->researchLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getProfessor(): ?Professor
    {
        return $this->professor;
    }

    public function setProfessor(?Professor $professor): static
    {
        $this->professor = $professor;

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setOffer($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getOffer() === $this) {
                $application->setOffer(null);
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
