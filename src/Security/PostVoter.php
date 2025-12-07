<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for controlling access to Post entities.
 *
 * Only post authors can edit or delete their own posts.
 * All posts are publicly viewable.
 *
 * @extends Voter<string, Post>
 */
final class PostVoter extends Voter
{
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const SHOW = 'show';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Post
            && \in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Anonymous users can only view posts
        if (!$user instanceof User) {
            return $attribute === self::SHOW;
        }

        // SHOW: all authenticated users can view
        if ($attribute === self::SHOW) {
            return true;
        }

        // EDIT and DELETE: only the post author
        return $user === $subject->getAuthor();
    }
}
