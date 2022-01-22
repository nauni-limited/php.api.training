<?php

namespace App\Controller;

use App\Entity\Task;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function array_map;
use function assert;
use function is_array;
use function is_string;
use function json_decode;

#[Suite(['controller', 'task'])]
class TaskController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine
    ) {
    }

    #[Route('/tasks', name: 'list_tasks', methods: ['GET'])]
    public function listTasks(): Response
    {
        $tasks = $this->doctrine
            ->getRepository(Task::class)
            ->findAll();

        $tasks = array_map(fn(Task $task): array => $task->toArray(), $tasks);

        return new JsonResponse($tasks, Response::HTTP_OK);
    }

    #[Route('/task/{id}', name: 'get_task', methods: ['GET', 'HEAD'])]
    public function getTask(int $id): Response
    {
        $task = $this->doctrine
            ->getRepository(Task::class)
            ->find($id);

        if ($task === null) {
            return (new Response())->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        assert($task instanceof Task);
        return new JsonResponse($task->toArray(), Response::HTTP_OK);
    }

    #[Route('/task', name: 'add_task', methods: ['POST'])]
    public function addTask(Request $request): Response
    {
        $content = $request->getContent();
        assert(is_string($content));

        $postData = json_decode($content, true);
        assert(is_array($postData));

        $task = (new Task())
            ->setTitle($postData['title'])
            ->setDescription($postData['description'])
            ->setDeadline(new DateTime($postData['deadline']))
            ->setCompleted($postData['completed']);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($task);
        $entityManager->flush();

        return (new Response())->setStatusCode(Response::HTTP_CREATED);
    }

    #[Route('/task/{id}', name: 'edit_task', methods: ['PATCH'])]
    public function editTask(int $id, Request $request): Response
    {
        $task = $this->doctrine
            ->getRepository(Task::class)
            ->find($id);

        if ($task === null) {
            return (new Response())->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        assert($task instanceof Task);

        $content = $request->getContent();
        assert(is_string($content));

        $patchData = json_decode($content, true);
        assert(is_array($patchData));

        foreach ($patchData as $key => $value) {
            if ($key === 'deadline') {
                $value = new DateTime($value);
            }
            $task->{'set' . $key}($value);
        }

        $entityManager = $this->doctrine->getManager();
        $entityManager->flush();

        return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    #[Route('/task/{id}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(int $id, Request $request): Response
    {
        $task = $this->doctrine
            ->getRepository(Task::class)
            ->find($id);

        if ($task === null) {
            return (new Response())->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        assert($task instanceof Task);

        $content = $request->getContent();
        assert(is_string($content));

        $putData = json_decode($content, true);
        assert(is_array($putData));

        $task->setTitle($putData['title'])
            ->setDescription($putData['description'])
            ->setDeadline(new DateTime($putData['deadline']))
            ->setCompleted($putData['completed']);

        $entityManager = $this->doctrine->getManager();
        $entityManager->flush();

        return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    #[Route('/task/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(int $id): Response
    {
        $task = $this->doctrine
            ->getRepository(Task::class)
            ->find($id);

        if ($task === null) {
            return (new Response())->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        assert($task instanceof Task);

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
