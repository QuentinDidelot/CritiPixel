<?php

namespace App\Security\Voter;

use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VideoGameVoter extends Voter
{
    public const REVIEW = 'review';

    /**
     * La classe VideoGameVoter étend Voter, et nous devons spécifier les types génériques ici
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut est REVIEW et que le sujet est une instance de VideoGame
        return $attribute === self::REVIEW && $subject instanceof VideoGame;
    }

    /**
     * Cette méthode vérifie si un utilisateur peut voter sur un attribut spécifique du sujet
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
