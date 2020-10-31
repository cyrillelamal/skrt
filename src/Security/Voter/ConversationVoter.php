<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ConversationVoter extends Voter
{
    public const SUPPORTED_ACTIONS = [
        'CREATE',
        'SHOW',
        'UPDATE',
        'DELETE',
    ];

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, self::SUPPORTED_ACTIONS)
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$attribute instanceof Conversation) {
            throw new LogicException("ConversationPreview voter votes only on \"ConversationPreview\" entities.");
        }

        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'POST_EDIT':
                return $this->canShow($user, $attribute);
        }

        return false;
    }

    protected function canShow(User $user, Conversation $conversation): bool
    {
        /** @var User[] $participants */
        $participants = $conversation->getParticipants()->toArray();

        foreach ($participants as $participant) {
            if ($participant->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
