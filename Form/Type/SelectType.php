<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Pintushi\Bundle\FormBundle\Form\DataTransformer\ArrayToStringTransformer;
use Pintushi\Bundle\FormBundle\Form\DataTransformer\SelectArrayToStringTransformerDecorator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectType extends AbstractType
{
    const HIDDEN_TYPE = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';

    /**
     * @var string
     */
    private $parentForm;

    /**
     * @var string
     */
    private $blockPrefix;

    /**
     * @param $parentForm
     * @param $blockPrefix
     */
    public function __construct($parentForm, $blockPrefix)
    {
        $this->parentForm = $parentForm;
        $this->blockPrefix = $blockPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->parentForm === self::HIDDEN_TYPE) {
            $this->addHiddenTypeTransformer($builder, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configs'] = $options['configs'];

        $this->addSelectBlockPrefix($view);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'configs' => [],
            'transformer' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parentForm;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->blockPrefix;
    }

    /**
     * @param FormView $view
     */
    private function addSelectBlockPrefix(FormView $view)
    {
        $blockPrefixes = $view->vars['block_prefixes'];
        $position = array_search($this->getBlockPrefix(), $blockPrefixes);

        if ($position) {
            array_splice($blockPrefixes, $position, 0, 'pintushi_select2');
            $view->vars['block_prefixes'] = $blockPrefixes;
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function addHiddenTypeTransformer(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['configs']['multiple'])) {
            $builder->addViewTransformer(
                new SelectArrayToStringTransformerDecorator(new ArrayToStringTransformer(',', true))
            );
        } elseif (null !== $options['transformer']) {
            $builder->addModelTransformer($options['transformer']);
        }
    }
}
