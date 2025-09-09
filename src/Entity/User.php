<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RUT', fields: ['rut'])]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $deactivatedNotifications = null;

    #[ORM\Column]
    private ?bool $sex = null;

    #[ORM\Column]
    private ?int $rut = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $lastName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRolesText(): string
    {

        $texts = $this->getRolesTextArray();

        return implode(', ', $texts);
    }

    public function getRolesTextArray(): ?array
    {
        $roleTexts = [
            'ROLE_ADMIN' => 'Administrador',
            'ROLE_STUDENT' => 'Estudiante',
            'ROLE_PROFESSOR' => 'Profesor',
            'ROLE_OTHER' => 'Otro',
        ];

        $texts = [];
        foreach ($this->roles as $role) {
            if (isset($roleTexts[$role])) {
                $texts[] = $roleTexts[$role];
            }
        }

        return $texts;
    }

    

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isDeactivatedNotifications(): ?bool
    {
        return $this->deactivatedNotifications;
    }

    public function setDeactivatedNotifications(bool $deactivatedNotifications): static
    {
        $this->deactivatedNotifications = $deactivatedNotifications;

        return $this;
    }

    public function isSex(): ?bool
    {
        return $this->sex;
    }

    public function getSexChar()
    {
        if($this->sex)
            return 'M';
        return 'F';
    }

    public function getSexText()
    {
        if($this->sex)
            return 'Masculino';
        return 'Femenino';
    }

    public function setSex(bool $sex): static
    {
        $this->sex = $sex;

        return $this;
    }

    public function setSexFromSiding($sex)
    {
        if($sex == 'M')
            $this->sex = 1;
        else
            $this->sex = 0;

        return $this;
    }

    public function getRut(): ?int
    {
        return $this->rut;
    }

    public function setRut(int $rut): static
    {
        $this->rut = $rut;

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFullName(): ?string
    {
        $name = $this->name;
        $lastName = $this->lastName;
        if ($name && $lastName) {
            return $name . ' ' . $lastName;
        } elseif ($name) {
            return $name;
        } elseif ($lastName) {
            return $lastName;
        }
        return null;
    }
}
