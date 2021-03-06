<?php
/**
 * @filesource modules/inventory/views/inward.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Inventory\Inward;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-inward.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * รายการสินค้ารับเข้า.
     *
     * @param Request $request
     * @param object  $owner
     *
     * @return string
     */
    public function render(Request $request, $owner)
    {
        $year_offset = Language::get('YEAR_OFFSET');
        // ปี เริ่มต้น 2017
        $years = array(0 => '{LNG_all items}');
        for ($y = 2017; $y <= date('Y'); ++$y) {
            $years[$y] = $y + $year_offset;
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Inventory\Inward\Model::toDataTable($owner),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('inward_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('inward_sort', 'order_date desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'customer_id', 'status', 'stock_status'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('order_no', 'company'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/inventory/model/inward/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'delete' => '{LNG_Delete}',
                    ),
                ),
                array(
                    'class' => 'button pink icon-plus',
                    'href' => $uri->createBackUri(array('module' => 'inventory-buy', 'id' => '0', 'typ' => $owner->status)),
                    'text' => '{LNG_Add New} '.$owner->typies[$owner->status],
                ),
            ),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => array(
                'MONTH(`order_date`)' => array(
                    'name' => 'month',
                    'text' => '{LNG_month}',
                    'options' => array(0 => '{LNG_all items}') + Language::get('MONTH_LONG'),
                    'default' => 0,
                    'value' => $owner->month,
                ),
                'YEAR(`order_date`)' => array(
                    'name' => 'year',
                    'text' => '{LNG_year}',
                    'options' => $years,
                    'default' => 0,
                    'value' => $owner->year,
                ),
            ),
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'order_date' => array(
                    'text' => '{LNG_Transaction date}',
                    'sort' => 'order_date',
                ),
                'order_no' => array(
                    'text' => '{LNG_Order No.}',
                    'sort' => 'order_no',
                ),
                'company' => array(
                    'text' => '{LNG_Supplier}',
                    'sort' => 'company',
                ),
                'total' => array(
                    'text' => '{LNG_Amount}',
                    'class' => 'center',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'total' => array(
                    'class' => 'right',
                ),
            ),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                array(
                    'class' => 'icon-print button brown notext',
                    'href' => WEB_URL.'export.php?module=inventory-export&typ=print&amp;id=:id',
                    'target' => '_export',
                    'title' => '{LNG_Print}',
                ),
                array(
                    'class' => 'icon-edit button green notext',
                    'href' => $uri->createBackUri(array('module' => 'inventory-buy', 'id' => ':id')),
                    'title' => '{LNG_Edit}',
                ),
            ),
        ));
        // save cookie
        setcookie('inward_perPage', $table->perPage, time() + 2592000, '/', null, HOST, true);
        setcookie('inward_sort', $table->sort, time() + 2592000, '/', null, HOST, true);

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
        if ($item['order_date'] == date('Y-m-d')) {
            $prop->class = 'bg3';
        }
        $item['order_date'] = Date::format($item['order_date'], 'd M Y');
        $item['total'] = Currency::format($item['total']);
        if ($item['customer_id'] == 0) {
            $item['company'] = '{LNG_Cash}';
        }

        return $item;
    }
}
