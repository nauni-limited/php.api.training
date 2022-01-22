<?php

namespace App\Tests\Application\Controller;

use App\Entity\Task;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use function array_merge;
use function assert;
use function json_decode;
use function json_encode;

#[Suite(['controller', 'task'])]
class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManager $entityManager;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();

        $registry = $kernel->getContainer()
            ->get('doctrine');
        assert($registry instanceof Registry);

        $em = $registry->getManager();
        assert($em instanceof EntityManager);
        $this->entityManager = $em;
    }

    public function testGetTasksWhenWeHaveNoTasks(): void
    {
        $this->client->request('GET', '/tasks');

        $response = $this->client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);

        self::assertEquals([], json_decode($content, true));
    }

    public function testGetTasksWhenWeHaveTasks(): void
    {
        $tasks = [
            (new Task())
                ->setTitle('Title')
                ->setDescription('Description')
                ->setDeadline(new DateTime('2021-04-06 17:00'))
                ->setCompleted(true),
            (new Task())->setTitle('Another'),
        ];

        foreach ($tasks as $task) {
            $this->entityManager->persist($task);
        }

        $this->entityManager->flush();

        $this->client->request('GET', '/tasks');

        $expected = [
            [
                'id' => $tasks[0]->getId(),
                'title' => 'Title',
                'description' => 'Description',
                'deadline' => '2021-04-06 17:00',
                'completed' => true,
            ],
            [
                'id' => $tasks[1]->getId(),
                'title' => 'Another',
                'description' => null,
                'deadline' => null,
                'completed' => false,
            ],
        ];

        $response = $this->client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        $actual = json_decode($content, true);

        self::assertSame($expected, $actual);
    }

    public function testGetTaskWhenTheIdDoesNotExist(): void
    {
        $this->client->request('GET', '/task/1');

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetTaskWhenTheIdExists(): void
    {
        $task = (new Task())
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTime('2021-04-06 17:00'))
            ->setCompleted(true);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->request('GET', '/task/' . $task->getId());

        $expected = [
            'id' => $task->getId(),
            'title' => 'Title',
            'description' => 'Description',
            'deadline' => '2021-04-06 17:00',
            'completed' => true,
        ];

        $response = $this->client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);

        $actual = json_decode($content, true);

        self::assertSame($expected, $actual);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateTaskWhenIdDoesNotExist(): void
    {
        $parameters = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'deadline' => '2021-05-05 15:00',
            'completed' => false,
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'PUT',
            '/task/999',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateTaskWhenTheIdExists(): void
    {
        $task = (new Task())
            ->setTitle('First Title')
            ->setDescription('First description')
            ->setDeadline(new DateTime('2021-05-05 15:00'))
            ->setCompleted(false);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $parameters = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'deadline' => '2021-05-05 17:00',
            'completed' => true,
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'PUT',
            '/task/' . $task->getId(),
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->entityManager->refresh($task);
        $expected = array_merge(['id' => $task->getId()], $parameters);
        $this->assertSame($expected, $task->toArray());
    }

    public function testEditTaskTitleWhenIdDoesNotExist(): void
    {
        $parameters = [
            'title' => 'Test Title',
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'PATCH',
            '/task/99',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testEditTaskTitleWhenIdExists(): void
    {
        $task = (new Task())
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTime('2021-04-06 17:00'))
            ->setCompleted(true);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $parameters = [
            'title' => 'New Title',
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'PATCH',
            '/task/' . $task->getId(),
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();
        $this->entityManager->refresh($task);
        $actual = $task->toArray();

        $expected = [
            'id' => $task->getId(),
            'title' => 'New Title',
            'description' => 'Description',
            'deadline' => '2021-04-06 17:00',
            'completed' => true,
        ];

        self::assertSame($expected, $actual);
        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testEditTaskWhenIdExists(): void
    {
        $task = (new Task())
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTime('2021-04-06 17:00'))
            ->setCompleted(false);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $parameters = [
            'description' => 'New description',
            'deadline' => '2021-05-05 16:00',
            'completed' => true,
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'PATCH',
            '/task/' . $task->getId(),
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        $this->entityManager->refresh($task);
        $actual = $task->toArray();

        $expected = [
            'id' => $task->getId(),
            'title' => 'Title',
            'description' => 'New description',
            'deadline' => '2021-05-05 16:00',
            'completed' => true,
        ];

        self::assertSame($expected, $actual);
        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testPostToTask(): void
    {
        $parameters = [
            'title' => 'TestMe',
            'description' => 'MyDescription',
            'deadline' => '2021-04-06 17:00',
            'completed' => false,
        ];

        $requestParameters = json_encode($parameters);
        self::assertNotFalse($requestParameters);

        $this->client->request(
            'POST',
            '/task',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        $actual = $response->getStatusCode();

        self::assertSame(Response::HTTP_CREATED, $actual);

        $newEntity = $this->entityManager->getRepository(Task::class)->findAll()[0];
        $expected = array_merge(['id' => $newEntity->getId()], $parameters);
        $this->assertSame($expected, $newEntity->toArray());
    }

    public function testDeleteTaskWhenIdDoesNotExist(): void
    {
        $this->client->request('DELETE', '/task/999');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteTaskWhenWeHaveTheId(): void
    {
        $task = (new Task())
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTime('2021-04-06 17:00'))
            ->setCompleted(true);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $id = $task->getId();

        $this->client->request('DELETE', '/task/' . $id);

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->entityManager->clear();
        $this::assertNull($this->entityManager->getRepository(Task::class)->find($id));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        unset(
            $this->entityManager,
            $this->client,
        );
    }
}
