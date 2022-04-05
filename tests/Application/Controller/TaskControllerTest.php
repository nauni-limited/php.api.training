<?php

namespace App\Tests\Application\Controller;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

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
                ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
                ->setTitle('Title')
                ->setDescription('Description')
                ->setDeadline(new DateTimeImmutable('2021-04-06 17:00'))
                ->setCompleted(true),
            (new Task())
                ->setUuid(new Uuid('862a1c32-3999-41e9-87a5-a41755ab5d28'))
                ->setTitle('Another'),
        ];

        foreach ($tasks as $task) {
            $this->entityManager->persist($task);
        }

        $this->entityManager->flush();

        $this->client->request('GET', '/tasks');

        $expected = [
            [
                'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
                'title' => 'Title',
                'description' => 'Description',
                'deadline' => '2021-04-06 17:00',
                'completed' => true,
            ],
            [
                'uuid' => '862a1c32-3999-41e9-87a5-a41755ab5d28',
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
        $this->client->request('GET', '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7');

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetTaskWhenTheIdExists(): void
    {
        $task = (new Task())
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTimeImmutable('2021-04-06 17:00'))
            ->setCompleted(true);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->request('GET', '/task/' . $task->getUuid());

        $expected = [
            'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
            '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('First Title')
            ->setDescription('First description')
            ->setDeadline(new DateTimeImmutable('2021-05-05 15:00'))
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
            '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->entityManager->refresh($task);
        $expected = array_merge(['uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7'], $parameters);
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
            '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTimeImmutable('2021-04-06 17:00'))
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
            '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();
        $this->entityManager->refresh($task);
        $actual = $task->toArray();

        $expected = [
            'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTimeImmutable('2021-04-06 17:00'))
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
            '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7',
            [],
            [],
            [],
            $requestParameters,
        );

        $response = $this->client->getResponse();

        $this->entityManager->refresh($task);
        $actual = $task->toArray();

        $expected = [
            'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
            'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
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
        $expected = array_merge(['uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7'], $parameters);
        $this->assertSame($expected, $newEntity->toArray());
    }

    public function testDeleteTaskWhenIdDoesNotExist(): void
    {
        $this->client->request('DELETE', '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteTaskWhenWeHaveTheId(): void
    {
        $task = (new Task())
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTimeImmutable('2021-04-06 17:00'))
            ->setCompleted(true);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/task/08a94b31-4dd3-420f-be2f-377eaeb017f7');

        $response = $this->client->getResponse();

        self::assertSame('', $response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->entityManager->clear();
        $this::assertNull(
            $this->entityManager->getRepository(Task::class)->find('08a94b31-4dd3-420f-be2f-377eaeb017f7')
        );
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
