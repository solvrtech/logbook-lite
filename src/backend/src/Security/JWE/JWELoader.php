<?php

namespace App\Security\JWE;

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\HeaderCheckerManagerFactory;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWE;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader as EncryptionJWELoader;
use Jose\Component\Encryption\JWETokenSupport;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class JWELoader implements JWELoaderInterface
{
    private ContainerBagInterface $containerBag;

    public function __construct(ContainerBagInterface $containerBag)
    {
        $this->containerBag = $containerBag;
    }

    /**
     * {@inheritDoc}
     */
    public function load(string $token): JWE
    {
        // Our key.
        $jwk = new JWK([
            'kty' => 'oct',
            'k' => $this->containerBag->get('jwt_key'),
        ]);

        // serializer manager
        $serializerManager = new JWESerializerManager([
            new CompactSerializer(),
        ]);

        // JWE loader
        $jweLoader = new EncryptionJWELoader(
            $serializerManager,
            self::jweDecrypt(),
            self::headerChecker()
        );
        $recipient = 0;

        // Decrypt the token
        return $jweLoader->loadAndDecryptWithKey($token, $jwk, $recipient);
    }

    /**
     * Create JWE token decrypter.
     *
     * @return JWEDecrypter
     */
    private function jweDecrypt(): JWEDecrypter
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

        // initiate the JWE Decrypted.
        return new JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );
    }

    /**
     * Create JWE token header checker manager.
     *
     * @return HeaderCheckerManager
     */
    private function headerChecker(): HeaderCheckerManager
    {
        $headerChecker = new HeaderCheckerManagerFactory();
        $headerChecker->add(
            'key_encryption_alg',
            new AlgorithmChecker(['A256KW'])
        );
        $headerChecker->addTokenTypeSupport(new JWETokenSupport());

        return $headerChecker->create(['key_encryption_alg']);
    }
}
