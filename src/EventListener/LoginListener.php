<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginListener
{
    private TranslatorInterface $translator;
    private RequestContextAwareInterface $requestContextAwareInterface;
    private RouterInterface $router;
    private SessionInterface $session;

    public function __construct(
        TranslatorInterface $translator,
        RequestContextAwareInterface $requestContextAwareInterface,
        RouterInterface $router,
        SessionInterface $session
    ) {
        $this->translator = $translator;
        $this->requestContextAwareInterface = $requestContextAwareInterface;
        $this->router = $router;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();
        if ($user->getLocale()) {
            $this->translator->setLocale($user->getLocale());
            $this->requestContextAwareInterface->getContext()->setParameter('_locale', $user->getLocale());
            $request->getSession()->set('_locale', $user->getLocale());
            $request->setLocale($user->getLocale());
        }

        if ($user->getEmailConfirmationToken()) {
            $this->session->getFlashBag()->add('danger', 'email_is_not_confirmed');
        }
    }
}
