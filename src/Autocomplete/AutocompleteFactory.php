<?php
/**
 * @author: AlexK
 * Date: 26-Jan-19
 * Time: 6:36 PM
 */

namespace ForgottenBooks\Autocomplete;


class AutocompleteFactory
{
    public function pibn($term)
    {
        return new AutocompleteProcessor('pibn', $term);
    }
}