<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        //on commence par créer la question
        $question = new Question();
        // On vient récupérer la classe qui gère le formulaire et on le lie à l'entity
        $formQuestion = $this->createForm(QuestionType::class, $question);
        //on récupère les données de la requête
        $formQuestion->handleRequest($request);
        //si le formulaire est soumis et valid
        if($formQuestion->isSubmitted() && $formQuestion->isValid()){
            //on passe les valeur par défaut
            $question->setNumberOfResponse(0); //par défaut il y a 0 réponse
            $question->setRating(0); //par défaut la note est à 0
            $question->setCreatedAt(new \DateTimeImmutable()); //par défaut on utilise le dateTime
            //on vient utiliser entity manager pour ajouter les deux autres champs renseigner par l'user
            //on utilise persist() pour sauvegarder les données saisi
            //equivaut a preparer la requete
            $em->persist($question);
            //on execute la requete
            $em->flush();
            //on redirige l'user sur la page d'accueil une fois le formulaire soumis
            return $this->redirectToRoute('home');
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }

    #[Route(
        path: "/question/{id}", name:"question_show"
    )]
    public function show(Request $request, string $id): Response
    {
        $question = [
            'name' => "Ma question",
            'content' => "blablalbla"
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question
        ]);
    }
}
