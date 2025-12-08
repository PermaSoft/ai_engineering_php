<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\CommentCreatedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
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

        // Generate URL to post
        $postUrl = $this->urlGenerator->generate(
            'blog_post',
            ['slug' => $post->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Use translator for email subject
        $subject = $this->translator->trans('notification.comment_created', [
            'postTitle' => $post->getTitle(),
        ]);

        $email = (new TemplatedEmail())
            ->from(Address::create($this->sender))
            ->to(new Address($post->getAuthor()->getEmail()))
            ->subject($subject)
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->context([
                'post' => $post,
                'comment' => $comment,
                'postUrl' => $postUrl,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error but don't fail the request
        }
    }
}
