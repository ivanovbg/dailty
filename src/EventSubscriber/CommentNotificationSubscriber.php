<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\EventSubscriber;
use App\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
/**
 * Notifies post's author about new comments.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
class CommentNotificationSubscriber implements EventSubscriberInterface
{

    public function __construct()
    {

    }
    public static function getSubscribedEvents(): array
    {
        return [
            Events::COMMENT_CREATED => 'onCommentCreated',
        ];
    }
    public function onCommentCreated(GenericEvent $event)
    {
       mail("krasimir.petrov@prospectogroup.com", "test", "test");
    }
}