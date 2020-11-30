<?php


namespace App\Service;


use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class FormUtils
{
    public static function mapFormErrors(FormInterface $form): array
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = static::mapFormErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    public static function mapValidatorErrors(ConstraintViolationListInterface $errors): array
    {
        $errors = array();

        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $errors[$error->getPropertyPath()] = [
                $error->getMessage(),
            ];
        }

        return $errors;
    }
}