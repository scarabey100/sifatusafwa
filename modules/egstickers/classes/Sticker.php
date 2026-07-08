<?php
class Sticker extends ObjectModel
{
    public $id_sticker;
    public $name;
    public $color;
    public $rate;
    public $position;
    public $active;
    public $sticker_position;

    public static $definition = [
        'table' => 'egstickers',
        'primary' => 'id_sticker',
        'multilang' => true,
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255, 'lang' => true],
            'color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'required' => true, 'size' => 7],
            'rate' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'sticker_position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
        ],
    ];
}