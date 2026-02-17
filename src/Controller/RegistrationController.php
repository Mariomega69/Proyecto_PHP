<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new Usuario();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encriptamos la contraseña
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Por defecto, Symfony suele asignar ROLE_USER,
            // pero nos aseguramos de que el usuario tenga roles
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // 1. Autenticamos al usuario en el firewall 'main'
            // IMPORTANTE: Se eliminan los argumentos extra que causaban el TypeError
            $security->login($user, LoginFormAuthenticator::class, 'main');

            // 2. Lógica de redirección según el Rol
            // Si es ADMIN, lo mandamos al panel de EasyAdmin
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin');
            }

            // Para usuarios normales, los mandamos al catálogo de motos
            // (Usamos 'app_moto' porque 'app_user_dashboard' podría no estar definida aún)
            $this->addFlash('success', '¡Registro completado con éxito! Bienvenido.');
            return $this->redirectToRoute('app_moto');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
