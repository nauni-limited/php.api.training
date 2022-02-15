<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskGetFormType;
use App\Form\TaskPostFormType;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use function array_map;
use function assert;
use function is_array;
use function is_string;
use function json_decode;

#[Suite(['controller', 'task'])]
class TaskController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private SerializerInterface $responseSerializer,
        private SerializerInterface $errorSerializer,
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
        if (!$this->createForm(TaskGetFormType::class, new Task())->submit(['id' => $id])->isValid()) {
            return (new Response())->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        };

        $task = $this->doctrine
            ->getRepository(Task::class)
            ->find($id);

        if ($task === null) {
            return (new Response())->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        assert($task instanceof Task);
        return new Response(
            $this->responseSerializer->serialize($task, 'json', [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i']),
            Response::HTTP_OK,
            ['content-type' => 'application/json'],
        );
    }

    #[Route('/task', name: 'add_task', methods: ['POST'])]
    public function addTask(Request $request): Response
    {
        $content = $request->getContent();
        assert(is_string($content));

        $postData = json_decode($content, true);
        assert(is_array($postData));

        $form = $this->createForm(TaskPostFormType::class, new Task())->submit($postData);

        if (!$form->isValid()) {
            return (new Response(
                $this->errorSerializer->serialize($form, 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['content-type' => 'application/json'],
            ));
        };

        $task = $form->getData();
        assert($task instanceof Task);

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
                $value = new DateTimeImmutable($value);
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
            ->setDeadline(new DateTimeImmutable($putData['deadline']))
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
