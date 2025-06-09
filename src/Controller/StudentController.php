<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\User;
use App\Form\StudentForm;
use App\Repository\StudentRepository;
use App\Security\Voter\StudentVoter;
use App\Service\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;




#[Route('/student')]
final class StudentController extends AbstractController
{
    public function __construct(){
        $user = new USer;
    }
    #[Route(name: 'app_student_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(StudentRepository $studentRepository): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findAll(),
        ]);
    }

    //Recuperation de l'Image de profile
    #[Route('/student/profile/image/{profileName}', name: 'student_profile_image')]
    public function studentProfileImage(string $profileName): BinaryFileResponse
    {

        $filePath = $this->getParameter('student_image_profile') . DIRECTORY_SEPARATOR . $profileName;
        return new BinaryFileResponse($filePath);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    #[IsGranted(StudentVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, UploadFile $uploadfile): Response
    {
        // $uploadfile->uploadFileMultiple();
        $student = new Student();
        $form = $this->createForm(StudentForm::class, $student);
        $errors = $form->getErrors(true, false); // Récupération des erreurs de validation du formulaire
        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            // encode the plain password
            $student->setPassword($userPasswordHasher->hashPassword($student, $plainPassword));
            
            //Traitements de l'Upload Image
            $imageFile = $form->get('imageprofile')->getData(); //Récupération des images multiples
            $target = 'student_image_profile';
            $uploadfile->uploadFileMultiple($imageFile, $student, $this, $target);


            $entityManager->persist($student);
            $entityManager->flush();

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student): Response
    {
        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[isGranted(StudentVoter::EDIT, subject:'student')]
    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentForm::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
