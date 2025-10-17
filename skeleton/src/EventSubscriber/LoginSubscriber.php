<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        if (!$session) {
            return;
        }
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if (is_object($user)) {
            $session->set('user.id', $user->getId());
            $session->set('user.email', $user->getEmail());
            $session->set('user.name', $user->getName());
            $session->set('user.roles', $user->getRoles()[0] ?? 'ROLE_USER');            
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        if (!$session) {
            return;
        }
        $session->remove('user.id');
        $session->remove('user.email');
        $session->remove('user.name');
        $session->remove('user.roles');
    }
}
