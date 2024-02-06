<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * This controller allow us to edit user's profile
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @param int $id
     * @param Request $request
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        UserRepository $repository,
        EntityManagerInterface $manager,
        int $id,
        Request $request
    ): Response
    {
        $user = $repository->findOneBy(["id" => $id]);
      //  dd($user, $this->getUser());
        if(!$this->getUser())
        {
            return $this->redirectToRoute('security.login');

        }
        if($this->getUser() !== $user)
        {
            return $this->redirectToRoute('recipe.index');

        }
        $form = $this->createForm(UserType::class,$user);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            $manager -> persist($user);
            $manager -> flush();

            $this->addFlash(
                'success',
                'Les informations de votre compte ont été bien modifiées');
                return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /** This controller allow us to edit user's password
      * @param UserRepository $repository,
      * @param EntityManagerInterface $manager
      * @param int $id
      * @param Request $request
      * @param UserPasswordHasherInterface $hasher
      * @return Response
      */
    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(
        UserRepository $repository,
        EntityManagerInterface $manager,
        int $id,
        Request $request ,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $user = $repository->findOneBy(["id" => $id]);
      
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
           
               /* $user->setPassword(
                   $hasher->hashPassword(
                    $user,
                    $form->getData()['newPassword']
                   )
                );*/
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setPlainPassword(
                    $form->getData()['newPassword']
                );

                $this->addFlash(
                    "success",
                    "Le mot de passe a été modifié " 
                );
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
