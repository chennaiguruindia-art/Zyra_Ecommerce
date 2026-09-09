<?php

namespace App\Translation;

use Illuminate\Translation\FileLoader;

class SafeFileLoader extends FileLoader
{
    public function load($locale, $group, $namespace = null)
    {
        if ($group !== '*' && ! $this->isSafePhpGroup($group)) {
            return [];
        }

        if (! $this->isSafeLocale((string) $locale)) {
            return [];
        }

        return parent::load($locale, $group, $namespace);
    }

    private function isSafePhpGroup(string $group): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_.-]+$/', $group);
    }

    private function isSafeLocale(string $locale): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]+$/', $locale);
    }
}
