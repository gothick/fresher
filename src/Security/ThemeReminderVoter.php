<?php

namespace App\Security;

use App\Entity\ThemeReminder;
use App\Entity\Goal;
use App\Entity\Theme;
use App\Entity\User;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ThemeReminderVoter extends Voter
{
    // One-size-fits-all for now
    const ACCESS = 'access';
    protected function supports(string $attribute, $subject): bool
    {
        if ($attribute !== self::ACCESS) {
            return false;
        }
        if (!$subject instanceof ThemeReminder) {
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
            /** @var ThemeReminder $reminder */ // Guaranteed by supports()
            $reminder = $subject;
            $theme = $reminder->getTheme();
            if ($theme === null) {
                throw new LogicException('Expected every ThemeReminder to have a Theme');
            }
            if ($theme->getOwner() === $user) {
                return true;
            } else {
                return false;
            }
        }
        throw new LogicException('This code should never be reached.');
    }
}
