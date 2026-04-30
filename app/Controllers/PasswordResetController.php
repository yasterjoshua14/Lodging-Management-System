<?php

namespace App\Controllers;

use App\Models\PasswordResetOtpModel;
use App\Models\TenantModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class PasswordResetController extends BaseController
{
    private const OTP_TTL_MINUTES     = 10;
    private const MAX_ATTEMPTS        = 5;
    private const MAX_REQUESTS        = 5;
    private const RATE_WINDOW_MINUTES = 15;
    private const RESEND_SECONDS      = 60;
    private const MAX_SENDS           = 3;

    public function showTenantRequest(): string
    {
        return $this->showRequest('tenant');
    }

    public function requestTenant(): RedirectResponse
    {
        return $this->requestReset('tenant');
    }

    public function showTenantVerify(): string|RedirectResponse
    {
        return $this->showVerify('tenant');
    }

    public function verifyTenant(): RedirectResponse
    {
        return $this->verifyOtp('tenant');
    }

    public function resendTenant(): RedirectResponse
    {
        return $this->resendOtp('tenant');
    }

    public function showTenantReset(): string|RedirectResponse
    {
        return $this->showReset('tenant');
    }

    public function resetTenant(): RedirectResponse
    {
        return $this->resetPassword('tenant');
    }

    public function showAdminRequest(): string
    {
        return $this->showRequest('admin');
    }

    public function requestAdmin(): RedirectResponse
    {
        return $this->requestReset('admin');
    }

    public function showAdminVerify(): string|RedirectResponse
    {
        return $this->showVerify('admin');
    }

    public function verifyAdmin(): RedirectResponse
    {
        return $this->verifyOtp('admin');
    }

    public function resendAdmin(): RedirectResponse
    {
        return $this->resendOtp('admin');
    }

    public function showAdminReset(): string|RedirectResponse
    {
        return $this->showReset('admin');
    }

    public function resetAdmin(): RedirectResponse
    {
        return $this->resetPassword('admin');
    }

    private function showRequest(string $role): string
    {
        $this->clearResetSession($role);

        return view('auth/password_reset/request', $this->viewData($role, [
            'title'       => $this->portalLabel($role) . ' Password Recovery',
            'allowSms'    => true,
            'requestCopy' => $role === 'tenant'
                ? 'Enter the email address or phone number from your tenant record.'
                : 'Enter your admin email address or recovery phone number.',
        ]));
    }

    private function requestReset(string $role): RedirectResponse
    {
        $rules = [
            'identifier' => 'required|min_length[3]|max_length[120]',
            'channel'    => 'required|in_list[email,sms]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $identifier       = trim((string) $this->request->getPost('identifier'));
        $channel          = (string) $this->request->getPost('channel');
        $identifierHash   = $this->identifierHash($role, $identifier);
        $genericMessage   = 'If the account and channel match our records, we sent a 6-digit OTP.';
        $tooManyRequests  = $this->hasTooManyRequests($role, $identifierHash);

        if ($tooManyRequests) {
            return redirect()->back()->withInput()->with('warning', 'Too many recovery attempts. Please wait before trying again.');
        }

        $account = $this->findAccount($role, $identifier);

        if ($account === null) {
            return redirect()->to($this->loginUrl($role))->with('success', $genericMessage);
        }

        $destination = $this->resolveDestination($role, $account, $channel);

        if ($destination === null) {
            return redirect()->to($this->loginUrl($role))->with('success', $genericMessage);
        }

        $otp       = $this->generateOtp();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_TTL_MINUTES . ' minutes'));
        $now       = date('Y-m-d H:i:s');
        $resetOtp  = new PasswordResetOtpModel();

        $resetOtp
            ->where('user_id', $account['user']['id'])
            ->where('role', $role)
            ->where('consumed_at', null)
            ->set('consumed_at', $now)
            ->update();

        $resetId = $resetOtp->insert([
            'user_id'            => $account['user']['id'],
            'role'               => $role,
            'channel'            => $channel,
            'identifier_hash'    => $identifierHash,
            'otp_hash'           => password_hash($otp, PASSWORD_DEFAULT),
            'masked_destination' => $this->maskDestination($destination, $channel),
            'attempts'           => 0,
            'send_count'         => 1,
            'last_sent_at'       => $now,
            'expires_at'         => $expiresAt,
            'request_ip'         => $this->request->getIPAddress(),
        ]);

        if ($resetId === false || ! $this->deliverOtp($role, $account, $channel, $destination, $otp)) {
            if ($resetId !== false) {
                $resetOtp->delete($resetId);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('warning', 'We could not send an OTP to that channel right now. Please try another channel.');
        }

        $this->session->set([
            $this->sessionKey($role, 'id')       => (int) $resetId,
            $this->sessionKey($role, 'verified') => false,
        ]);

        return redirect()
            ->to($this->verifyUrl($role))
            ->with('success', 'We sent a 6-digit OTP to ' . $this->maskDestination($destination, $channel) . '.');
    }

    private function showVerify(string $role): string|RedirectResponse
    {
        $reset = $this->activeReset($role);

        if ($reset === null) {
            return redirect()->to($this->requestUrl($role))->with('warning', 'Please start password recovery again.');
        }

        return view('auth/password_reset/verify', $this->viewData($role, [
            'title'             => 'Verify OTP',
            'maskedDestination' => $reset['masked_destination'],
            'attemptsRemaining' => max(0, self::MAX_ATTEMPTS - (int) $reset['attempts']),
            'canResend'         => $this->secondsUntilResend($reset) === 0 && (int) $reset['send_count'] < self::MAX_SENDS,
            'resendSeconds'     => $this->secondsUntilResend($reset),
        ]));
    }

    private function verifyOtp(string $role): RedirectResponse
    {
        $reset = $this->activeReset($role);

        if ($reset === null) {
            return redirect()->to($this->requestUrl($role))->with('warning', 'Please start password recovery again.');
        }

        if (! $this->validate(['otp' => 'required|exact_length[6]|numeric'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $resetOtp = new PasswordResetOtpModel();

        if ((int) $reset['attempts'] >= self::MAX_ATTEMPTS) {
            $resetOtp->update($reset['id'], ['consumed_at' => date('Y-m-d H:i:s')]);
            $this->clearResetSession($role);

            return redirect()->to($this->requestUrl($role))->with('warning', 'That OTP has been locked after too many attempts.');
        }

        $otp = trim((string) $this->request->getPost('otp'));

        if (! password_verify($otp, $reset['otp_hash'])) {
            $resetOtp->update($reset['id'], [
                'attempts' => (int) $reset['attempts'] + 1,
            ]);

            return redirect()->back()->withInput()->with('error', 'The OTP you entered is incorrect or expired.');
        }

        $resetOtp->update($reset['id'], [
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->set($this->sessionKey($role, 'verified'), true);

        return redirect()->to($this->resetUrl($role))->with('success', 'OTP verified. Create a new password.');
    }

    private function resendOtp(string $role): RedirectResponse
    {
        $reset = $this->activeReset($role);

        if ($reset === null) {
            return redirect()->to($this->requestUrl($role))->with('warning', 'Please start password recovery again.');
        }

        if ((int) $reset['send_count'] >= self::MAX_SENDS) {
            return redirect()->back()->with('warning', 'You have reached the OTP resend limit. Please start again.');
        }

        $seconds = $this->secondsUntilResend($reset);
        if ($seconds > 0) {
            return redirect()->back()->with('warning', 'Please wait ' . $seconds . ' seconds before requesting another OTP.');
        }

        $account = $this->findAccountByUserId($role, (int) $reset['user_id']);
        if ($account === null) {
            $this->clearResetSession($role);

            return redirect()->to($this->requestUrl($role))->with('warning', 'Please start password recovery again.');
        }

        $destination = $this->resolveDestination($role, $account, $reset['channel']);
        if ($destination === null) {
            return redirect()->back()->with('warning', 'That recovery channel is no longer available.');
        }

        $otp = $this->generateOtp();

        if (! $this->deliverOtp($role, $account, $reset['channel'], $destination, $otp)) {
            return redirect()->back()->with('warning', 'We could not resend an OTP right now.');
        }

        (new PasswordResetOtpModel())->update($reset['id'], [
            'otp_hash'           => password_hash($otp, PASSWORD_DEFAULT),
            'masked_destination' => $this->maskDestination($destination, $reset['channel']),
            'attempts'           => 0,
            'send_count'         => (int) $reset['send_count'] + 1,
            'last_sent_at'       => date('Y-m-d H:i:s'),
            'expires_at'         => date('Y-m-d H:i:s', strtotime('+' . self::OTP_TTL_MINUTES . ' minutes')),
        ]);

        return redirect()->back()->with('success', 'We sent a new OTP to ' . $this->maskDestination($destination, $reset['channel']) . '.');
    }

    private function showReset(string $role): string|RedirectResponse
    {
        $reset = $this->verifiedReset($role);

        if ($reset === null) {
            return redirect()->to($this->verifyUrl($role))->with('warning', 'Verify your OTP before creating a new password.');
        }

        return view('auth/password_reset/reset', $this->viewData($role, [
            'title' => 'Create New Password',
        ]));
    }

    private function resetPassword(string $role): RedirectResponse
    {
        $reset = $this->verifiedReset($role);

        if ($reset === null) {
            return redirect()->to($this->verifyUrl($role))->with('warning', 'Verify your OTP before creating a new password.');
        }

        $rules = [
            'password'         => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        (new UserModel())->update($reset['user_id'], [
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        (new PasswordResetOtpModel())->update($reset['id'], [
            'consumed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->clearResetSession($role);

        return redirect()->to($this->loginUrl($role))->with('success', 'Your password has been reset. You can now sign in.');
    }

    private function findAccount(string $role, string $identifier): ?array
    {
        return $role === 'admin'
            ? $this->findAdminAccount($identifier)
            : $this->findTenantAccount($identifier);
    }

    private function findAccountByUserId(string $role, int $userId): ?array
    {
        $user = (new UserModel())
            ->where('id', $userId)
            ->where('role', $role)
            ->first();

        if ($user === null) {
            return null;
        }

        if ($role === 'admin') {
            return ['user' => $user, 'tenant' => null];
        }

        $tenant = ! empty($user['tenant_id']) ? (new TenantModel())->find((int) $user['tenant_id']) : null;

        return $tenant === null ? null : ['user' => $user, 'tenant' => $tenant];
    }

    private function findAdminAccount(string $identifier): ?array
    {
        $normalizedPhone = $this->normalizePhone($identifier);
        $userModel       = new UserModel();

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $userModel
                ->where('role', 'admin')
                ->where('email', strtolower($identifier))
                ->first();

            return $user === null ? null : ['user' => $user, 'tenant' => null];
        }

        if ($normalizedPhone === '') {
            return null;
        }

        foreach ($userModel->where('role', 'admin')->findAll() as $user) {
            if ($this->normalizePhone((string) ($user['recovery_phone'] ?? '')) === $normalizedPhone) {
                return ['user' => $user, 'tenant' => null];
            }
        }

        return null;
    }

    private function findTenantAccount(string $identifier): ?array
    {
        $tenant = null;

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $tenant = (new TenantModel())
                ->where('email', strtolower($identifier))
                ->first();
        } else {
            $normalizedPhone = $this->normalizePhone($identifier);

            if ($normalizedPhone !== '') {
                foreach ((new TenantModel())->findAll() as $candidate) {
                    if ($this->normalizePhone((string) ($candidate['phone'] ?? '')) === $normalizedPhone) {
                        $tenant = $candidate;
                        break;
                    }
                }
            }
        }

        if ($tenant === null) {
            return null;
        }

        $user = (new UserModel())
            ->where('role', 'tenant')
            ->where('tenant_id', $tenant['id'])
            ->first();

        return $user === null ? null : ['user' => $user, 'tenant' => $tenant];
    }

    private function resolveDestination(string $role, array $account, string $channel): ?string
    {
        if ($channel === 'email') {
            $email = $role === 'tenant'
                ? (string) (($account['tenant']['email'] ?? '') ?: ($account['user']['email'] ?? ''))
                : (string) ($account['user']['email'] ?? '');

            return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : null;
        }

        $phone = $role === 'tenant'
            ? (string) ($account['tenant']['phone'] ?? '')
            : (string) ($account['user']['recovery_phone'] ?? '');

        return $this->normalizePhone($phone) === '' ? null : $phone;
    }

    private function deliverOtp(string $role, array $account, string $channel, string $destination, string $otp): bool
    {
        $name = (string) ($account['user']['full_name'] ?? 'there');

        if ($channel === 'email') {
            return $this->sendEmailOtp($destination, $name, $otp);
        }

        return $this->sendSmsOtp($role, (int) $account['user']['id'], $destination, $otp);
    }

    private function sendEmailOtp(string $destination, string $name, string $otp): bool
    {
        $message = "Hello {$name},\n\n"
            . "Your Lodging Management System password reset OTP is {$otp}.\n"
            . 'This code expires in ' . self::OTP_TTL_MINUTES . " minutes. If you did not request this reset, ignore this message.\n";

        try {
            $emailConfig = config('Email');
            $email       = service('email');
            $fromEmail   = $emailConfig->fromEmail !== '' ? $emailConfig->fromEmail : 'no-reply@lodging.local';
            $fromName    = $emailConfig->fromName !== '' ? $emailConfig->fromName : 'Lodging Management System';

            $email->clear(true);
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($destination);
            $email->setSubject('Your password reset OTP');
            $email->setMessage($message);

            if ($email->send(false)) {
                return true;
            }
        } catch (Throwable $exception) {
            log_message('error', 'Password reset email delivery failed: ' . $exception->getMessage());
        }

        if (ENVIRONMENT !== 'production') {
            log_message('notice', 'Development password reset email OTP for {destination}: {otp}', [
                'destination' => $this->maskDestination($destination, 'email'),
                'otp'         => $otp,
            ]);

            return true;
        }

        return false;
    }

    private function sendSmsOtp(string $role, int $userId, string $destination, string $otp): bool
    {
        if (ENVIRONMENT !== 'production') {
            log_message('notice', 'Development password reset SMS OTP for {role} user #{userId} at {destination}: {otp}', [
                'role'        => $role,
                'userId'      => $userId,
                'destination' => $this->maskDestination($destination, 'sms'),
                'otp'         => $otp,
            ]);

            return true;
        }

        log_message('error', 'SMS password reset delivery is not configured for production.');

        return false;
    }

    private function activeReset(string $role): ?array
    {
        $resetId = $this->session->get($this->sessionKey($role, 'id'));

        if ($resetId === null) {
            return null;
        }

        $reset = (new PasswordResetOtpModel())
            ->where('id', (int) $resetId)
            ->where('role', $role)
            ->where('consumed_at', null)
            ->first();

        if ($reset === null || strtotime((string) $reset['expires_at']) < time()) {
            if ($reset !== null) {
                (new PasswordResetOtpModel())->update($reset['id'], ['consumed_at' => date('Y-m-d H:i:s')]);
            }

            $this->clearResetSession($role);

            return null;
        }

        return $reset;
    }

    private function verifiedReset(string $role): ?array
    {
        $reset = $this->activeReset($role);

        if ($reset === null || ! $this->session->get($this->sessionKey($role, 'verified')) || empty($reset['verified_at'])) {
            return null;
        }

        return $reset;
    }

    private function hasTooManyRequests(string $role, string $identifierHash): bool
    {
        $windowStart = date('Y-m-d H:i:s', strtotime('-' . self::RATE_WINDOW_MINUTES . ' minutes'));
        $ipAddress   = $this->request->getIPAddress();
        $model       = new PasswordResetOtpModel();

        $identifierCount = $model
            ->where('role', $role)
            ->where('identifier_hash', $identifierHash)
            ->where('created_at >=', $windowStart)
            ->countAllResults();

        $ipCount = (new PasswordResetOtpModel())
            ->where('role', $role)
            ->where('request_ip', $ipAddress)
            ->where('created_at >=', $windowStart)
            ->countAllResults();

        return $identifierCount >= self::MAX_REQUESTS || $ipCount >= self::MAX_REQUESTS * 2;
    }

    private function secondsUntilResend(array $reset): int
    {
        $lastSent = strtotime((string) ($reset['last_sent_at'] ?? ''));

        if ($lastSent === false) {
            return 0;
        }

        return max(0, self::RESEND_SECONDS - (time() - $lastSent));
    }

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function identifierHash(string $role, string $identifier): string
    {
        $normalized = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? strtolower(trim($identifier))
            : $this->normalizePhone($identifier);

        return hash('sha256', $role . '|' . $normalized);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function maskDestination(string $destination, string $channel): string
    {
        if ($channel === 'email') {
            [$name, $domain] = array_pad(explode('@', $destination, 2), 2, '');
            $visible         = substr($name, 0, 1);

            return $visible . str_repeat('*', max(strlen($name) - 1, 3)) . '@' . $domain;
        }

        $digits = $this->normalizePhone($destination);

        return str_repeat('*', max(strlen($digits) - 4, 3)) . substr($digits, -4);
    }

    private function clearResetSession(string $role): void
    {
        $this->session->remove([
            $this->sessionKey($role, 'id'),
            $this->sessionKey($role, 'verified'),
        ]);
    }

    private function sessionKey(string $role, string $name): string
    {
        return 'password_reset_' . $role . '_' . $name;
    }

    private function viewData(string $role, array $data = []): array
    {
        return $data + [
            'authSurface' => $role,
            'portalLabel' => $this->portalLabel($role),
            'loginUrl'    => $this->loginUrl($role),
            'requestUrl'  => $this->requestUrl($role),
            'verifyUrl'   => $this->verifyUrl($role),
            'resendUrl'   => $this->resendUrl($role),
            'resetUrl'    => $this->resetUrl($role),
        ];
    }

    private function portalLabel(string $role): string
    {
        return $role === 'admin' ? 'Admin' : 'Tenant';
    }

    private function loginUrl(string $role): string
    {
        return $role === 'admin' ? admin_path('login') : tenant_path('login');
    }

    private function requestUrl(string $role): string
    {
        return $role === 'admin' ? admin_path('forgot-password') : tenant_path('forgot-password');
    }

    private function verifyUrl(string $role): string
    {
        return $role === 'admin' ? admin_path('forgot-password/verify') : tenant_path('forgot-password/verify');
    }

    private function resendUrl(string $role): string
    {
        return $role === 'admin' ? admin_path('forgot-password/resend') : tenant_path('forgot-password/resend');
    }

    private function resetUrl(string $role): string
    {
        return $role === 'admin' ? admin_path('forgot-password/reset') : tenant_path('forgot-password/reset');
    }
}
