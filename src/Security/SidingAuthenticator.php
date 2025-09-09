<?php
namespace App\Security;

use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Service\Siding;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class SidingAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $siding;
    private $router;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, Siding $siding, RouterInterface $router, UserPasswordHasher $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->siding = $siding;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function authenticate(Request $request): Passport
    {
        $user_repository = $this->entityManager->getRepository(User::class);

        $password = $request->get('_password');
        $login = $request->get('_username');
        
        $is_email = strpos($login, '@') !== false;

        if ($is_email) {
            $username = substr($login, 0, strpos($login, '@'));
        } else {
            $username = $login;
        }

        $authenticated_rut = $this->siding->authenticate($username, $password);
        $is_rut_in_db = !is_null($user_repository->findOneByRut($authenticated_rut));

        if ($authenticated_rut > 0) {

            if (!$is_rut_in_db) {
                $data = $this->siding->getUserData($authenticated_rut);
                $user_repository->createFromSidingData($data, $this->entityManager);
            }

            if ($is_email) {
                $user = $user_repository->findOneByEmail($login);
            } else {
                $user = $user_repository->findOneByRut($authenticated_rut);
            }

            if (!is_null($user)) {
                $email = $user->getEmail();
                return new SelfValidatingPassport(new UserBadge($email));
            } else {
                throw new CustomUserMessageAuthenticationException('Los datos del usuario no son válidos');
            }
        } else {
            throw new CustomUserMessageAuthenticationException('Los datos del usuario no son válidos');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($target = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($target);
        }
        return new RedirectResponse(
            $this->router->generate('homepage')
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate("login");
    }
}
