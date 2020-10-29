<?php


namespace App\Service;


class StringUtils
{
    /**
     * Return the camelCaseVersion of the snake_case_string.
     * @param string $string Original string.
     * @param bool $withFirstWord If true convert TheEntireString, else skip theFirstWord.
     * @return string
     */
    public static function snakeCaseToCamelCase(string $string, bool $withFirstWord = false): string
    {
        $words = explode('_', $string);

        if (count($words) > 1) {
            $offset = $withFirstWord ? 0 : 1;

            $words = array_map(function (string $word) {
                return ucfirst($word);
            }, array_slice($words, $offset));
        }

        return implode('', $words);
    }
}
