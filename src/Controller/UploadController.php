<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mime\Email;

class UploadController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw new BadRequestHttpException('No file uploaded');
        }

        $csvData = file_get_contents($file->getPathname());
        $lines = explode(PHP_EOL, $csvData);
        $users = [];

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $data = str_getcsv($line);
            if (count($data) !== 5) {
                continue;
            }

            list($name, $email, $username, $address, $role) = $data;

            $user = new User();
            $user->setName($name)
                ->setEmail($email)
                ->setUsername($username)
                ->setAddress($address)
                ->setRole($role);

            $this->entityManager->persist($user);
            $users[] = $user;
        }

        $this->entityManager->flush();

        foreach ($users as $user) {
            $email = (new Email())
                ->from('admin@example.com')
                ->to($user->getEmail())
                ->subject('User Data Uploaded')
                ->text("Hello {$user->getName()}, your data has been successfully uploaded.");

            // Dispatch the email to be sent asynchronously
            $this->messageBus->dispatch(new SendEmailMessage($email));
        }

        return $this->json([
            'status' => 'Data uploaded successfully!',
        ]);
    }
}
