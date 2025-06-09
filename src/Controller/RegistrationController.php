<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setToken(uniqid()); //Génération d'un token
            $entityManager->persist($user);
            $entityManager->flush();


            // generate a signed url and email it to the user
            // $email = (new TemplatedEmail())
            //     ->from(new Address('noreply@christian.com', 'Noreply'))
            //     ->to((string) $user->getEmail())
            //     ->subject('Activation de votre compte')
            //     ->htmlTemplate('registration/confirmation_email.html.twig')
            //     ->context([
            //         // 'resetToken' => $resetToken,
            //         'user' => $user
            //     ]);

            // $mailer->send($email);

            // $this->addFlash('success', 'Votre compte a été crée, n\'oubliez pas de le confirmer depuis votre mail');

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('seorefchristian@gmail.com', 'Symfony_Christian'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                   
            );

            //do anything else you need here, like send an email


            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
          
            /** @var User $user */
            // $user = new User();
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }

    
    //Route qui permettra de valider le token
    #[Route('/activation/{token}', name: 'app_activation', methods: ['GET', 'POST'])]
    public function activationcompte(string $token, UserRepository $userrepo, EntityManagerInterface $entitymanager): Response
    {
        $user = $userrepo->findOneBy(['token' => $token]);
        if ($user) {
            $user->setToken(null);
            $user->setIsVerified(true);
            $entitymanager->flush();
            $this->addFlash('success', 'Votre compte a été activé, vous pouvez vous connecter');
        }
        else {
            $this->addFlash('error', 'Un problème est survenue');
            
        }
        return $this->redirectToRoute(('app_login'));
    }
}
