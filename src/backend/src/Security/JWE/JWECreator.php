<?php

namespace App\Security\JWE;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class JWECreator implements JWECreatorInterface
{
    protected ?string $payload = null;

    private ContainerBagInterface $containerBag;

    public function __construct(ContainerBagInterface $containerBag)
    {
        $this->containerBag = $containerBag;
    }

    public function setPayload(string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): JWE
    {
        $jwk = new JWK([
            'kty' => 'oct',
            'k' => $this->containerBag->get('jwt_key'),
        ]);

        // create a new JWE
        return self::jweBuilder()
            ->create()
            ->withPayload($this->payload)
            ->withSharedProtectedHeader([
                'alg' => 'A256KW',
                'enc' => 'A256CBC-HS512',
                'zip' => 'DEF',
            ])
            ->addRecipient($jwk)
            ->build();
    }

    /**
     * Create JWE token builder.
     *
     * @return JWEBuilder
     */
    private function jweBuilder(): JWEBuilder
    {
        // key encryption algorithm manager with the A256KW algorithm.
        $keyEncryptionAlgorithmManager = new AlgorithmManager([
            new A256KW(),
        ]);

        // content encryption algorithm manager with the A256CBC-HS256 algorithm.
        $contentEncryptionAlgorithmManager = new AlgorithmManager([
            new A256CBCHS512(),
        ]);

        // compression method manager with the DEF (Deflate) method.
        $compressionMethodManager = new CompressionMethodManager([
            new Deflate(),
        ]);

        // initiate the JWE Builder.
        return new JWEBuilder(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );
    }
}
