<?php

namespace App\Tests\Unit\Component\Serializer\Normalizer;

use App\Component\Serializer\Normalizer\FormErrorNormalizer;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

class FormErrorNormalizerTest extends TestCase
{
    private FormErrorNormalizer $normalizer;

    private FormInterface $form;

    protected function setUp(): void
    {
        $this->normalizer = new FormErrorNormalizer();

        $this->form = $this->createMock(FormInterface::class);
        $this->form->method('isSubmitted')->willReturn(true);
        $this->form->method('all')->willReturn([]);

        $this->form->method('getErrors')
            ->willReturn(new FormErrorIterator($this->form, [
                new FormError('a', 'b', ['c', 'd'], 5, 'f'),
                new FormError('1', '2', [3, 4], 5, 6),
            ]));
    }

    public function testSupportsNormalizationWithWrongClass(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new stdClass()));
    }

    public function testSupportsNormalizationWithNotSubmittedForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $this->assertFalse($this->normalizer->supportsNormalization($form));
    }

    public function testSupportsNormalizationWithValidForm(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($this->form));
    }

    public function testNormalize(): void
    {
        $expected = [
            'title' => 'Validation Failed',
            'errors' => [
                [
                    'message' => 'a',
                ],
                [
                    'message' => '1',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($this->form));
    }

    public function testNormalizeWithChildren(): void
    {
        $exptected = [
            'title' => 'Validation Failed',
            'errors' => [
                [
                    'message' => 'a',
                ],
            ],
            'children' => [
                'form1' => [
                    'errors' => [
                        [
                            'message' => 'b',
                        ],
                    ],
                ],
                'form2' => [
                    'errors' => [
                        [
                            'message' => 'c',
                        ],
                    ],
                    'children' => [
                        'form3' => [
                            'errors' => [
                                [
                                    'message' => 'd',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $form1 = clone $form2 = clone $form3 = $this->createMock(FormInterface::class);

        $form1->method('getErrors')
            ->willReturn(new FormErrorIterator($form1, [
                new FormError('b'),
            ]));
        $form1->method('getName')->willReturn('form1');

        $form2->method('getErrors')
            ->willReturn(new FormErrorIterator($form1, [
                new FormError('c'),
            ]));
        $form2->method('getName')->willReturn('form2');

        $form3->method('getErrors')
            ->willReturn(new FormErrorIterator($form1, [
                new FormError('d'),
            ]));
        $form3->method('getName')->willReturn('form3');

        $form2->method('all')->willReturn([$form3]);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('all')->willReturn([$form1, $form2]);
        $form->method('getErrors')
            ->willReturn(new FormErrorIterator($form, [
                new FormError('a'),
            ]));

        $this->assertEquals($exptected, $this->normalizer->normalize($form));
    }
}
