<?php


namespace App\Service;


use Symfony\Component\Form\FormInterface;

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
}