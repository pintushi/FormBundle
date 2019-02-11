<?php

namespace Pintushi\Bundle\FormBundle\Form\Transformer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Limenius\Liform\Transformer\AbstractTransformer;

class SelectHiddenAutocompleteTransfomer extends AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(FormInterface $form, array $extensions = [], $widget = null)
    {
        $formView = $form->createView();

        $schema = [
            'title' => $form->getConfig()->getOption('label'),
            'type' => 'integer',
        ];

        $schema = $this->addCommonSpecs($form, $schema, $extensions, $widget);
        $schema = $this->addWidgetConfig($form, $schema);

        return $schema;
    }

    protected function addWidgetConfig($form, $schema)
    {
        $schema['widget_config'] = $form->getConfig()->getOption('configs');

        return $schema;
    }
}
