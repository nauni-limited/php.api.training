<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskTest extends TestCase
{
    public function testSetUuid(): void
    {
        $task = (new Task())->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'));
        self::assertEquals('08a94b31-4dd3-420f-be2f-377eaeb017f7', (string) $task->getUuid());
    }

    public function testSetTitle(): void
    {
        $task = (new Task())->setTitle('Title');
        self::assertEquals('Title', $task->getTitle());
    }

    public function testSetDescription(): void
    {
        $task = (new Task())->setDescription('Description');
        self::assertEquals('Description', $task->getDescription());
    }

    public function testSetDescriptionAllowsNull(): void
    {
        $task = (new Task())->setDescription('Description');
        $task->setDescription(null);
        self::assertNull($task->getDescription());
    }

    public function testSetDeadLine(): void
    {
        $task = (new Task())->setDeadline(new DateTimeImmutable('2020-01-04 10:00:00'));
        self::assertEquals('2020-01-04 10:00:00', $task->getDeadline()?->format('Y-m-d H:i:s'));
    }

    public function testSetDeadLineAllowsNull(): void
    {
        $task = (new Task())->setDeadline(new DateTimeImmutable('2020-01-04 10:00:00'));
        $task->setDeadline(null);
        self::assertNull($task->getDeadline()?->format('Y-m-d H:i:s'));
    }

    public function testGetCompletedIsFalseByDefault(): void
    {
        $task = (new Task());
        self::assertFalse($task->getCompleted());
    }

    public function testSetCompleted(): void
    {
        $task = (new Task())->setCompleted(true);
        self::assertTrue($task->getCompleted());
    }

    public function testToArray(): void
    {
        $task = (new Task())
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTimeImmutable('2021-05-05 16:00'))
            ->setCompleted(true);
        self::assertEquals(
            [
                'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
                'title' => 'Title',
                'description' => 'Description',
                'deadline' => '2021-05-05 16:00',
                'completed' => true,
            ],
            $task->toArray()
        );
    }

    public function testToArrayWithNull(): void
    {
        $task = (new Task())
            ->setUuid(new Uuid('08a94b31-4dd3-420f-be2f-377eaeb017f7'))
            ->setTitle('Title')
            ->setDescription(null)
            ->setDeadline(null)
            ->setCompleted(false);
        self::assertEquals(
            [
                'uuid' => '08a94b31-4dd3-420f-be2f-377eaeb017f7',
                'title' => 'Title',
                'description' => null,
                'deadline' => null,
                'completed' => false,
            ],
            $task->toArray()
        );
    }
}
