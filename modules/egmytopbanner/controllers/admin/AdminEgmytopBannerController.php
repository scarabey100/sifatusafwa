<?php
 
require_once  _PS_MODULE_DIR_ .'/egmytopbanner/classes/EgMyTopBannerObjectClass.php';
 

class AdminEgmytopBannerController extends ModuleAdminController
{
     /**
     * Instanciation de la classe
     * Définition des paramètres basiques obligatoires
     */
    
    public function __construct()
    {
        $this->bootstrap = true; //Gestion de l'affichage en mode bootstrap 
        $this->table = EgMyTopBannerObjectClass::$definition['table']; //Table de l'objet
        $this->identifier = EgMyTopBannerObjectClass::$definition['primary']; //Clé primaire de l'objet
        $this->className = EgMyTopBannerObjectClass::class; //Classe de l'objet
        $this->lang = true; // Enable multilang
        $this->dir = EgMyTopBannerObjectClass::$dirname.'/egmytopbanner';
    
        //Appel de la fonction parente pour pouvoir utiliser la traduction ensuite
        parent::__construct();
 
        //Liste des champs de l'objet à afficher dans la liste
        $this->fields_list = [
             
            'id_egmytopbanner' => [
                'title' => $this->module->l('ID'),
                'align' => 'left',
            ],
            'title' => [
                'title' => $this->module->l('Title'),
                'align' => 'left',
                'lang' => true, // Multilang
            ],
            'content_b' => [
                'title' => $this->module->l('Content'),
                'align' => 'left',
                'callback' => 'getDescriptionClean',
                'lang' => true, // Multilang
            ], 
            'color' => [
                'title' => $this->module->l('Color'),
                'align' => 'left',
                'callback' => 'getDescriptionClean',
            ], 
            'active' => [
                'title' => $this->module->l('Enable'),
                'align' => 'left',
                'search' => false,
            ]
        ];
        
        
        //Ajout d'actions sur chaque ligne
       
        $this->addRowAction('edit');
        $this->addRowAction('delete');
    }

    
    /**
     * @param $description
     * @return string Content without html
     */
    public static function getDescriptionClean($description)
    {
        return Tools::getDescriptionClean($description);
    }
    public function renderForm()
    {   
        
        
        $this->fields_form = [
            
            //Entête
            'legend' => [
                'title' => $this->module->l('Edit form'),
                'icon' => 'icon-cog'
            ],
             
            //Champs
            'input' => [
                
                [
                    'type' => 'text', //Type de champ
                    'label' => $this->module->l('Title'), //Label
                    'name' => 'title', //Nom 
                    'size' => 160, //longueur maximale du champ
                    'required' => false, //Requis ou non
                    'empty_message' => $this->l('Please fill the text'), //Message d'erreur si vide
                    'hint' => $this->module->l('Enter  text'), //Indication complémentaires de saisie
                    'lang' => true // Multilang
                ],

                [
                     
                    'type' => 'textarea',
                    'label' => $this->l('Content'),
                    'tinymce' => true,
                    'required' => true,
                    'name' => 'content_b',
                    'lang' => true, // Multilang
                    'cols' => 50,
                    'rows' => 10,
                    'class' => 'rte',
                    'autoload_rte' => true,
                    'hint' => $this->l('Caractères invalides:').' <>;=#{}'
                ],
                [
                    'type' => 'color',
                    'label' => $this->trans('Color', array(), 'Admin.Global'),
                    'name' => 'color',
                    'desc' => $this->trans('Please enter a Color.', array(), 'Admin.Global')
                ],
                
                 [
                    'type' => 'switch', //Type de champ
                    'label' => $this->module->l('Enable'), //Label
                    'name' => 'active', //Nom 
                    'class' => 't', //longueur maximale du champ
                    'required' => false, //Requis ou non
                    'is_bool' => true, 
                     'values' => array (
                                array (
                                    'id' => 'active_on',
                                    'value' => 1 ,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                        )

                ], 
               
            ],
            
            //Boutton de soumission
            'submit' => [
                'title' => $this->l('Save'), //On garde volontairement la traduction de l'admin par défaut
                 
                ]
        ];
     
      
        return parent::renderForm(); 
    }



    public function viewAccess($disable = false)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<=')){
            return true;
        }
        return parent::viewAccess($disable);
    
    }
        
 
}