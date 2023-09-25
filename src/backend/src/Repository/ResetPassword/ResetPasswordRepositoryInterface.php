<?php

namespace App\Repository\ResetPassword;

use App\Entity\ResetPassword;
use DateTime;

interface ResetPasswordRepositoryInterface
{
    /**
     * Find user ResetPassword entity matching the token on the storage.
     *
     * @param string $token
     *
     * @return ResetPassword|null
     */
    public function findResetPasswordByToken(string $token): ResetPassword|null;

    /**
     * Find user ResetPassword entity matching the email on the storage.
     *
     * @param string $email
     *
     * @return ResetPassword|null
     */
    public function findResetPasswordByEmail(string $email): ResetPassword|null;

    /**
     * Save ResetPassword entity into storage.
     *
     * @param ResetPassword $resetPassword
     */
    public function save(ResetPassword $resetPassword): void;

    /**
     * Delete ResetPassword entity from the storage.
     *
     * @param ResetPassword $resetPassword
     */
    public function delete(ResetPassword $resetPassword): void;

    /**
     * Deletes any expired reset password records from the storage.
     *
     * @param DateTime $expiryDate
     */
    public function deleteExpiredResetPasswords(DateTime $expiryDate): void;
}