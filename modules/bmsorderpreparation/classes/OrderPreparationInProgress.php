<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/Helper/CompatibilityOrderPreparation.php';

class BmsOrderPreparationInProgress extends ObjectModel
{

    public $id;

    public $id_employee;

    public $id_warehouse;

    public $id_order;

    public $id_state_ori;

    public $date_add;

    /**
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'bms_orderpreparation_inprogress',
        'primary' => 'id_bms_orderpreparation_inprogress',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_employee' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_warehouse' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_state_ori' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false
            )
        )

    );

    public static function remove($ids, $restoreState = true)
    {
        $arrId = array();
        if (! is_array($ids)) {
            $arrId[] = $ids;
        } else {
            $arrId = $ids;
        }
        foreach ($arrId as $order_id) {
            if ($restoreState) {
                $order_in_progress = BmsOrderPreparationInProgress::getOrderInProgressByIdOrder($order_id);
                $statut = $order_in_progress->id_state_ori;
                if (! empty($statut)) {
                    /**
                     * on n'utilise pas la fonction setCurrentState pour éviter de renvoyer un mail déjà reçu par un client *
                     */
                    $order = new Order((int) $order_id);
                    $order->current_state = $statut;
                    $order->save();
                    $history = new OrderHistory();
                    $history->id_order = (int) $order_id;
                    $history->changeIdOrderState($statut, (int) $order_id);
                    $history->save();
                }
            }
            DB::getInstance()->delete('bms_orderpreparation_inprogress', 'id_order=' . (int) $order_id);
        }
    }

    public static function append($ids)
    {
        $errors = array();

        $arrId = array();
        if (! is_array($ids)) {
            $arrId[] = $ids;
        } else {
            $arrId = $ids;
        }
        foreach ($arrId as $id) {
            $order_id = (int) $id;

            if (!self::orderCanBeShipped($order_id))
            {
                $errors[] = 'Order #'.$order_id.' can not be prepared because all products are not reserved';
                continue;
            }

            $inProgress = new BmsOrderPreparationInProgress();
            $inProgress->id_employee = (int) Context::getContext()->employee->id;
            $inProgress->id_order = $order_id;
            $statut = Configuration::get('BMS_OP_STATUT_IN_PROGRESS');
            if (! empty($statut)) {
                $order = new Order($order_id);
                $inProgress->id_state_ori = $order->current_state;
                /**
                 * setCurrentState modifie le statut de la commande, modifie l'historique des statuts + envoi un mail selon paramétrage du statut *
                 */
                $order->setCurrentState($statut, $inProgress->id_employee);
            }
            $inProgress->save();
        }

        return $errors;
    }

    public static function orderCanBeShipped($order_id)
    {
        if (CompatibilityOrderPreparation::advancedStockModuleIsInstalled())
        {
            $order = new Order($order_id);
            foreach($order->getProducts() as $orderItem)
            {
                $extendedOrderItem = AdvancedStockExtendedOrderDetail::getEodFromOdId($orderItem['id_order_detail']);
                if ($extendedOrderItem->getQtyToReserve() > 0)
                    return false;
            }
        }

        return true;
    }

    public static function getOrdersList()
    {
        $query = new DbQuery();
        $query->select('o.id_order as id_order,concat("#",o.id_order," (",c.firstname," " , c.lastname,") ",osl.name) as name')->from('orders', 'o');
        $query->leftJoin('order_state_lang', 'osl', 'current_state=id_order_state and osl.id_lang=' . Context::getContext()->language->id);
        $query->leftJoin('customer', 'c', 'c.id_customer=o.id_customer');
        $query->innerJoin('bms_orderpreparation_inprogress', 'opi', 'opi.id_order=o.id_order');

        $result = DB::getInstance()->ExecuteS($query);
        return $result;
    }

    public static function getOrderFromBareCode($id_order)
    {
        $id_order = (int) $id_order;
        $query = new DbQuery();
        $query->select('id_order')
            ->from('bms_orderpreparation_inprogress', 'ord')
            ->where("id_order=" . (int) $id_order);
        $result = DB::getInstance()->getValue($query);

        return $result;
    }

    public static function getLastOrderState($id_order)
    {
        $id_order_state = Db::getInstance()->getValue('
        SELECT `id_order_state`
        FROM `' . _DB_PREFIX_ . 'order_history`
        WHERE `id_order` = ' . (int) $id_order . '
        ORDER BY `date_add` DESC, `id_order_history` DESC');

        // returns false if there is no state
        if (! $id_order_state) {
            return false;
        }

        // else, returns an OrderState object
        return new OrderState($id_order_state, Configuration::get('PS_LANG_DEFAULT'));
    }

    public static function getLastHistory($id_order)
    {
        $id_order_history = Db::getInstance()->getValue('
        SELECT `id_order_history`
        FROM `' . _DB_PREFIX_ . 'order_history`
        WHERE `id_order` = ' . (int) $id_order . '
        ORDER BY `date_add` DESC, `id_order_history` DESC');

        // returns false if there is no state
        if (! $id_order_history) {
            return false;
        }

        // else, returns an OrderState object
        return new OrderHistory($id_order_history);
    }

    public static function setWsWeightNumber($id_order, $number)
    {
        $id_order_carrier = Db::getInstance()->getValue('
            SELECT `id_order_carrier`
            FROM `' . _DB_PREFIX_ . 'order_carrier`
            WHERE `id_order` = ' . (int) $id_order);
        if ($id_order_carrier) {
            $order_carrier = new OrderCarrier($id_order_carrier);
            $order_carrier->weight = (float) $number;
            $order_carrier->update();
        }
        return true;
    }

    public static function getWsWeightNumber($id_order)
    {
        $id_order_carrier = Db::getInstance()->getValue('
            SELECT `id_order_carrier`
            FROM `' . _DB_PREFIX_ . 'order_carrier`
            WHERE `id_order` = ' . (int) $id_order);
        if ($id_order_carrier) {
            $order_carrier = new OrderCarrier($id_order_carrier);
            return $order_carrier->weight;
        }
        return false;
    }

    public static function getOrdersInProgressLite()
    {
        $query = new DbQuery();

        $query->select('o.id_order')
            ->from('orders', 'o')
            ->innerJoin('bms_orderpreparation_inprogress', 'oi', 'oi.id_order=o.id_order');
        $results = Db::getInstance()->ExecuteS($query);
        return $results;
    }

    public static function getOrderInProgressByIdOrder($id_order)
    {
        $id_order_in_progress = Db::getInstance()->getValue('
            SELECT `id_bms_orderpreparation_inprogress`
            FROM `' . _DB_PREFIX_ . 'bms_orderpreparation_inprogress`
            WHERE `id_order` = ' . (int) $id_order);

        if ($id_order_in_progress) {
            $order_in_progress = new BmsOrderPreparationInProgress($id_order_in_progress);
            return $order_in_progress;
        }

        return false;
    }
}
