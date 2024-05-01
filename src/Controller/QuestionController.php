<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
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
            $question->setAuthor($user);
            $question->setCreatedAt(new \DateTimeImmutable()); //par défaut on utilise le dateTime
            //on vient utiliser entity manager pour ajouter les deux autres champs renseigner par l'user
            //on utilise persist() pour sauvegarder les données saisi
            //equivaut a preparer la requete
            $em->persist($question);
            //on execute la requete
            $em->flush();
            //ajout du message flash 
            $this->addFlash('success', "Votre question a bien été ajoutée");
            //on redirige l'user sur la page d'accueil une fois le formulaire soumis
            return $this->redirectToRoute('home');
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }

    #[Route(path: "/question/{id}", name:"question_show")]
    public function show(Request $request, QuestionRepository $questionRepo, EntityManagerInterface $em, int $id): Response
    {

        $question = $questionRepo->getQuestionWithCommentAndAuthors($id);


        $options = [
            'question' => $question
        ];
        
        $user = $this->getUser();

        if($user){
                //on vient créer le commentaire
            $comment = new Comment();
            // on vient lier la classe du formulaire, et la classe entity
            $commentForm = $this->createForm(CommentType::class, $comment);
            //on récupère la requête
            $commentForm->handleRequest($request);

            if($commentForm->isSubmitted() && $commentForm->isValid()){
                //on ajoute les valeurs par défaut
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setRating(0);
                //on set directement la ref à la question
                $comment->setQuestion($question);
                $comment->setAuthor($user);
                //on vient mettre à jour le nombre de response à une question
                $question->setNumberOfResponse($question->getNumberOfResponse() +1);
                //on persiste la requete avec l'entity manager
                $em->persist($comment);
                $em->flush();
                //on vient afficher un message flash
                $this->addFlash('success', "Réponse ajoutée avec succès");
                //on refresh la page avec uri
                return $this->redirect($request->getUri());
            }
            $ptions['form'] = $commentForm->createView();
        }
       
        return $this->render('question/show.html.twig', $options);
    }

    #[Route('/question/rating/{id}/{score}', name: 'question_rating')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ratingQuestion(Question $question, int $score, EntityManagerInterface $em, Request $request, VoteRepository $voteRepo)
    {
        $user = $this->getUser();

        if($user !== $question->getAuthor()){
            $vote = $voteRepo->findOneBy([
                'author'=>$user,
                'question'=>$question
            ]);
            if($vote){
                if(($vote->isLiked() && $score > 0) || (!$vote->isLiked() && $score < 0)){
                    $em->remove($vote);
                    $question->setRating($question->getRating() + ($score > 0 ? -1 : 1));
                }else{
                    $vote->setLiked($vote->isLiked());
                    $question->setRating($question->getRating() + ($score > 0 ? 2 : -2));
                }
            }else{
                $vote = new Vote();
                $vote->setAuthor($user);
                $vote->setQuestion($question);
                $vote->setLiked($score > 0 ? true : false );
                $question->setRating($question->getRating() + $score);
                $em->persist($vote);
            }
            $em->flush();
        }

        $referer = $request->server->get('HTTP_REFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }

    #[Route('/comment/rating/{id}/{score}', name: 'comment_rating')]
    #[IsGranted('REMEMBERED')]
    public function ratingComment(Comment $comment, int $score, EntityManagerInterface $em, Request $request, VoteRepository $voteRepo)
    {
        $user = $this->getUser();
        if($user !== $comment->getAuthor()){
            $vote = $voteRepo->findOneBy([
                'author'=>$user,
                'comment'=>$comment
            ]);
            if($vote){
                if(($vote->isLiked() && $score > 0) || (!$vote->isLiked() && $score < 0)){
                    $em->remove($vote);
                    $comment->setRating($comment->getRating() + ($score > 0 ? -1 : 1));
                }else{
                    $vote->setLiked($vote->isLiked());
                    $comment->setRating($comment->getRating() + ($score > 0 ? 2 : -2));
                }
            }else{
                $vote = new Vote();
                $vote->setAuthor($user);
                $vote->setComment($comment);
                $vote->setLiked($score > 0 ? true : false );
                $comment->setRating($comment->getRating() + $score);
                $em->persist($vote);
            }
            $em->flush();
        }
        $referer = $request->server->get('HTTP_REFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }


    #[Route('/question/search/{search}', name: 'question_search', priority: 1)]
    public function questionSearch(string $search = "none", QuestionRepository $questionRepo)
    {
        if($search === "none"){
            $questions = [];
        }else{
            $questions = $questionRepo->findBySearch($search);
        }
        return $this->json(json_encode($questions));
    }
}
