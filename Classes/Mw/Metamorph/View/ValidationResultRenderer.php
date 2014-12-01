<?php
namespace Mw\Metamorph\View;


use TYPO3\Flow\Error\Result;

class ValidationResultRenderer
{



    public function renderValidationResult(Result $result, $message = NULL)
    {
        $output = $message ?: 'The following validation errors have occurred:';
        $output .= "\n\n";

        foreach ($result->getFlattenedErrors() as $property => $errors)
        {
            $output .= "<comment>$property</comment>:\n";
            foreach ($errors as $error)
            {
                $output .= "  " . $error->getMessage() . "\n";
            }
        }
        return $output;
    }

}