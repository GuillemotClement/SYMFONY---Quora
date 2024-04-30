<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends AbstractController
{

    public function __construct(
        private FormLoginAuthenticator $authenticator
    ) {
    }



    #[Route('/signup', name: 'signup')]
    public function signup(UserAuthenticatorInterface $userAuthenticator, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()){
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Bienvenue sur Quora');
            $email = new TemplatedEmail();
            $email->to($user->getEmail());
            $email->subject('Bienvenue sur Quora');
            $email->htmlTemplate('@email_templates/welcome.html.twig');
            $email->context([
                'username'=> $user->getFirstname()
            ]);
            $mailer->send($email);
            // inscription et redirect vers acceuil 
            return $userAuthenticator->authenticateUser($user, $this->authenticator, $request);
        }

        return $this->render('security/signup.html.twig', [
            'form' => $userForm->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenUtils): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('home');
        }

        $error = $authenUtils->getLastAuthenticationError();
        $username = $authenUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'username'=>$username
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }

    #[Route('reset-password-request', name: 'reset-password-request')]
    public function resetPasswordRequest(Request $request, UserRepository $userRepo)
    {
        $emailForm = $this->createFormBuilder()->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner votre email'
                ])
            ]
        ])->getForm();

        // Traiter le formulaire
        $emailForm->handleRequest($request);
        if($emailForm->isSubmitted() && $emailForm->isValid()){
            $email = $emailForm->get('email')->getData();
            $user = $userRepo->findOneBy(['email' => $email]);
            if($user){
                //si l'user existe, alors on créer el token de reset
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                //on set l'expiration à 2h après now
                $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
                //génération du token
                $token = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(30))), 0, 20);
                $resetPassword->setToken($token);
            }
            $this->addFlash('success', 'Un email à été envoyer pour rénitialiser le mot de passe');
        }
        return $this->render('security/reset-password-request.html.twig', [
            'form' => $emailForm->createView()
        ]);
    }
}
