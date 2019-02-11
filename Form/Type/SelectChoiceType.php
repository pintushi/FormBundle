<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SelectChoiceType extends SelectType
{
    public function __construct()
    {
        parent::__construct(ChoiceType::class, 'pintushi_select_choice');
    }
}
