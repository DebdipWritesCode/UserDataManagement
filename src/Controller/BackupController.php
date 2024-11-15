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
        $backupFile = 'backup.sql';

        if (file_exists($backupFile)) {
            unlink($backupFile);
        }

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
