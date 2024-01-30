<?php

namespace Oro\Bundle\CustomerBundle\Security;

/**
 * An utility class to encode and decode a visitor identifier string.
 */
class VisitorIdentifierUtil
{
    private const VISITOR_PREFIX          = 'visitor:';
    private const VISITOR_IDENTIFIER_EXPR = '/^visitor:\d+:\w+/';

    /**
     * Checks whether the given string represents a visitor identifier.
     */
    public static function isVisitorIdentifier(string $identifier): bool
    {
        return preg_match(self::VISITOR_IDENTIFIER_EXPR, $identifier) === 1;
    }

    /**
     * Gets a string that represents an encoded identifier of the given visitor.
     */
    public static function encodeIdentifier(int $visitorId, string $visitorSessionId): string
    {
        return self::VISITOR_PREFIX . $visitorId . ':' . $visitorSessionId;
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

        return [(int)$identifierData[1], $identifierData[2]];
    }
}
