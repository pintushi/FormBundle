<?php

namespace Pintushi\Bundle\FormBundle\Form\Transformer;

use Symfony\Component\Form\FormInterface;
use Limenius\Liform\Transformer\AbstractTransformer;

class HiddenTransformer extends AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(FormInterface $form, array $extensions = [], $widget = null)
    {
        $schema = ['type' => 'hidden'];
        $schema = $this->addCommonSpecs($form, $schema, $extensions, $widget);

        return $schema;
    }
}
