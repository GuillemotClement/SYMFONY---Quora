<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function userProfil(User $user): Response
    {
        $currentUser = $this->getUser();
        if($currentUser === $user){
            return $this->redirectToRoute('current_user');
        }
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user', name: 'current_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function currentUserProfile(Uploader $uploader, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHash, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->remove('password');
        $userForm->add('newPassword', PasswordType::class, ['label' => 'Nouveau mot de passe', 'required' => false]);
        $userForm->handleRequest($request);
        
        if($userForm->isSubmitted() && $userForm->isValid()){
            $newPassword = $user->getNewPassword();
            if($newPassword){
                $hash = $passwordHash->hashPassword($user, $newPassword);
                $user->setPassword($hash);
            }
            $picture = $userForm->get('pictureFile')->getData();
            if($picture){
                $user->setPicture($uploader->uploadProfileImage($picture, $user->getPicture()));
            }
            $em->flush();
            $this->addFlash('success', 'Modification(s) sauvegardée(s)');
        }

        return $this->render('user/index.html.twig', [
            'form' => $userForm->createView()
        ]);
    }
}
