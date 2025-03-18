<?php

namespace Oro\Bundle\CustomerBundle\Security;

/**
 * An utility class to encode and decode a visitor identifier string.
 */
class VisitorIdentifierUtil
{
    /**
     * Checks whether the given string represents a visitor identifier.
     */
    public static function isVisitorIdentifier(string $identifier): bool
    {
        return preg_match('/^visitor:\w+$/', $identifier) === 1
            // BC compatibility (can be removed in v7.0): old format of the identifier
            || preg_match('/^visitor:\d+:\w+$/', $identifier) === 1;
    }

    /**
     * Gets a string that represents an encoded identifier of the given visitor.
     */
    public static function encodeIdentifier(int $visitorId, string $visitorSessionId): string
    {
        return 'visitor:' . $visitorSessionId;
    }

    /**
     * Decodes a visitor ID and its session ID from the given visitor identifier.
     *
     * @param string $identifier
     *
     * @return array [visitor id, visitor session id]
     */
    public static function decodeIdentifier(string $identifier): array
    {
        $identifierData = explode(':', $identifier);
        if (\count($identifierData) > 2) {
            // BC compatibility (can be removed in v7.0): old format of the identifier
            return [0, $identifierData[2]];
        }

        return [0, $identifierData[1]];
    }
}
