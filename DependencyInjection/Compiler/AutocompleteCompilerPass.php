<?php

namespace Pintushi\Bundle\FormBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Pintushi\Bundle\FormBundle\Autocomplete\SearchRegistry;
use Pintushi\Bundle\FormBundle\Autocomplete\Security;

class AutocompleteCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $searchRegistryDefinition = $container->getDefinition(SearchRegistry::class);
        $securityDefinition = $container->getDefinition(Security::class);

        foreach ($container->findTaggedServiceIds('pintushi_form.autocomplete.search_handler') as $id => $attributes) {
            foreach ($attributes as $eachTag) {
                $name = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $searchRegistryDefinition->addMethodCall('addSearchHandler', array($name, new Reference($id)));
                if (!empty($eachTag['acl_resource'])) {
                    $securityDefinition->addMethodCall(
                        'setAutocompleteAclResource',
                        array($name, $eachTag['acl_resource'])
                    );
                }
            }
        }
    }
}
