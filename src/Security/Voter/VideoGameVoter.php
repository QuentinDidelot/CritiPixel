<?php

namespace App\Security\Voter;

use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


/**
 * @extends \App\Security\Voter
 */
class VideoGameVoter extends Voter
{
    public const REVIEW = 'review';

    /**
     * Déclare les types génériques : TAttribute et TSubject
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut est REVIEW et que le sujet est une instance de VideoGame
        return $attribute === self::REVIEW && $subject instanceof VideoGame;
    }

    /**
     * Vérifie si l'utilisateur a le droit d'effectuer l'action sur le sujet
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return !$subject->hasAlreadyReview($user);
    }
}
