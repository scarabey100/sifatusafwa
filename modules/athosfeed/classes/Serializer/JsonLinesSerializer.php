<?php

namespace AthosFeed\Serializer;

class JsonLinesSerializer
{
    public function serialize(array $records)
    {
        $lines = array();
        foreach ($records as $record) {
            $lines[] = $this->serializeRecord($record);
        }

        return implode("\n", $lines) . ($lines ? "\n" : '');
    }

    public function serializeRecord(array $record)
    {
        $json = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Unable to encode feed record: ' . json_last_error_msg());
        }
        return $json;
    }
}
