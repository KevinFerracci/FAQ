<?php

namespace App\Security\Voter;

use App\Entity\Question;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class QuestionVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['edit', 'validateAnswer'])
            && $subject instanceof Question;
    }

    protected function voteOnAttribute($attribute, $question, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'edit':
                if ($user == $question->getUser()) {
                    return true;
                }
                if (in_array($user->getRole()->getRoleString(), ['ROLE_ADMIN', 'ROLE_MODERATOR'])) {
                    return true;
                }
                break;
            case 'validateAnswer':
                if ($user == $question->getUser()) {
                    return true;
                }
                break;
        }
        return false;
    }
}
