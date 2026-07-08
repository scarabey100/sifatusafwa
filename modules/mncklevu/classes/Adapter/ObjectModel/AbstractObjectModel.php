<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\ObjectModel;

use Db;
use ObjectModel;

abstract class AbstractObjectModel extends ObjectModel
{
    /**
     * @param array $params
     *
     * @return null|string
     */
    protected function getFieldTypeByParams(array $params)
    {
        $result = null;

        if (isset($params['type'])) {
            switch ($params['type']) {
                case self::TYPE_INT:
                    $result = 'INT(' . (isset($params['size']) ? $params['size'] : 10) . ')';
                    if (isset($params['validate']) && (
                        (strtolower($params['validate']) === 'isunsignedid') ||
                        (strtolower($params['validate']) === 'isunsignedint')
                    )) {
                        $result .= ' UNSIGNED';
                    }
                    break;

                case self::TYPE_STRING:
                    if (isset($params['values'])) {
                        $result = 'ENUM(' . implode(
                            ', ',
                            array_map(
                                function($value) {
                                    return '\'' . $value . '\'';
                                },
                                $params['values']
                            )
                        ) . ')';
                    } else {
                        $result = isset($params['size']) ? 'VARCHAR(' . $params['size'] . ')' : 'TEXT';
                    }
                    break;

                case self::TYPE_BOOL:
                    $result = 'TINYINT(1) UNSIGNED';
                    break;

                case self::TYPE_FLOAT:
                    $result = 'DECIMAL(20,6)';
                    break;

                case self::TYPE_DATE:
                    $result = isset($params['validate']) && (strtolower($params['validate']) === 'isdateformat') ?
                        'DATE' : 'DATETIME';
                    break;
            }
        }

        if ($result) {
            if (isset($params['required']) && $params['required']) {
                $result .= ' NOT NULL';
            }
    
            if (isset($params['default'])) {
                $result .= ' DEFAULT \'' . $params['default'] . '\'';
            }
        }

        return $result;
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    protected function getFieldsSql(array $fields)
    {
        $result = [];

        foreach ($fields as $fieldName => $params) {
            $result[] = '`' . $fieldName . '` ' . $this->getFieldTypeByParams($params);
        }

        $result[] = '';

        return implode(', ', $result);
    }

    /**
     * @return bool
     */
    public function createTable()
    {
        $definition = ObjectModel::getDefinition($this);

        return (bool)Db::getInstance()->execute(implode('', [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $definition['table'] . '` (',
            '`' . $definition['primary'] . '` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ',
            $this->getFieldsSql($definition['fields']),
            'PRIMARY KEY (`' . $definition['primary'] . '`)',
            ') ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;'
        ]));
    }

    /**
     * @return bool
     */
    public function dropTable()
    {
        $definition = ObjectModel::getDefinition($this);

        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $definition['table'] . '`;');
    }
}
