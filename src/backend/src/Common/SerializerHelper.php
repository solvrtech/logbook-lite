<?php

namespace App\Common;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerHelper
{
    public const JSON = 'json';

    /**
     * Convert an object into an array.
     *
     * @param object $object
     * @param array $ignoredAttributes Lists of attributes are not being converted.
     *
     * @return array
     *
     * @throws ExceptionInterface
     */
    public function toArray(object $object, array $ignoredAttributes): array
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->normalize(
            $object,
            null,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes]
        );
    }

    /**
     * Convert an array|string into object.
     *
     * @param array|string $data
     * @param string $className The name of object class
     *
     * @return mixed
     */
    public function toObj(array|string $data, string $className): mixed
    {
        $stringJson = is_array($data) ? json_encode($data) : $data;
        $stringJson = str_replace(':""', ':null', $stringJson);

        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->deserialize(
            $stringJson,
            $className,
            SerializerHelper::JSON,
            [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']
        );
    }

    /**
     * Convert an array into array object.
     *
     * @param array $array
     * @param string $className The name of object class
     * @param array $ignoredAttributes Lists of attributes are not being converted.
     *
     * @return array
     */
    public function toArrayObj(array $array, string $className, array $ignoredAttributes): array
    {
        $serializer = new Serializer(
            [
                new ObjectNormalizer(
                    null,
                    new CamelCaseToSnakeCaseNameConverter()
                ),
                new ArrayDenormalizer(),
            ],
            [new JsonEncoder()]
        );

        return $serializer->deserialize(
            json_encode($array),
            "{$className}[]",
            SerializerHelper::JSON,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes]
        );
    }
}
