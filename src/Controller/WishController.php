<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use App\Services\Censurator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wish", name="wish_")
 */
class WishController extends AbstractController
{
    /**
     * @Route("", name="list")
     */
    public function list(WishRepository $wishRepository): Response
    {
        //$liste = $wishRepository->findBy(['isPublished'=>true], ['dateCreated'=>'DESC']);

        // récupère les Wish publiés, du plus récent au plus ancien
        //$wishes = $wishRepository->findBy(['isPublished' => true], ['dateCreated' => 'DESC']);

        // on appelle une méthode personnalisée ici pour éviter d'avoir trop de requêtes.
        $listes = $wishRepository->findPublishedWishesWithCategories();

        return $this->render('wish/list.html.twig', [
            "liste" => $listes
        ]);
    }

    /**
     * @Route("/detail/{id}", name="detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, WishRepository $wishRepository): Response
    {
        $detail = $wishRepository->find($id);
        if (!$detail){
            throw $this->createNotFoundException('This wish does not exist ! Sorry');
        }

        return $this->render('wish/detail.html.twig', [
            "detail" => $detail
        ]);
    }

    /**
     * @Route("/add-wish", name="add_wish")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, Censurator $censurator): Response
    {
        //Création d'entité vide
        $wish = new Wish();
        $currentUserUsername = $this->getUser()->getUserIdentifier();
        $wish->setAuthor($currentUserUsername);

        $wish->setDateCreated(new \DateTime());

        //Création d'un formulaire, associé à l'entité vide
        $wishForm = $this->createForm(WishType::class, $wish);

        //Récupère les données du form et les injecte dans notre $wish
        $wishForm->handleRequest($request);

        //Si le formulaire est soumis et valide
        if($wishForm->isSubmitted() && $wishForm->isValid()){
            //Hydrate les propriétés absentes du formulaires
            $wish->setIsPublished(true);
            //$wish->setDescription($censurator->purify($wish->getDescription()));

            //Sauvegarde en BDD
            $entityManager->persist($wish);
            $entityManager->flush();

            //Affiche un message flash sur la page suivante
            $this->addFlash('success', 'Idée ajoutée avec succès !');

            //Redirige vers la page de détails de l'idée crée
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        //Affiche le formulaire
        return $this->render('wish/add_wish.html.twig', [
            'wishForm' => $wishForm->createView()
        ]);
    }
}
