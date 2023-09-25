<?php

namespace App\Service\Auth;

use App\Entity\ResetPassword;
use App\Model\Request\ResetPasswordRequest;

interface ResetPasswordServiceInterface
{
    /**
     * Request reset password and generate token
     * that can be provided to the user.
     *
     * @param ResetPasswordRequest $request
     *
     * @return bool
     */
    public function reset(ResetPasswordRequest $request): bool;

    /**
     * Validate token reset password is registered or not.
     *
     * @param string $token
     *
     * @return ResetPassword
     */
    public function isTokenValid(string $token): ResetPassword;

    /**
     * Register new user password.
     *
     * @param ResetPasswordRequest $request
     * @param string $token
     *
     * @return bool
     */
    public function savePassword(ResetPasswordRequest $request, string $token): bool;

    /**
     * Deletes any set password records from the system that have expired.
     */
    public function clearExpiredSetPasswords(): void;
}