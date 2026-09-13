<?php
$file = 'D:/dsBilling/app/Http/Requests/Auth/LoginRequest.php';
$content = file_get_contents($file);

// Ensure we have the closing brace for the class
$content = rtrim($content);
if (substr($content, -1) === '}') {
    $content = substr($content, 0, -1);
}

$missingMethods = <<<PHP

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts(\$this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(\$this));

        \$seconds = RateLimiter::availableIn(\$this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => \$seconds,
                'minutes' => ceil(\$seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(\$this->string('login')).'|'.\$this->ip());
    }
}
PHP;

file_put_contents($file, $content . $missingMethods);
echo "Restored missing methods.\n";
?>
