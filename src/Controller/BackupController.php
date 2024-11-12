<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends AbstractController
{
    #[Route('/api/backup', name: 'app_backup', methods: ['GET'])]
    public function backup(): Response
    {
        // Define the backup file path (ensure the directory is writable)
        $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // The command to create a backup using mysqldump
        $command = [
            'mysqldump',
            '--host=localhost',
            '--user=' . $_ENV['DATABASE_USER'],
            '--password=' . $_ENV['DATABASE_PASSWORD'],
            '--databases', $_ENV['DATABASE_NAME'],
            '--result-file=' . $backupFile
        ];        

        // Run the command using Symfony's Process component
        $process = new Process($command);
        try {
            $process->mustRun();
            return new Response('Backup created successfully: ' . $backupFile);
        } catch (ProcessFailedException $exception) {
            return new Response('Backup failed: ' . $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
