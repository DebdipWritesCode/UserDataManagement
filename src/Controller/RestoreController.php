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
        $backupFilePath = $this->getParameter('kernel.project_dir') . '/public/backup.sql';

        if (!file_exists($backupFilePath)) {
            return $this->json(['error' => 'Backup file not found.'], Response::HTTP_NOT_FOUND);
        }

        $databaseHost = 'localhost';
        $databaseUser = $_ENV['DATABASE_USER'];
        $databasePassword = $_ENV['DATABASE_PASSWORD'];
        $databaseName = $_ENV['DATABASE_NAME'];

        $checkDbCommand = sprintf(
            'mysql --host=%s --user=%s --password=%s -e "SHOW DATABASES LIKE \'%s\'"',
            escapeshellarg($databaseHost),
            escapeshellarg($databaseUser),
            escapeshellarg($databasePassword),
            escapeshellarg($databaseName)
        );

        $process = Process::fromShellCommandline($checkDbCommand);
        
        try {
            $process->mustRun();
            $output = $process->getOutput();

            if (strpos($output, $databaseName) === false) {
                $createDbCommand = sprintf(
                    'mysql --host=%s --user=%s --password=%s -e "CREATE DATABASE %s;"',
                    escapeshellarg($databaseHost),
                    escapeshellarg($databaseUser),
                    escapeshellarg($databasePassword),
                    escapeshellarg($databaseName)
                );

                $process = Process::fromShellCommandline($createDbCommand);
                $process->mustRun();
            }

            $restoreCommand = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < "%s"',
                escapeshellarg($databaseHost),
                escapeshellarg($databaseUser),
                escapeshellarg($databasePassword),
                escapeshellarg($databaseName),
                $backupFilePath
            );

            $process = Process::fromShellCommandline($restoreCommand);
            $process->mustRun();

            return $this->json(['message' => 'Database restored successfully.']);

        } catch (ProcessFailedException $exception) {
            return $this->json(['error' => 'Restore failed: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
