<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\CommentCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private string $sender,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $post = $comment->getPost();

        if (null === $post || null === $post->getAuthor()) {
            return;
        }

        $linkToPost = $this->urlGenerator->generate(
            'blog_post',
            ['slug' => $post->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = sprintf('New comment on "%s"', $post->getTitle());

        $body = <<<EMAIL_BODY
            A new comment has been posted on your post "{$post->getTitle()}".

            Author: {$comment->getAuthor()?->getFullName()}
            Content: {$comment->getContent()}

            View the post: {$linkToPost}
            EMAIL_BODY;

        $email = (new Email())
            ->from($this->sender)
            ->to($post->getAuthor()->getEmail() ?? '')
            ->subject($subject)
            ->text($body);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }
    }
}
