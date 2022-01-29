<?php

namespace App\Security;
use App\Entity\Helper;
use App\Entity\User;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class HelperVoter extends Voter
{
    // One-size-fits-all for now
    const ACCESS = 'access';
    protected function supports(string $attribute, $subject): bool
    {
        if ($attribute !== self::ACCESS) {
            return false;
        }
        if (!$subject instanceof Helper) {
            return false;
        }
        return true;
    }
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // Well, being logged in at all would be a good start
            return false;
        }
        if ($attribute === self::ACCESS) {
            /** @var Helper $helper  */ // Guaranteed by supports()
            $helper = $subject;
            if ($helper->getOwner() === $user) {
                return true;
            } else {
                return false;
            }
        }
        throw new LogicException('This code should never be reached.');
    }
}
