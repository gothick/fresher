<?php

namespace App\Security;

use App\Entity\User;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['checkPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        // Symfony says this is deprecated, but it's what its own sodding
        // CheckCredentialsListener uses and I can't find any good documentation
        // on what to do instead.
        if (!$passport instanceof UserPassportInterface) {
            throw new Exception("Expected passport to bear a user");
        }
        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new Exception("Expected to get our own User class back.");
        }
        if (!$user->isVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if ($event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            $response = new RedirectResponse($this->router->generate('app_verify_resend_email'));
            $event->setResponse($response);
        }
    }
}
