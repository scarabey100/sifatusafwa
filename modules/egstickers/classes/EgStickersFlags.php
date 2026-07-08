<?php

class EgStickersFlags extends ObjectModel
{
    public $id_flag;
    public $native_flag;
    public $parallel_value;
    public $sticker_position;
    public $color;
    public $active;

    public static $definition = [
        'table' => 'egstickers_flags',
        'primary' => 'id_flag',
        'multilang' => true,
        'fields' => [
            'native_flag' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'parallel_value' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true],
            'sticker_position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'required' => true],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    public static function NativeFlag($value)
    {
        $id_lang = Context::getContext()->language->id;
        $query = new DbQuery();
      
        $query->select('f.*, fl.parallel_value')
              ->from(self::$definition['table'], 'f')
              ->leftJoin(self::$definition['table'] . '_lang', 'fl', 'f.id_flag = fl.id_flag AND fl.id_lang = ' . (int)$id_lang)
              ->where('f.native_flag = \'' . pSQL($value) . '\''); 
        return Db::getInstance()->getRow($query); 
    }
}