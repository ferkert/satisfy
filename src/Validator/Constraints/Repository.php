<?php

namespace Playbloom\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Repository extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
