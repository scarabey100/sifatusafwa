<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\Klevu;

class RequestData
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var Record[]
     */
    protected $records = [];

    /**
     * @param string $sessionId
     *
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = (string)$sessionId;

        return $this;
    }

    /**
     * @param Record $record
     *
     * @return $this
     */
    public function addRecord(Record $record)
    {
        $this->records[] = $record;

        return $this;
    }

    /**
     * @param Record[] $records
     *
     * @return $this
     */
    public function addRecords(array $records)
    {
        foreach ($records as $record) {
            $this->addRecord($record);
        }

        return $this;
    }

    /**
     * @return Record[]
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->sessionId && count($this->records);
    }

    /**
     * @return string
     */
    protected function getSessionIdXml()
    {
        return '<sessionId>' . $this->sessionId . '</sessionId>';
    }

    /**
     * @return string
     */
    protected function getRecordsXml()
    {
        return implode('', [
            '<records>',
            implode('', array_map(
                function($record) {
                    return $record->getXml();
                },
                $this->records
            )),
            '</records>'
        ]);
    }

    /**
     * @return string
     */
    public function getXml()
    {
        return implode('', [
            '<?xml version="1.0" encoding="UTF-8" ?><request>',
            $this->getSessionIdXml(),
            $this->getRecordsXml(),
            '</request>'
        ]);
    }
}
