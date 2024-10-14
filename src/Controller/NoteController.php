<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\NoteType;
use App\Entity\Note;
use DateTimeImmutable;
use DateTimeZone;

class NoteController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #show all notes in a list

    #[Route('/', name: 'app_note')]
    public function index(): Response
    {
        $notes = $this->em->getRepository(Note::class)->findAll();
        return $this->render('note/index.html.twig', ['notes'=>$notes]);
    }

    #create post methode

    #[Route('/create_note', name:'create_note')]
    public function createNote(Request $request): Response
    {
        #create a new note
        $note = new Note();
        $date = new DateTimeImmutable();
        $note->setCreatedAt($date);
        $form = $this->createForm(NoteType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #add note to our bd
            $this->em->persist($note);
            $this->em->flush();

            #show that note is added to our bd

            $this->addFlash('message', 'Note inserted successfully!');
            return $this->redirectToRoute('app_note');
        }
        return $this->render('note/create.html.twig', ['form'=>$form->createView()]);

    }

    #Modify a note
    #[Route('/update_note/{id}', name:'update_note')]
    public function updateNote(Request $request, $id)
    {
        #find note with her id
        $note = $this->em->getRepository(Note::class)->find($id);
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #update database
            $this->em->persist($note);
            $this->em->flush();

            $this->addFlash('message', 'Note updated successfully');
            return $this->redirectToRoute('app_note');
        }
        return $this->render('note/create.html.twig', ['form'=>$form->createView()]);
    }

    #delete a note
    #[Route('delete_note/{id}', name:'delete_note')]
    public function deleteNote($id)
    {
        #find note by id
        $note = $this->em->getRepository(Note::class)->find($id);
        #remove note
        $this->em->remove($note);
        $this->em->flush();
        #print message that delete was successful
        $this->addFlash('message', 'Note deleted successfully');
        return $this->redirectToRoute('app_note');
    }
}
