<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentApiController extends AbstractController
{
    #[Route('/student/api', name: 'app_student_api_index', methods:'GET')]
    public function index(StudentRepository $studentRepo): Response
    {
        $students = $studentRepo->findAll();
        return $this->json($students);
    }

    #[Route('student/api/{id}', name: 'app_student_api_show', methods:'GET', requirements:['id' => '\d+'])]
    public function show(int $id, StudentRepository $studentRepo): Response
    {
        $student = $studentRepo->find($id);
        if (!$student) {
            return $this->json(['message' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($student);
    }

    //Genère la partie delete
    #[Route('student/api/delete/{id}', name: 'app_student_api_delete', methods:'DELETE', requirements:['id' => '\d+'])]
    public function delete(int $id, StudentRepository $studentRepo): Response
    {
        $student = $studentRepo->find($id);
        if (!$student) {
            return $this->json(['message' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }
        $studentRepo->remove($student, true);
        return $this->json(['message' => 'Student deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    //Genère la partie update
    #[Route('student/api/update/{id}', name: 'app_student_api_update', methods:'PUT', requirements:['id' => '\d+'])]
    public function update(int $id, StudentRepository $studentRepo): Response
    {
        $student = $studentRepo->find($id);
        if (!$student) {
            return $this->json(['message' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }
        // Logique de mise à jour ici
        // Par exemple, vous pouvez modifier certaines propriétés de l'entité Student
        // $student->setName('New Name');
        // $studentRepo->save($student, true);
        
        return $this->json(['message' => 'Student updated successfully'], Response::HTTP_OK);
    }

//genères new à partir d'un donnée Json 
    #[Route('student/api/new', name: 'app_student_api_new', methods:'GET')]
    public function new(StudentRepository $studentRepo, Request $request): Response
    {
        // Logique pour créer un nouvel étudiant à partir des données JSON
        // Par exemple, vous pouvez récupérer les données du corps de la requête et les utiliser pour créer une nouvelle entité Student
        $data = json_decode($request->getContent(), true);
        
        // Check if JSON is valid
        if ($data === null) {
            return $this->json([
                'error' => 'Invalid JSON data'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate required fields
        if (!isset($data['name']) || !isset($data['prenom'])) {
            return $this->json([
                'error' => 'Missing required fields: name and prenom'
            ], Response::HTTP_BAD_REQUEST);
        }

        $student = new Student();
        $student->setName($data['name']);
        $student->setPrenom($data['prenom']);
        
        $studentRepo->save($student, true);
        
        return $this->json([
            'message' => 'New student created successfully',
            'student' => [
                'id' => $student->getId(),
                'name' => $student->getName(),
                'prenom' => $student->getPrenom()
            ]
        ], Response::HTTP_CREATED);
    }

}
