<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\Klevu;

class Response
{
    /**
     * @var string
     */
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * @var string
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * @var int
     */
    private $code = null;

    /**
     * @var string
     */
    private $status = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $error = null;

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = (int)$code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (string)$status;

        return $this;
    }
    
    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->error = (string)$error;

        return $this;
    }
}
