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

class RestoreController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/restore', name: 'app_restore', methods: ['POST'])]
    public function restore(Request $request): Response
    {
        $username = $request->request->get('username');

        if (!$username) {
            return $this->json(['error' => 'Username is required'], Response::HTTP_BAD_REQUEST);
        }

        $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        
        if (!$adminUser || $adminUser->getRole() !== 'ADMIN') {
            return $this->json(['error' => 'Access denied: Only ADMIN users can perform this action.'], Response::HTTP_FORBIDDEN);
        }

        $backupFilePath = $this->getParameter('kernel.project_dir') . '/public/backup.sql';

        if (!file_exists($backupFilePath)) {
            return $this->json(['error' => 'Backup file not found.'], Response::HTTP_NOT_FOUND);
        }

        $command = sprintf(
            'mysql --host=localhost --user=%s --password=%s %s < "%s"',
            escapeshellarg($_ENV['DATABASE_USER']),
            escapeshellarg($_ENV['DATABASE_PASSWORD']),
            escapeshellarg($_ENV['DATABASE_NAME']),
            $backupFilePath
        );

        $process = Process::fromShellCommandline($command);
        
        try {
            $process->mustRun();
            return $this->json(['message' => 'Database restored successfully.']);
        } catch (ProcessFailedException $exception) {
            return $this->json(['error' => 'Restore failed: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
