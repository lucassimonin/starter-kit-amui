<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:create-admin',
    description: 'Crée un compte administrateur pour le back-office.',
)]
final class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $users,
        private UserPasswordHasherInterface $hasher,
        private ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $io->ask('E-mail', null, function (?string $value): string {
            if (!$value || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('E-mail invalide.');
            }

            return strtolower(trim($value));
        });

        if ($this->users->findOneBy(['email' => $email])) {
            $io->error("Un utilisateur avec cet e-mail existe déjà : {$email}");

            return Command::FAILURE;
        }

        $fullName = (string) $io->ask('Nom complet (optionnel)', '');
        $password = (string) $io->askHidden('Mot de passe (min 8 caractères)', function (?string $value): string {
            if (!$value || strlen($value) < 8) {
                throw new \RuntimeException('Mot de passe trop court (8 min).');
            }

            return $value;
        });

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_ADMIN']);

        $user->setPassword($this->hasher->hashPassword($user, $password));

        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            foreach ($violations as $v) {
                $io->error((string) $v->getMessage());
            }

            return Command::FAILURE;
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success("Admin créé : {$email}");

        return Command::SUCCESS;
    }
}
