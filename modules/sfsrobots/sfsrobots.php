<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SfsRobots extends Module
{


    private const RULE_GROUPS = [
        'Faceted Search & Filters (ps_facetedsearch)' => [
            '?q=',
            '&q=',
            '?selected_filters=',
            '&selected_filters=',
        ],
        'Product Attribute / Combination Parameters' => [
            '?id_product_attribute=',
            '&id_product_attribute=',
        ],
    ];

    public function __construct()
    {
        $this->name = 'sfsrobots';
        $this->tab = 'seo';
        $this->version = '1.1.0';
        $this->author = 'SifatuSafwa';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('SifatuSafwa robots.txt rules');
        $this->description = $this->l('Adds project-specific rules to the standard robots.txt generator.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionAdminMetaBeforeWriteRobotsFile');
    }

    /**
     * Add rules to the generated content before PrestaShop writes robots.txt.
     *
     * @param array $params
     */
    public function hookActionAdminMetaBeforeWriteRobotsFile(array $params)
    {
        if (!isset($params['rb_data'])) {
            return;
        }

        if (is_array($params['rb_data'])) {
            $this->addRulesToRobotsData($params['rb_data']);

            return;
        }

        foreach (self::RULE_GROUPS as $label => $rules) {
            $missingRules = [];

            foreach ($rules as $rule) {
                $directive = 'Disallow: /*' . $rule;

                if (!preg_match('/^' . preg_quote($directive, '/') . '\r?$/m', $params['rb_data'])) {
                    $missingRules[] = $directive;
                }
            }

            if ($missingRules) {
                $params['rb_data'] = rtrim($params['rb_data'])
                    . "\n\n# {$label}\n"
                    . implode("\n", $missingRules)
                    . "\n";
            }
        }

        foreach (self::LEGACY_ARABIC_FILES as $file) {
            $directive = 'Disallow: /*ar/' . $file;

            if (!preg_match('/^' . preg_quote($directive, '/') . '\r?$/m', $params['rb_data'])) {
                $params['rb_data'] = rtrim($params['rb_data']) . "\n" . $directive . "\n";
            }
        }
    }

    /**
     * @param array $robotsData Structured data used by PrestaShop's robots generator
     */
    private function addRulesToRobotsData(array &$robotsData)
    {

        foreach (self::RULE_GROUPS as $rules) {
            foreach ($rules as $rule) {
                if (!in_array($rule, $robotsData['GB'], true)) {
                    $robotsData['GB'][] = $rule;
                }
            }
        }

        if (!isset($robotsData['Files']['ar']) || !is_array($robotsData['Files']['ar'])) {
            $robotsData['Files']['ar'] = [];
        }

        foreach (self::LEGACY_ARABIC_FILES as $file) {
            if (!in_array($file, $robotsData['Files']['ar'], true)) {
                $robotsData['Files']['ar'][] = $file;
            }
        }
    }
}
