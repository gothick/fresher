<?php

namespace App\Security;

use App\Entity\GoalReminder;
use App\Entity\Goal;
use App\Entity\Theme;
use App\Entity\User;
use LogicException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GoalReminderVoter extends Voter
{
    // One-size-fits-all for now
    const ACCESS = 'access';
    protected function supports(string $attribute, $subject): bool
    {
        if ($attribute !== self::ACCESS) {
            return false;
        }
        if (!$subject instanceof GoalReminder) {
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
            /** @var GoalReminder $reminder */ // Guaranteed by supports()
            $reminder = $subject;
            $goal = $reminder->getGoal();
            if ($goal === null) {
                throw new LogicException('Expected every GoalReminder to have a Goal');
            }
            $theme = $goal->getTheme();
            if ($theme === null) {
                throw new LogicException('Expected every goal to have a Theme');
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
