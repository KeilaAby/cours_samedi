<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class UploadFile {

    public function __construct(private SluggerInterface $slugger){}

    public function uploadFileMultiple(mixed $imageFile, mixed $subject, mixed $subjectController, string $pathFolder)
    {
        $imageNames = []; //Initialisation d'un tableau pour stocker les noms des images
        if ($imageFile) {

                foreach ($imageFile as $images) { //Parcourir les images multiples

                    $orignaleFileName = pathInfo($images->getClientOriginalName(), PATHINFO_FILENAME); //Obtenion du nom original du fichier
                    $safeFileName = $this->slugger->slug($orignaleFileName); //Formatage du nom de fichier pour le rendre sûr, exemple : "Mon Image.jpg" devient "mon-image"
                    $newFileName = $safeFileName . '-' . uniqid() . '.' . $images->guessExtension(); // Création d'un nom de fichier unique
                    $images->move($subjectController->getParameter($pathFolder), $newFileName); // Déplacement du fichier vers le répertoire de destination
                    $imageNames[] = $newFileName; // Ajout du nom de fichier au tableau
                    //et la boucle recommence selon le nombre de l'image
                }
                $subject->setImageProfile($imageNames); // Enregistrement du tableau des noms d'images dans l'entité Student
            } else {
                $imageNames[] = "avatar.png"; // Si aucune image n'est fournie, on utilise une image par défaut
            }


    }

}