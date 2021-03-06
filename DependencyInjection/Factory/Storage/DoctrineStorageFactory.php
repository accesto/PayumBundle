<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

class DoctrineStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder
            ->beforeNormalization()->ifString()->then(function($v) {
                return array('driver' => $v);
            })->end()
            ->children()
                ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));

        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            $loader->load('doctrine.'.$config['driver'].'.xml');
        } else {
            $loader->load('doctrine.'.$config['driver'].'-bc-26.xml');
        }

        $storage = new DefinitionDecorator(sprintf('payum.storage.doctrine.%s', $config['driver']));
        $storage->setPublic(true);
        $storage->replaceArgument(1, $modelClass);
        
        return $storage;
    }
}