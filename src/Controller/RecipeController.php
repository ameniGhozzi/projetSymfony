<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use App\Form\RecipeType;


class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
 
    #[Route('/recette', name: 'recipe.index' , methods:['Get'])]  
    public function index(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]), 
            $request->query->getInt('page', 1),
            10 
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' =>$recipes
        ]);
    }

     /**
     * This controller show a form which create a new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */

     #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'] )]
     public function  new(Request $request, EntityManagerInterface $manager) : Response
     {
         $recipe = new Recipe();
         $form =  $this->createForm(RecipeType::class, $recipe);
 
         $form ->handleRequest($request);
         if($form->isSubmitted() && $form->isValid()) {
           // dd($form->getData());
          $recipe = $form->getData();
          $recipe-> setUser($this->getUser());
          $manager->persist($recipe);
          $manager->flush();
          $this->addFlash(
             'success',
             'Votre recette a été créé avec succès !'
         );
         return $this->redirectToRoute('recipe.index');
         }
 
         return $this->render('pages/recipe/new.html.twig', [
             'form' =>$form->createView()
         ]);
     }


    /**
     * This controller allow us to edit a recipe
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
  
    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(
        RecipeRepository $repository,
        Request $request,
        EntityManagerInterface $manager,
        int $id
    ): Response {
    
        $recipe= $repository->findOneBy(["id" => $id]);
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        } 

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us to delete a recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @param int $id
     * @return Response
     */

    #[Route('/recette/suppression/{id}', 'recipe.delete' , methods: ['get'])]
    public function delete(
        RecipeRepository $repository,
        EntityManagerInterface $manager,
        int $id
        ): Response
    {
       $recipe = $repository->findOneBy(["id" => $id]);
       if(!$recipe){
        $this->addFlash(
            'success',
            'Le recipe en question n\'a pas été trouvé !'
        );
        return $this->redirectToRoute('recipe.index');
       } 
       $manager->remove($recipe);
       $manager->flush();
       $this->addFlash(
        'success',
        'Votre recette a été supprimé avec succès !'
    );

    return $this->redirectToRoute('recipe.index');

    }


      /**
     * This controller allow us to show a recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @param int $id
     * @return Response
     */

     #[Route('/recette/show/{id}', 'recipe.show' , methods: ['get'])]
     public function show(
         RecipeRepository $repository,
         int $id
         ): Response
        {
        $recipe= $repository->findOneBy(["id" => $id]);
    
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
 
     }


      /**
     * This controller allow us to show a recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @param int $id
     * @return Response
     */

     #[Route('/recette/publique', 'recipe.index.public' , methods: ['get'])]
     public function indexPublic(
         RecipeRepository $repository,
         PaginatorInterface $paginator,
         Request $request
         ): Response
     {
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request -> query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
 
     }
}
