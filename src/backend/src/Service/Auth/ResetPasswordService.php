<?php

namespace App\Service\Auth;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Model\Request\ResetPasswordRequest;
use App\Repository\ResetPassword\ResetPasswordRepositoryInterface;
use App\Repository\User\UserRepositoryInterface;
use App\Service\BaseService;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordService
    extends BaseService
    implements ResetPasswordServiceInterface
{
    private ResetPasswordRepositoryInterface $resetPasswordRepository;
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHashes;

    public function __construct(
        ResetPasswordRepositoryInterface $resetPasswordRepository,
        UserRepositoryInterface $userRepository,
        UserPasswordHasherInterface $passwordHashes
    ) {
        $this->resetPasswordRepository = $resetPasswordRepository;
        $this->userRepository = $userRepository;
        $this->passwordHashes = $passwordHashes;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function reset(ResetPasswordRequest $request): bool
    {
        // request validation
        $this->validate($request, ['generate_token']);

        // checking user email is registered
        $user = $this->userRepository->findUserByEmail($request->getEmail());
        if (null === $user) {
            throw new Exception("Reset password was failed");
        }

        // save new reset password request
        $resetPassword = self::initResetPassword($request->getEmail());

        try {
            $this->resetPasswordRepository->save(
                $resetPassword
            );
        } catch (Exception $e) {
            throw new Exception("Reset password was failed");
        }

        // send reset password link to user's email
        self::sendPasswordResetLink($user, $resetPassword->getToken());

        return true;
    }

    /**
     * Initiate new ResetPassword entity
     *
     * @param string $email
     *
     * @return ResetPassword
     *
     * @throws Exception
     */
    private function initResetPassword(string $email): ResetPassword
    {
        $resetPassword = $this->resetPasswordRepository
            ->findResetPasswordByEmail($email);
        $interval = self::getResetInterval();
        $date = new DateTime();
        $expDate = $date->add(new DateInterval("PT{$interval}S"));
        $token = $this->generateHmac($email);

        // checking user has make reset password and his|her token is still active
        if ($resetPassword) {
            if ($date <= $resetPassword->getExpiredAt()) {
                throw new Exception("Reset password was failed");
            }

            $resetPassword->setToken($token)
                ->setCreatedAt($date)
                ->setExpiredAt($expDate);
        } else {
            $resetPassword = (new ResetPassword())
                ->setToken($token)
                ->setEmail($email)
                ->setCreatedAt($date)
                ->setExpiredAt($expDate);
        }

        return $resetPassword;
    }

    /**
     * Retrieve reset password interval from the environment.
     *
     * @return int
     */
    private function getResetInterval(): int
    {
        $interval = $this->getParam('set_password_expiration');

        if (!is_int($interval)) {
            return 3600;
        }

        return $interval;
    }

    /**
     * Sending password reset link to applicant's email.
     *
     * @param User $user
     * @param string $token
     *
     * @throws Exception
     */
    private function sendPasswordResetLink(User $user, string $token): void
    {
        $setPasswordUrl = $this->getParam('set_password_url');

        if (!is_array($setPasswordUrl)) {
            throw new Exception("Set password url not valid");
        }

        $url = "{$setPasswordUrl['url']}{$setPasswordUrl['path']}/{$token}";
        $templatedEmail = (new TemplatedEmail())
            ->subject("Reset Password")
            ->to($user->getEmail())
            ->htmlTemplate('reset-password-notify.html.twig')
            ->context([
                'name' => $user->getName(),
                'url' => $url,
            ]);

        $mailSetting = $this->mailSetting()->getMailSettingCached();

        if ($mailSetting) {
            $this->sendEmail(
                $mailSetting,
                $templatedEmail
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function savePassword(ResetPasswordRequest $request, string $token): bool
    {
        // request validation
        $this->validate($request, ['save']);

        $resetPassword = self::isTokenValid($token);
        $user = $this->userRepository
            ->findUserByEmail($resetPassword->getEmail());

        // check the user is still active
        if (!$user) {
            throw new Exception("Reset password was failed");
        }

        $password = $this->passwordHashes
            ->hashPassword($user, $request->getPassword());
        $user->setPassword($password);
        $user->setModifiedAt(new DateTime());

        // save new user password
        try {
            $this->userRepository->save($user);
            $this->resetPasswordRepository->delete($resetPassword);
        } catch (Exception $e) {
            throw new Exception("Reset password was failed");
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function isTokenValid(string $token): ResetPassword
    {
        $resetPassword = $this->resetPasswordRepository->findResetPasswordByToken($token);
        $date = new DateTime();

        if (!$resetPassword || $date >= $resetPassword->getExpiredAt()) {
            throw new Exception("Token reset password not valid");
        }

        return $resetPassword;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function clearExpiredSetPasswords(): void
    {
        $expiryDate = new DateTime();

        try {
            $this->resetPasswordRepository
                ->deleteExpiredResetPasswords($expiryDate);
        } catch (Exception $exception) {
            throw new Exception("Error deleting expired reset password: ".$exception->getMessage());
        }
    }
}