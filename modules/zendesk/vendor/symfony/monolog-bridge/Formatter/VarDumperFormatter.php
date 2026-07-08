<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class VarDumperFormatter implements FormatterInterface
{
    private $cloner;

    public function __construct(VarCloner $cloner = null)
    {
        $this->cloner = $cloner ?: new VarCloner();
    }

    public function format($record)
    {
        $record['context'] = $this->cloner->cloneVar($record['context']);
        $record['extra'] = $this->cloner->cloneVar($record['extra']);

        return $record;
    }

    public function formatBatch($records)
    {
        foreach ($records as $k => $record) {
            $record[$k] = $this->format($record);
        }

        return $records;
    }
}
