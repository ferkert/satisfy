<?php

namespace Playbloom\Form\Type;

use Playbloom\Form\DataTransformer\JsonTextTransformer;
use Playbloom\Model\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('#[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*#'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('homepage', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('require', CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_type' => PackageConstraintType::class,
                'prototype' => true,
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
            ->add('blacklist', CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_type' => PackageConstraintType::class,
                'prototype' => true,
                'help' => 'Package versions to exclude after the packages above have been selected.',
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
            ->add('abandoned', CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_type' => AbandonedType::class,
                'prototype' => true,
                'help' => 'Packages to mark as abandoned, optionally with a replacement package.',
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
            ->add('requireAll', Type\CheckboxType::class, [
                'required' => false,
                'help' => 'Select all versions of all packages in the configured repositories.',
            ])
            ->add('requireDependencies', Type\CheckboxType::class, [
                'required' => false,
                'help' => 'Resolve all dependencies of the selected packages from the configured repositories.',
            ])
            ->add('requireDevDependencies', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('requireDependencyFilter', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('minimumStability', ChoiceType::class, [
                'required' => false,
                'choices' => PackageStabilityType::STABILITY_LEVELS,
                'constraints' => [
                    new Assert\Choice(choices: PackageStabilityType::STABILITY_LEVELS),
                ],
            ])
            ->add('minimumStabilityPerPackage', CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_type' => PackageStabilityType::class,
                'prototype' => true,
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
            ->add('includeFilename', TextType::class, [
                'required' => false,
                'help' => 'Filename of the include file. Default: include/all${SHA1_HASH}.json',
            ])
            ->add('outputDir', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'empty_data' => Configuration::DEFAULT_OUTPUT_DIR,
                'help' => 'Where the build writes the repository files, unless the build command is given another path.',
            ])
            ->add('outputHtml', Type\CheckboxType::class, [
                'required' => false,
                'label' => 'Output HTML',
                'help' => 'Build a static web page that lists all packages.',
            ])
            ->add('providers', Type\CheckboxType::class, [
                'required' => false,
                'help' => 'Dump package providers.',
            ])
            ->add('config', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'trim' => true,
                'help' => 'Composer configuration options as JSON.',
                'attr' => [
                    'rows' => 6,
                    'class' => 'font-monospace',
                    'spellcheck' => 'false',
                ],
            ])
            ->add('twigTemplate', TextType::class, [
                'required' => false,
                'help' => 'Path to a custom Twig template for the generated index.html page.',
            ])
            ->add('notifyBatch', UrlType::class, [
                'label' => 'Notify batch URL',
                'required' => false,
                'help' => 'URL that is called every time a user installs a package.',
            ])
            ->add('prettyPrint', Type\CheckboxType::class, [
                'label' => 'Pretty print',
                'required' => false,
                'help' => 'Write indented JSON files (JSON_PRETTY_PRINT).',
            ]);
        $builder->get('config')->addModelTransformer(new JsonTextTransformer());
    }
}
