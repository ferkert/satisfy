<?php

namespace Playbloom\Persister;

use Playbloom\Model\Abandoned;
use Playbloom\Model\PackageConstraint;
use Playbloom\Model\PackageStability;
use Playbloom\Model\Repository;
use Playbloom\Model\RepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigurationNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PackageConstraint::class . '[]' => true,
            RepositoryInterface::class . '[]' => true,
            PackageStability::class . '[]' => true,
            Abandoned::class . '[]' => true,
            \ArrayIterator::class => false,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if ($type === PackageConstraint::class . '[]') {
            return $this->denormalizeRequire($data);
        }

        if ($type === RepositoryInterface::class . '[]') {
            return $this->denormalizeRepositories($data);
        }

        if (\ArrayIterator::class === $type) {
            return new \ArrayIterator($this->denormalizeRepositories($data));
        }

        if ($type === PackageStability::class . '[]') {
            return $this->denormalizePackageStability($data);
        }

        if ($type === Abandoned::class . '[]') {
            return $this->denormalizeAbandoned($data);
        }

        if ($this->serializer instanceof DenormalizerInterface) {
            return $this->serializer->denormalize($data, $type, $format, $context);
        }

        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        // Configuration::$repositories is typed as "\ArrayIterator|RepositoryInterface[]". Since Symfony 7 the serializer
        // tries the \ArrayIterator member first and would build an empty iterator, so handle it here.
        if (\ArrayIterator::class === $type) {
            return 'repositories' === ($context['deserialization_path'] ?? null);
        }

        switch ($type) {
            case PackageConstraint::class . '[]':
            case RepositoryInterface::class . '[]':
            case PackageStability::class . '[]':
            case Abandoned::class . '[]':
                return true;
            default:
        }

        return false;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    private function denormalizeRequire($data)
    {
        $require = [];
        foreach ($data as $package => $constraint) {
            $require[] = new PackageConstraint($package, $constraint);
        }

        return $require;
    }

    private function denormalizeRepositories($data)
    {
        $list = [];
        foreach ($data as $item) {
            $repository = new Repository($item['url'], $item['type']);
            if (!empty($item['installation-source'])) {
                $repository->setInstallationSource($item['installation-source']);
            }
            $list[$repository->getId()] = $repository;
        }

        return $list;
    }

    private function denormalizePackageStability($data): array
    {
        $list = [];
        foreach ($data as $package => $stability) {
            $list[] = new PackageStability($package, $stability);
        }

        return $list;
    }

    private function denormalizeAbandoned($data): array
    {
        $list = [];
        foreach ($data as $package => $replacement) {
            if (!is_string($replacement)) {
                $replacement = null;
            }
            $list[] = new Abandoned($package, $replacement);
        }

        return $list;
    }
}
