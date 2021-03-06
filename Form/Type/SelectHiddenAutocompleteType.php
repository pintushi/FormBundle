<?php

namespace Pintushi\Bundle\FormBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Pintushi\Bundle\FormBundle\Autocomplete\ConverterInterface;
use Pintushi\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Pintushi\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectHiddenAutocompleteType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SearchRegistry
     */
    protected $searchRegistry;

    /**
     * @param EntityManager  $entityManager
     * @param SearchRegistry $registry
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ObjectManager $entityManager,
        SearchRegistry $registry
    ) {
        $this->entityManager  = $entityManager;
        $this->searchRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaultConfig = [
            'placeholder'        => 'pintushi.form.choose_value',
            'allowClear'         => true,
            'minimumInputLength' => 0,
        ];

        $resolver
            ->setDefaults(
                [
                    'placeholder'        => '',
                    'empty_data'         => null,
                    'data_class'         => null,
                    'entity_class'       => null,
                    'configs'            => $defaultConfig,
                    'converter'          => null,
                    'autocomplete_alias' => null,
                    'excluded'           => null,
                    'random_id'          => true,
                    'error_bubbling'     => false,
                ]
            );

        $this->setConverterNormalizer($resolver);
        $this->setConfigsNormalizer($resolver, $defaultConfig);

        $resolver->setNormalizer(
            'entity_class',
            function (Options $options, $entityClass) {
                if (!empty($entityClass)) {
                    return $entityClass;
                }

                if (!empty($options['autocomplete_alias'])) {
                    $searchHandler = $this->searchRegistry->getSearchHandler($options['autocomplete_alias']);

                    return $searchHandler->getEntityName();
                }

                throw new InvalidConfigurationException('The option "entity_class" must be set.');
            }
        )
        ->setNormalizer(
            'transformer',
            function (Options $options, $value) {
                if (!$value && !empty($options['entity_class'])) {
                    $value = $this->createDefaultTransformer($options['entity_class']);
                }

                if (!$value instanceof DataTransformerInterface) {
                    throw new TransformationFailedException(
                        sprintf(
                            'The option "transformer" must be an instance of "%s".',
                            'Symfony\Component\Form\DataTransformerInterface'
                        )
                    );
                }

                return $value;
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function setConverterNormalizer(OptionsResolver $resolver)
    {
        $resolver->setNormalizer(
            'converter',
            function (Options $options, $value) {
                if (!$value && !empty($options['autocomplete_alias'])) {
                    $value = $this->searchRegistry->getSearchHandler($options['autocomplete_alias']);
                }

                if (!$value) {
                    throw new InvalidConfigurationException('The option "converter" must be set.');
                }

                if (!$value instanceof ConverterInterface) {
                    throw new UnexpectedTypeException(
                        $value,
                        'Pintushi\Bundle\FormBundle\Autocomplete\ConverterInterface'
                    );
                }

                return $value;
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     * @param array                    $defaultConfig
     */
    protected function setConfigsNormalizer(OptionsResolver $resolver, array $defaultConfig)
    {
        $resolver->setNormalizer(
            'configs',
            function (Options $options, $configs) use ($defaultConfig) {
                $result = array_replace_recursive($defaultConfig, $configs);

                if (!empty($options['autocomplete_alias'])) {
                    $autoCompleteAlias            = $options['autocomplete_alias'];
                    $result['autocomplete_alias'] = $autoCompleteAlias;
                    if (empty($result['properties'])) {
                        $searchHandler        = $this->searchRegistry->getSearchHandler($autoCompleteAlias);
                        $result['properties'] = $searchHandler->getProperties();
                    }
                    if (empty($result['route_name'])) {
                        $result['route_name'] = 'pintushi_form_autocomplete_search';
                    }
                    if (empty($result['component'])) {
                        $result['component'] = 'autocomplete';
                    }
                }

                if (!array_key_exists('route_parameters', $result)) {
                    $result['route_parameters'] = [];
                }

                if (empty($result['route_name'])) {
                    throw new InvalidConfigurationException(
                        'Option "configs[route_name]" must be set.'
                    );
                }

                return $result;
            }
        );
    }

    /**
     * @param string $entityClass
     *
     * @return EntityToIdTransformer
     */
    public function createDefaultTransformer($entityClass)
    {
        return new EntityToIdTransformer($this->entityManager, $entityClass);
    }

    /**
     * Set data-title attribute to element to show selected value
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $vars = [
            'configs'  => $options['configs'],
            'excluded' => (array)$options['excluded']
        ];

        if ($form->getData()) {
            /** @var ConverterInterface $converter */
            $converter = $options['converter'];
            if (isset($options['configs']['multiple']) && $options['configs']['multiple']) {
                $result = [];
                foreach ($form->getData() as $item) {
                    $result[] = $converter->convertItem($item);
                }
            } else {
                $result = $converter->convertItem($form->getData());
            }

            $vars['attr'] = [
                'data-selected-data' => $result
            ];
        }

        $view->vars = array_replace_recursive($view->vars, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return SelectHiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pintushi_select_hidden_autocomplete';
    }
}
