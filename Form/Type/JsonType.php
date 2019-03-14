<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataTransformerInterface;

class JsonType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pintushi_json_type';
    }

     /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'compound' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        return is_array($data)? json_encode($data): $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        if (is_array($data)) {
            return $data;
        }

        if (empty($data)) {
            return [];
        }

        if (is_string($data) && $data = json_decode($data, true)) {
           return is_array($data) ? $data : [];
        }

        return [$data];
    }
}
