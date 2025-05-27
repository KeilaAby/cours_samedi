<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentForm;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/student')]
final class StudentController extends AbstractController
{
    #[Route(name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
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
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentForm::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageprofile')->getData(); //Récupération des images multiples
            $imageNames = []; //Initialisation d'un tableau pour stocker les noms des images

            if ($imageFile) {
                
                foreach($imageFile as $images) { //Parcourir les images multiples

                    $orignaleFileName = pathInfo($images->getClientOriginalName(), PATHINFO_FILENAME); //Obtenion du nom original du fichier
                    $safeFileName = $slugger->slug($orignaleFileName); //Formatage du nom de fichier pour le rendre sûr, exemple : "Mon Image.jpg" devient "mon-image"
                    $newFileName = $safeFileName . '-' . uniqid() . '.' . $images->guessExtension(); // Création d'un nom de fichier unique
                    $images->move($this->getParameter('student_image_profile'), $newFileName); // Déplacement du fichier vers le répertoire de destination
                    $imageNames[] = $newFileName; // Ajout du nom de fichier au tableau
                    //et la boucle recommence selon le nombre de l'image
                }
                $student->setImageProfile($imageNames); // Enregistrement du tableau des noms d'images dans l'entité Student
            }else{
                $imageNames[]="avatar.png"; // Si aucune image n'est fournie, on utilise une image par défaut
            }

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
