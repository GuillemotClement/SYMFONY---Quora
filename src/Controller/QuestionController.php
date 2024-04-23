<?php

namespace App\Controller;

use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request): Response
    {
        // On vient récupérer la classe qui gère le formulaire
        $formQuestion = $this->createForm(QuestionType::class);

        //on récupère les données de la requête
        $formQuestion->handleRequest($request);

        //si le formulaire est soumis et valid
        if($formQuestion->isSubmitted() && $formQuestion->isValid()){
            //on affiche les données de la requête
            dump($formQuestion->getData());
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }
}
