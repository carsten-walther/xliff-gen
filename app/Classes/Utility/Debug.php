<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class Debug
 *
 * @package CarstenWalther\XliffGen\Utility
 */
class Debug
{
    /**
     * @param             $variable
     * @param string|null $title
     *
     * @return string
     */
    public static function var_dump($variable, string $title = null) : string
    {
        $output = '<div>';
        if ($title !== '') {
            $output .= '    <p><strong>' . $title . '</strong></p>';
        }
        $output .= '    <pre>';
        $output .= print_r($variable, true);
        $output .= '    </pre>';
        $output .= '</div>';

        echo $output;

        return '';
    }
}
