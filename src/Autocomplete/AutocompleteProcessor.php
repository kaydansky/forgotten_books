<?php
/**
 * @author: AlexK
 * Date: 26-Jan-19
 * Time: 7:29 PM
 */

namespace ForgottenBooks\Autocomplete;

use ForgottenBooks\Domain\Queue\QueueModel;

class AutocompleteProcessor implements AutocompleteInterface
{
    private $term;
    public $json;

    public function __construct($method, $term)
    {
        $this->term = $term;
        $this->$method();
    }

    public function pibn()
    {
        $data = (new QueueModel())->autocompletePibn($this->term);

        if (! $data) {
            return false;
        }

        $a = [];

        foreach ($data as $item) {
            $a[] = [
                'id' => $item['id'],
                'value' => $item['pibn']
            ];
        }

        $this->json = json_encode($a);
    }
}