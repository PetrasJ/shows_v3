<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly RequestContextAwareInterface $requestContextAwareInterface,
    ) {
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();
        if ($user->getLocale()) {
            $this->translator->setLocale($user->getLocale());
            $this->requestContextAwareInterface->getContext()->setParameter('_locale', $user->getLocale());
            $request->getSession()->set('_locale', $user->getLocale());
            $request->setLocale($user->getLocale());
        }

        if ($user->getEmailConfirmationToken()) {
            $request->getSession()->getFlashBag()->add('danger', 'email_is_not_confirmed');
        }
    }
}
