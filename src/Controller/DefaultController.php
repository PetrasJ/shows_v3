<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @param Request     $request
     * @param Mailer $mailer
     * @Route("/feedback", name="feedback")
     * @return Response
     */
    public function feedback(Request $request, Mailer $mailer)
    {
        $feedback = new Feedback();

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mailer->sendFeedback($feedback);
            return $this->render('feedback/sent.html.twig');
        }

        return $this->render('feedback/index.html.twig', ['form' => $form->createView()]);
    }
}
