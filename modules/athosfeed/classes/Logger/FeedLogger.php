<?php

namespace AthosFeed\Logger;

class FeedLogger
{
    private $baseContext = array();

    public function withContext(array $context)
    {
        $clone = clone $this;
        $clone->baseContext = array_merge($this->baseContext, $context);
        return $clone;
    }

    public function info($message, array $context = array())
    {
        $this->log(1, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(3, $message, $context);
    }

    protected function log($severity, $message, array $context)
    {
        $context = array_merge($this->baseContext, $context);
        foreach ($context as $key => $value) {
            $message = str_replace('{' . $key . '}', (string) $value, $message);
        }
        $prefix = '[athosfeed]';
        foreach (array('execution_id', 'shop', 'language', 'stage') as $key) {
            if (isset($context[$key])) {
                $prefix .= '[' . $key . '=' . preg_replace('/[^a-zA-Z0-9_.:-]/', '', (string) $context[$key]) . ']';
            }
        }
        \PrestaShopLogger::addLog($prefix . ' ' . $message, $severity);
    }
}
