<?php

if (!function_exists('applyTranslationReplacements')) {
    /**
     * Apply Laravel-style :placeholder replacements to an already-resolved string
     * (with the same :key / :Key / :KEY case variants Laravel uses). Needed because
     * the custom __() below resolves DB/JSON translations itself rather than via
     * trans(), so it must interpolate placeholders itself.
     */
    function applyTranslationReplacements($line, array $replace)
    {
        if (empty($replace) || !is_string($line)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $value = (string) $value;
            $line = strtr($line, [
                ':' . $key             => $value,
                ':' . strtoupper($key) => strtoupper($value),
                ':' . ucfirst($key)    => ucfirst($value),
            ]);
        }

        return $line;
    }
}

if (!function_exists('__')) {
    function __($key = null, $replace = [], $locale = null)
    {
        if (is_null($key)) {
            return $key;
        }

        if (session()->get('local') != null) {
            $path = resource_path() . "/lang/" . session()->get('local') . ".json";
            if (!file_exists($path)) {
                file_put_contents($path, '{}');
            }
            $website = json_decode(file_get_contents($path), true) ?: [];

            $key = preg_replace('/\s+/S', " ", $key);

            // Auto-register the key on first sight (translators fill the value later).
            if (!array_key_exists($key, $website)) {
                $website[$key] = $key;
                file_put_contents($path, json_encode($website));
            }

            // Resolve then interpolate — previously the stored value was returned
            // raw, so :placeholders were never replaced when a locale was active.
            return applyTranslationReplacements($website[$key], $replace);
        }

        return trans($key, $replace, $locale);
    }
}
