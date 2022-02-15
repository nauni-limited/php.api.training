<?php

namespace App\Component\Serializer\Normalizer;

use Nauni\Bundle\NauniTestSuiteBundle\Attribute\Suite;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function count;

#[Suite(['normalizer', 'form'])]
class FormErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const TITLE = 'title';

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array<string, string> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        assert($object instanceof FormInterface);
        $data = [
            'title' => $context[self::TITLE] ?? 'Validation Failed',
            'errors' => $this->convertFormErrorsToArray($object),
        ];

        if (0 !== count($object->all())) {
            $data['children'] = $this->convertFormChildrenToArray($object);
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof FormInterface && $data->isSubmitted() && !$data->isValid();
    }

    /**
     * @param FormInterface $data
     * @return array<int, array<string, string>>
     */
    private function convertFormErrorsToArray(FormInterface $data): array
    {
        $errors = [];

        foreach ($data->getErrors() as $error) {
            assert($error instanceof FormError);
            $errors[] = [
                'message' => $error->getMessage(),
            ];
        }

        return $errors;
    }

    /**
     * @param FormInterface $data
     * @return array<string, mixed>
     */
    private function convertFormChildrenToArray(FormInterface $data): array
    {
        $children = [];

        foreach ($data->all() as $child) {
            $childData = [
                'errors' => $this->convertFormErrorsToArray($child),
            ];

            if (!empty($child->all())) {
                $childData['children'] = $this->convertFormChildrenToArray($child);
            }

            $children[$child->getName()] = $childData;
        }

        return $children;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === static::class;
    }
}
