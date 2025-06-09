<?php

namespace App\Security\Voter;

use App\Entity\Student;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class StudentVoter extends Voter
{
    public const EDIT = 'STUDENT_EDIT';
    public const VIEW = 'STUDENT_VIEW';
    public const CREATE = 'STUDENT_CREATE';

    public function __construct(private readonly Security $security)
    {
        
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return (in_array($attribute, [self::CREATE]) ||
            in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Student
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // if (!$subject instanceof User) {
        //     return false;
        // }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                return $subject->getId() === $user->getId();
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
            case self::CREATE:
                return $this->security->isGranted('ROLE_ADMIN');
                break;
        }

        return false;
    }
}
