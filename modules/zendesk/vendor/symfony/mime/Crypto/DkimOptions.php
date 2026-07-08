<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Crypto;

/**
 * A helper providing autocompletion for available DkimSigner options.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class DkimOptions
{
    private $options = [];

    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * @return $this
     */
    public function algorithm($algo): self
    {
        $this->options['algorithm'] = $algo;

        return $this;
    }

    /**
     * @return $this
     */
    public function signatureExpirationDelay($show): self
    {
        $this->options['signature_expiration_delay'] = $show;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyMaxLength($max): self
    {
        $this->options['body_max_length'] = $max;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyShowLength($show): self
    {
        $this->options['body_show_length'] = $show;

        return $this;
    }

    /**
     * @return $this
     */
    public function headerCanon($canon): self
    {
        $this->options['header_canon'] = $canon;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyCanon($canon): self
    {
        $this->options['body_canon'] = $canon;

        return $this;
    }

    /**
     * @return $this
     */
    public function headersToIgnore($headers): self
    {
        $this->options['headers_to_ignore'] = $headers;

        return $this;
    }
}
