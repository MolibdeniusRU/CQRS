<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\NameConverter\SnakeCaseToCamelCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\FormErrorNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::Serializer->value, Serializer::class)
        ->args([[], [], []])
        ->alias(SerializerInterface::class, Component::Serializer->value)
        ->alias(NormalizerInterface::class, Component::Serializer->value)
        ->alias(DenormalizerInterface::class, Component::Serializer->value)
        ->alias(EncoderInterface::class, Component::Serializer->value)
        ->alias(DecoderInterface::class, Component::Serializer->value)
        ->alias('serializer.property_accessor', 'property_accessor')

        // Discriminator Map
        ->set('serializer.mapping.class_discriminator_resolver', ClassDiscriminatorFromClassMetadata::class)
        ->args([service('serializer.mapping.class_metadata_factory')])
        ->alias(ClassDiscriminatorResolverInterface::class, 'serializer.mapping.class_discriminator_resolver')

        // Normalizer
        ->set('serializer.normalizer.constraint_violation_list', ConstraintViolationListNormalizer::class)
        ->args([1 => service('serializer.name_converter.metadata_aware')])
        ->autowire(true)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -915])
        ->set('serializer.normalizer.datetimezone', DateTimeZoneNormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -915])
        ->set('serializer.normalizer.dateinterval', DateIntervalNormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -915])
        ->set('serializer.normalizer.data_uri', DataUriNormalizer::class)
        ->args([service('mime_types')->nullOnInvalid()])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -920])
        ->set('serializer.normalizer.datetime', DateTimeNormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -910])
        ->set('serializer.normalizer.json_serializable', JsonSerializableNormalizer::class)
        ->args([null, null])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -950])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -890])
        ->set('serializer.denormalizer.unwrapping', UnwrappingDenormalizer::class)
        ->args([service('serializer.property_accessor')])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => 1000])
        ->set('serializer.normalizer.uid', UidNormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -890])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -920])
        ->set('serializer.normalizer.form_error', FormErrorNormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -915])
        ->set('serializer.normalizer.object', ObjectNormalizer::class)
        ->args([
            service('serializer.mapping.class_metadata_factory'),
            service('serializer.name_converter.metadata_aware'),
            service('serializer.property_accessor'),
            service('property_info')->ignoreOnInvalid(),
            service('serializer.mapping.class_discriminator_resolver')->ignoreOnInvalid(),
            null,
            [],
            service('property_info')->ignoreOnInvalid(),
        ])
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -1000])
        ->set('serializer.normalizer.property', PropertyNormalizer::class)
        ->args([
            service('serializer.mapping.class_metadata_factory'),
            service('serializer.name_converter.metadata_aware'),
            service('property_info')->ignoreOnInvalid(),
            service('serializer.mapping.class_discriminator_resolver')->ignoreOnInvalid(),
            null,
        ])
        ->set('serializer.denormalizer.array', ArrayDenormalizer::class)
        ->tag('serializer.normalizer', ['built_in' => true, 'priority' => -990])

        // Loader
        ->set('serializer.mapping.attribute.loader', AttributeLoader::class)
        ->set('serializer.mapping.chain_loader', LoaderChain::class)
        ->args([[service('serializer.mapping.attribute.loader')]])

        // Class Metadata Factory
        ->set('serializer.mapping.class_metadata_factory', ClassMetadataFactory::class)
        ->args([service('serializer.mapping.chain_loader')])
        ->alias(ClassMetadataFactoryInterface::class, 'serializer.mapping.class_metadata_factory')

        // Encoders
        ->set('serializer.encoder.xml', XmlEncoder::class)
        ->tag('serializer.encoder', ['built_in' => true])
        ->set('serializer.encoder.json', JsonEncoder::class)
        ->args([null, null])
        ->tag('serializer.encoder', ['built_in' => true])
        ->set('serializer.encoder.yaml', YamlEncoder::class)
        ->args([null, null])
        ->tag('serializer.encoder', ['built_in' => true])
        ->set('serializer.encoder.csv', CsvEncoder::class)
        ->tag('serializer.encoder', ['built_in' => true])

        // Name converters
        ->set('serializer.name_converter.camel_case_to_snake_case', CamelCaseToSnakeCaseNameConverter::class)
        ->set('serializer.name_converter.snake_case_to_camel_case', SnakeCaseToCamelCaseNameConverter::class)
        ->set('serializer.name_converter.metadata_aware.abstract', MetadataAwareNameConverter::class)
        ->abstract()
        ->args([service('serializer.mapping.class_metadata_factory')])
        ->set('serializer.name_converter.metadata_aware')
        ->parent('serializer.name_converter.metadata_aware.abstract');
};