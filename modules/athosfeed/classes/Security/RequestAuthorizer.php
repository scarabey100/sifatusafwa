<?php

namespace AthosFeed\Security;

class RequestAuthorizer
{
    public function authorize($expectedToken, $providedToken, $remoteAddress, $allowedIps = '')
    {
        if (!is_string($expectedToken) || $expectedToken === '' || !is_string($providedToken)
            || !hash_equals($expectedToken, $providedToken)) {
            return false;
        }
        $allowedIps = trim((string) $allowedIps);
        if ($allowedIps === '') {
            return true;
        }
        $allowed = array_filter(array_map('trim', explode(',', $allowedIps)));
        return in_array((string) $remoteAddress, $allowed, true);
    }
}
