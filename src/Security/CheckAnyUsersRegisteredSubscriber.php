<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class CheckAnyUsersRegisteredSubscriber implements EventSubscriberInterface
{
    /** @var RouterInterface */
    private $router;

    /** @var UserRepository */
    private $userRepository;

    /** @var FirewallMap */
    private $firewallMap;

    public function __construct(
        RouterInterface $router,
        UserRepository $userRepository,
        FirewallMap $firewallMap
    ) {
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->firewallMap = $firewallMap;
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ['checkUsers']
        ];
    }

    public function checkUsers(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {

            $firewallConfig = $this->firewallMap->getFirewallConfig($event->getRequest());
            if (null === $firewallConfig) {
                return;
            }

            // TODO: Does doing this count with every request slow things down? We
            // could cache it, but it's only a quick DB query and we're unlikely to
            // have lots of users any time soon!
            if (
                $event->getRequest()->get('_route') !== 'setup' &&
                $firewallConfig->getName() !== 'dev' && // Allow developer toolbar, etc.
                $this->userRepository->getUserCount() === 0
            ) {
                // There are no users yet. Redirect to the setup page.
                $response = new RedirectResponse($this->router->generate('setup'));
                $event->setResponse($response);
            }
        }
    }
}
