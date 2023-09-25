<?php

namespace App\Validator;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LanguageExistValidator extends ConstraintValidator
{
    private ContainerBagInterface $containerBag;

    public function __construct(ContainerBagInterface $containerBag)
    {
        $this->containerBag = $containerBag;
    }

    /**
     * {@iheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (is_array($value)) {
            if (!self::isArrayLangNotExist($value))
                return;
        } elseif (is_string($value)) {
            if (!self::isLangNotExist($value))
                return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }

    private function isArrayLangNotExist(array $values): bool
    {
        $languages = self::getAllLanguages();

        foreach ($values as $val) {
            if (!isset($languages[$val]))
                return true;
        }

        return false;
    }

    private function getAllLanguages(): array
    {
        return $this->containerBag->get('languages');
    }

    private function isLangNotExist(string $value): bool
    {
        $languages = self::getAllLanguages();

        return !isset($languages[$value]);
    }
}
