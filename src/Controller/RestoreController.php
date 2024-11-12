<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RestoreController extends AbstractController
{
    #[Route('/api/restore', name: 'app_restore', methods: ['POST'])]
    public function restore(): Response
    {
        $backupFilePath = $this->getParameter('kernel.project_dir') . '/public/backup.sql'; // Modify this path as needed

        if (!file_exists($backupFilePath)) {
            return new Response('Backup file not found.', Response::HTTP_NOT_FOUND);
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
            return new Response('Database restored successfully.');
        } catch (ProcessFailedException $exception) {
            return new Response('Restore failed: ' . $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
