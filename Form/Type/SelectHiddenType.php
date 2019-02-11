<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SelectHiddenType extends SelectType
{
    public function __construct()
    {
        parent::__construct(HiddenType::class, 'pintushi_select_hidden');
    }
}
