<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SelectEntityType extends SelectType
{
    public function __construct()
    {
        parent::__construct(EntityType::class, 'pintushi_select_entity');
    }
}
