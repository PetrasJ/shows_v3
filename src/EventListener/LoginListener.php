<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginListener
{
    private $em;
    private $translator;
    private $router;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, RequestContextAwareInterface $router)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();
        if ($user->getLocale()) {
            $this->translator->setLocale($user->getLocale());
            $this->router->getContext()->setParameter('_locale', $user->getLocale());
            $request->getSession()->set('_locale', $user->getLocale());
            $request->setLocale($user->getLocale());
        }
    }
}
