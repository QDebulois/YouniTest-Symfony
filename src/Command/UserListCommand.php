<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:list',
    description: 'Liste des utilisateurs',
)]
class UserListCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $u) {
            $output->writeln(
                "id:".$u->getId()
                ." email:".$u->getEmail()
                ." roles:".json_encode($u->getRoles(), true)
                ." crée_le:".$u->getCreatedAt()->format("d-m-Y H:m:i")
                ." nbr_article:".count($u->getPosts())
                ." nbr_categorie:".count($u->getCategories())
            );
        }

        $io->success('Il y a '.count($users).' utilisateurs dans la BDD.');

        return Command::SUCCESS;
    }
}
