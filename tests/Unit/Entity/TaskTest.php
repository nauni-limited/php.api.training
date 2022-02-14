<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use DateTime;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testSetId(): void
    {
        $task = (new Task())->setId(10);
        self::assertEquals(10, $task->getId());
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
        $task = (new Task())->setDeadline(new DateTime('2020-01-04 10:00:00'));
        self::assertEquals('2020-01-04 10:00:00', $task->getDeadline()?->format('Y-m-d H:i:s'));
    }

    public function testSetDeadLineAllowsNull(): void
    {
        $task = (new Task())->setDeadline(new DateTime('2020-01-04 10:00:00'));
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
            ->setId(1)
            ->setTitle('Title')
            ->setDescription('Description')
            ->setDeadline(new DateTime('2021-05-05 16:00'))
            ->setCompleted(true);
        self::assertEquals(
            [
                'id' => 1,
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
            ->setId(1)
            ->setTitle('Title')
            ->setDescription(null)
            ->setDeadline(null)
            ->setCompleted(false);
        self::assertEquals(
            [
                'id' => 1,
                'title' => 'Title',
                'description' => null,
                'deadline' => null,
                'completed' => false,
            ],
            $task->toArray()
        );
    }
}
