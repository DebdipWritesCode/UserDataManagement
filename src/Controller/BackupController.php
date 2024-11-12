<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/backup', name: 'app_backup', methods: ['POST'])]
    public function backup(Request $request): Response
    {
        $username = $request->request->get('username');
        
        if (!$username) {
            return $this->json(['error' => 'Username is required'], Response::HTTP_BAD_REQUEST);
        }

        $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        
        if (!$adminUser || $adminUser->getRole() !== 'ADMIN') {
            return $this->json(['error' => 'Access denied: Only ADMIN users can perform this action.'], Response::HTTP_FORBIDDEN);
        }

        $backupFile = 'backup.sql';

        $command = [
            'mysqldump',
            '--host=localhost',
            '--user=' . $_ENV['DATABASE_USER'],
            '--password=' . $_ENV['DATABASE_PASSWORD'],
            '--databases', $_ENV['DATABASE_NAME'],
            '--result-file=' . $backupFile
        ];

        $process = new Process($command);
        try {
            $process->mustRun();
            return $this->json(['message' => 'Backup created successfully', 'file' => $backupFile]);
        } catch (ProcessFailedException $exception) {
            return $this->json(['error' => 'Backup failed: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
