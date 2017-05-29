<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2013 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

require_once CLASS_REALDIR . 'pages/admin/order/LC_Page_Admin_Order.php';

/**
 * 受注管理 のページクラス(拡張).
 *
 * LC_Page_Admin_Order をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Admin_Order_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Admin_Order_Ex extends LC_Page_Admin_Order
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();
        $this->arrList = $this->lfGetSqlList();

    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process()
    {
        parent::process();
    }

    public function action()
    {
    	$objFormParam = new SC_FormParam_Ex();
    	$this->lfInitParam($objFormParam);
    	$objFormParam->setParam($_POST);

    	$this->arrHidden = $objFormParam->getSearchArray();
    	$this->arrForm = $objFormParam->getFormParamList();

    	$objPurchase = new SC_Helper_Purchase_Ex();

    	switch ($this->getMode()) {
    		// 削除
    		case 'delete':
    			$order_id = $objFormParam->getValue('order_id');
    			$objPurchase->cancelOrder($order_id, ORDER_CANCEL, true);
    			// 削除後に検索結果を表示するため breakしない

    			// 検索パラメーター生成後に処理実行するため breakしない
    		case 'csv':
    		case 'delete_all':
    		case 'csv_output':




    			// 検索パラメーターの生成
    		case 'search':
    			$objFormParam->convParam();
    			$objFormParam->trimParam();
    			$this->arrErr = $this->lfCheckError($objFormParam);
    			$arrParam = $objFormParam->getHashArray();

    			if (count($this->arrErr) == 0) {
    				$where = 'del_flg = 0';
    				$arrWhereVal = array();
    				foreach ($arrParam as $key => $val) {
    					if ($val == '') {
    						continue;
    					}
    					$this->buildQuery($key, $where, $arrWhereVal, $objFormParam);
    				}

    				$order = 'update_date DESC';

    				/* -----------------------------------------------
    				 * 処理を実行
    				* ----------------------------------------------- */
    				switch ($this->getMode()) {
    					// CSVを送信する。
    					case 'csv':
    						$this->doOutputCSV($where, $arrWhereVal, $order);

    						SC_Response_Ex::actionExit();
    						break;

    						// 全件削除(ADMIN_MODE)
    					case 'delete_all':
    						$page_max = 0;
    						$arrResults = $this->findOrders($where, $arrWhereVal,
    								$page_max, 0, $order);
    						foreach ($arrResults as $element) {
    							$objPurchase->cancelOrder($element['order_id'], ORDER_CANCEL, true);
    						}
    						break;
    				 case 'csv_output':


                	$this->lfDoCsvOutput($objFormParam->getValue('arrCsv'));

                    var_dump($objFormParam->getValue('arrCsv'));
                    exit();

                    SC_Response_Ex::actionExit();
               break;

    						// 検索実行
    					default:
    						// 行数の取得
    						$this->tpl_linemax = $this->getNumberOfLines($where, $arrWhereVal);
    						// ページ送りの処理
    						$page_max = SC_Utils_Ex::sfGetSearchPageMax($objFormParam->getValue('search_page_max'));
    						// ページ送りの取得
    						$objNavi = new SC_PageNavi_Ex($this->arrHidden['search_pageno'],
    								$this->tpl_linemax, $page_max,
    								'eccube.moveNaviPage', NAVI_PMAX);
    						$this->arrPagenavi = $objNavi->arrPagenavi;

    						// 検索結果の取得
    						$this->arrResults = $this->findOrders($where, $arrWhereVal,
    								$page_max, $objNavi->start_row, $order);
    						break;
    				}
    			}
    			break;
    		default:
    			break;
    	}

    }


    public function lfInitParam(&$objFormParam)
    {
    	$objFormParam->addParam('注文番号1', 'search_order_id1', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('注文番号2', 'search_order_id2', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('対応状況', 'search_order_status', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('注文者 お名前', 'search_order_name', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('注文者 お名前(フリガナ)', 'search_order_kana', STEXT_LEN, 'KVCa', array('KANA_CHECK','MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('性別', 'search_order_sex', INT_LEN, 'n', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('年齢1', 'search_age1', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('年齢2', 'search_age2', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('メールアドレス', 'search_order_email', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('TEL', 'search_order_tel', TEL_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('支払い方法', 'search_payment_id', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('購入金額1', 'search_total1', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('購入金額2', 'search_total2', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('表示件数', 'search_page_max', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('選択CSV出力', 'arrCsv', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));


    	// 受注日
    	$objFormParam->addParam('開始年', 'search_sorderyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始月', 'search_sordermonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始日', 'search_sorderday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了年', 'search_eorderyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了月', 'search_eordermonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了日', 'search_eorderday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	// 更新日
    	$objFormParam->addParam('開始年', 'search_supdateyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始月', 'search_supdatemonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始日', 'search_supdateday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了年', 'search_eupdateyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了月', 'search_eupdatemonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了日', 'search_eupdateday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	// 生年月日
    	$objFormParam->addParam('開始年', 'search_sbirthyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始月', 'search_sbirthmonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始日', 'search_sbirthday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了年', 'search_ebirthyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了月', 'search_ebirthmonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了日', 'search_ebirthday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('購入商品','search_product_name',STEXT_LEN,'KVa',array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('ページ送り番号','search_pageno', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('受注ID', 'order_id', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    }


    public function lfGetSqlData(&$objFormParam)
    {
    	// 編集中データがある場合
    	if (!SC_Utils_Ex::isBlank($objFormParam->getValue('sql_name'))
    	|| !SC_Utils_Ex::isBlank($objFormParam->getValue('csv_sql'))
    	) {
    		return $objFormParam->getHashArray();
    	}
    	$sql_id = $objFormParam->getValue('sql_id');
    	if (!SC_Utils_Ex::isBlank($sql_id)) {
    		$arrData = $this->lfGetSqlCsv('sql_id = ?', array($sql_id));

    		return $arrData[0];
    	}

    	return array();
    }



public function lfDoCsvOutput($sql_id)
    {
        $objCSV = new SC_Helper_CSV_Ex();

        $arrData = $this->lfGetSqlCsv('sql_id = ?', array($sql_id));

        $sql = 'SELECT  ' . $arrData[0]['csv_sql'];
;

        $objCSV->sfDownloadCsvFromSql($sql, array(), 'order', null, true);
        SC_Response_Ex::actionExit();
    }

    public function lfGetSqlCsv($where = '' , $arrVal = array())
    {
    	$objQuery =& SC_Query_Ex::getSingletonInstance();
    	$table = 'dtb_csv_sql';
    	$objQuery->setOrder('sql_id');

    	return $objQuery->select('*', $table, $where, $arrVal);
    }


    public function lfGetSqlList()
    {
    	$objQuery =& SC_Query_Ex::getSingletonInstance();
    	$table = 'dtb_csv_sql';
    	$objQuery->setOrder('sql_id');
    	$then=  $objQuery->select('*', $table, $where, $arrVal);
        $isthis = NULL;
        foreach($then as $akk){
        	$isthis[$akk["sql_id"]] = $akk["sql_name"];


        }
    	return $isthis;
    }


}
