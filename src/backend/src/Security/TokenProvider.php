<?php

namespace App\Security;

use App\Common\Config\AuthConfig;
use App\Entity\User;
use App\Repository\User\UserRepositoryInterface;
use App\Security\JWE\JWECreatorInterface;
use App\Security\JWE\JWELoaderInterface;
use Exception;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TokenProvider implements TokenProviderInterface
{
    private ContainerBagInterface $containerBag;
    private JWECreatorInterface $JWECreator;
    private JWELoaderInterface $JWELoader;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ContainerBagInterface $containerBag,
        JWECreatorInterface $JWECreator,
        JWELoaderInterface $JWELoader,
        UserRepositoryInterface $userRepository
    ) {
        $this->containerBag = $containerBag;
        $this->JWECreator = $JWECreator;
        $this->JWELoader = $JWELoader;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function generateAccessToken(string $email): array
    {
        return self::generateToken($email, AuthConfig::ACCESS_TOKEN);
    }

    /**
     * Generate new JWE token.
     *
     * @param string $email
     * @param string $type
     *
     * @return array
     */
    private function generateToken(string $email, string $type): array
    {
        $date = time();

        if (AuthConfig::ACCESS_TOKEN === $type) {
            $exp = $date + $this->containerBag->get('jwt_access_expiration');
        } else {
            $exp = $date + $this->containerBag->get('jwt_refresh_expiration');
        }

        $payload = json_encode([
            'iat' => $date,
            'exp' => $exp,
            'email' => $email,
        ]);
        $token = $this->JWECreator->setPayload($payload)->create();

        return array(
            'exp' => $exp,
            'token' => (new CompactSerializer())
                ->serialize($token, 0),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateRefreshToken(string $email): array
    {
        return self::generateToken($email, AuthConfig::REFRESH_TOKEN);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getUser(string $token): User
    {
        $payload = $this->decode($token);
        $user = $this->userRepository->findUserByEmail($payload->email);

        if (null === $user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function decode(string $token): object
    {
        $jwe = $this->JWELoader->load($token);
        $payload = json_decode($jwe->getPayload());

        // checking token expired date
        if (time() <= $payload->exp) {
            return $payload;
        } else {
            throw new Exception("Token have expired");
        }
    }
}
