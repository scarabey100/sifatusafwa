<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
// Set the cookie with HttpOnly
setcookie(
    'httponly_cookie',   // Cookie name
    'value1',            // Cookie value
    time() + 3600,       // Expiry time (1 hour from now)
    '/',                 // Path (available across the entire domain)
    '',                  // Domain (default is the current domain)
    false,               // Secure (set to true if using HTTPS)
    true                 // HttpOnly flag
);

// Set the cookie without HttpOnly
setcookie(
    'non_httponly_cookie', // Cookie name
    'value2',              // Cookie value
    time() + 3600,         // Expiry time (1 hour from now)
    '/',                   // Path (available across the entire domain)
    '',                    // Domain (default is the current domain)
    false,                 // Secure (set to true if using HTTPS)
    false                  // HttpOnly flag
);

echo 'Cookies have been set!';
