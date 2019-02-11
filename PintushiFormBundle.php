<?php

namespace Pintushi\Bundle\FormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pintushi\Bundle\FormBundle\DependencyInjection\Compiler\AutocompleteCompilerPass;

class PintushiFormBundle extends Bundle
{
      /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AutocompleteCompilerPass());
    }
}
