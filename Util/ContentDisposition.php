<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\String\UnicodeString;

/**
 * Builds a Content-Disposition header for any filename, eg one built from a user's name.
 * HeaderUtils::makeDisposition() throws on slashes & needs an ASCII fallback (without "%") for a name
 * like "Zoë" – Symfony leaves both to the app (symfony/symfony#34528, #37978).
 */
class ContentDisposition
{
    public static function make(string $disposition, string $filename): string
    {
        // slashes, characters Windows doesn't allow in filenames & control characters.
        // Matched by byte, as none of them can be part of a multibyte UTF-8 character
        $filename = preg_replace('/[\/\\\\:*?"<>|\x00-\x1f\x7f]/', '-', $filename);

        return HeaderUtils::makeDisposition(
            $disposition,
            $filename,
            // ascii() transliterates ("Zoë" to "Zoe") & turns what it can't into "?"
            preg_replace('/[^\x20-\x7e]|[%?]/', '_', (new UnicodeString($filename))->ascii()->toString()),
        );
    }
}
