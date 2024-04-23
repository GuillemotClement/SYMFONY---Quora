<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $questions = [[
            'id'=> '1',
            "title"=> "Comment résoudre les erreurs 404 dans un site web ?",
            "content" => "Expliquez les différentes approches pour gérer les erreurs 404 et fournissez des exemples de code",
            "rating" => 14,
            "author" => [
                'name' => "WebDevExpert",
                "avatar" =>"https://randomuser.me/api/portraits/men/18.jpg",
            ],
            "nbrOfResponse" => 10,
        ],
        [
            'id'=> '2',
            "title"=> "Quelles sont les bonnes pratiques pour optimiser les performances d'un site web ?",
            "content" => "Décrivez les techniques telles que la minification, la compression Gzip, le chargement asynchrone des ressources, etc.",
            "rating" => 4,
            "author" => [
                'name' => "PerfNinja",
                "avatar" =>"https://randomuser.me/api/portraits/men/39.jpg",
            ],
            "nbrOfResponse" => 10,
        ],
        [
            'id'=> '3',
            "title"=> "Quels sont les principes fondamentaux du responsive design ?",
            "content" => "Discutez des media queries, des grilles flexbox/grid, de la gestion des images et des vidéos, etc.",
            "rating" => 0,
            "author" => [
                'name' => "DesignGuru",
                "avatar" =>"https://randomuser.me/api/portraits/women/55.jpg",
            ],
            "nbrOfResponse" => 10,
        ],
        [
            'id'=> '4',
            "title"=> "Comment implémenter un système d'authentification sécurisé avec PHP et MySQL ?",
            "content" => "Détaillez les étapes pour créer un système d'inscription, de connexion et de gestion des sessions sécurisées.",
            "rating" => -2,
            "author" => [
                'name' => "SecureCoder",
                "avatar" =>"https://randomuser.me/api/portraits/women/70.jpg",
            ],
            "nbrOfResponse" => 10,
        ],
        [
            'id'=> '5',
            "title"=> "Quelles sont les étapes pour déployer un site web sur un serveur AWS ?",
            "content" => "Expliquez comment configurer un serveur EC2, installer Apache/NGINX, configurer les bases de données, etc.",
            "rating" => 14,
            "author" => [
                'name' => "CloudDevOps",
                "avatar" =>"https://randomuser.me/api/portraits/women/74.jpg",
            ],
            "nbrOfResponse" => 10,
        ]
    ];

        return $this->render('home/index.html.twig', [
            'questions' => $questions
        ]);
    }
}
