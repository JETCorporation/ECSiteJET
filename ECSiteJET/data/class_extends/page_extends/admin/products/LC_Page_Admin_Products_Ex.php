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

require_once CLASS_REALDIR . 'pages/admin/products/LC_Page_Admin_Products.php';

/**
 * 商品管理 のページクラス(拡張).
 *
 * LC_Page_Admin_Products をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Admin_Products_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Admin_Products_Ex extends LC_Page_Admin_Products
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();
        parent::init();
        $this->tpl_mainpage = 'products/index.tpl';
        $this->tpl_mainno = 'products';
        $this->tpl_subno = 'index';
        $this->tpl_pager = 'pager.tpl';
        $this->tpl_maintitle = '商品管理';
        $this->tpl_subtitle = '商品マスター';

        $masterData = new SC_DB_MasterData_Ex();
        $this->arrPageMax = $masterData->getMasterData('mtb_page_max');
        $this->arrDISP = $masterData->getMasterData('mtb_disp');
        $this->arrSTATUS = $masterData->getMasterData('mtb_status');
        $this->arrMaker = $masterData->getMasterData('mtb_allergy','id','name');
        $this->arrPRODUCTSTATUS_COLOR = $masterData->getMasterData('mtb_product_status_color');

        $objDate = new SC_Date_Ex();
        // 登録・更新検索開始年
        $objDate->setStartYear(RELEASE_YEAR);
        $objDate->setEndYear(DATE('Y'));
        $this->arrStartYear = $objDate->getYear();
        $this->arrStartMonth = $objDate->getMonth();
        $this->arrStartDay = $objDate->getDay();
        // 登録・更新検索終了年
        $objDate->setStartYear(RELEASE_YEAR);
        $objDate->setEndYear(DATE('Y'));
        $this->arrEndYear = $objDate->getYear();
        $this->arrEndMonth = $objDate->getMonth();
        $this->arrEndDay = $objDate->getDay();
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
    public function lfInitParam(&$objFormParam)
    {
    	// POSTされる値
    	$objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('カテゴリID', 'category_id', STEXT_LEN, 'n', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('ページ送り番号','search_pageno', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('表示件数', 'search_page_max', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));

    	// 検索条件
    	$objFormParam->addParam('商品ID', 'search_product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品コード', 'search_product_code', STEXT_LEN, 'KVna', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品名', 'search_name', STEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('カテゴリ', 'search_category_id', STEXT_LEN, 'n', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('種別', 'search_status', INT_LEN, 'n', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('アレルギー表示', 'search_allergy', INT_LEN, 'n', array( 'MAX_LENGTH_CHECK'));

    	// 登録・更新日
    	$objFormParam->addParam('開始年', 'search_startyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始月', 'search_startmonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('開始日', 'search_startday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了年', 'search_endyear', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了月', 'search_endmonth', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
    	$objFormParam->addParam('終了日', 'search_endday', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));

    	$objFormParam->addParam('商品ステータス', 'search_product_statuses', INT_LEN, 'n', array('MAX_LENGTH_CHECK'));
    }
    /**
     * クエリを構築する.
     *
     * 検索条件のキーに応じた WHERE 句と, クエリパラメーターを構築する.
     * クエリパラメーターは, SC_FormParam の入力値から取得する.
     *
     * 構築内容は, 引数の $where 及び $arrValues にそれぞれ追加される.
     *
     * @param  string       $key          検索条件のキー
     * @param  string       $where        構築する WHERE 句
     * @param  array        $arrValues    構築するクエリパラメーター
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param  SC_FormParam $objDb        SC_Helper_DB_Ex インスタンス
     * @return void
     */
    public function buildQuery($key, &$where, &$arrValues, &$objFormParam, &$objDb)
    {
    	$dbFactory = SC_DB_DBFactory_Ex::getInstance();
    	switch ($key) {
    		// 商品ID
    		case 'search_product_id':
    			$where .= ' AND product_id = ?';
    			$arrValues[] = sprintf('%d', $objFormParam->getValue($key));
    			break;
    			// 商品コード
    		case 'search_product_code':
    			$where .= ' AND product_id IN (SELECT product_id FROM dtb_products_class WHERE product_code ILIKE ? AND del_flg = 0)';
    			$arrValues[] = sprintf('%%%s%%', $objFormParam->getValue($key));
    			break;
    			// 商品名
    		case 'search_name':
    			$where .= ' AND name LIKE ?';
    			$arrValues[] = sprintf('%%%s%%', $objFormParam->getValue($key));
    			break;
    			// カテゴリ
    		case 'search_category_id':
    			list($tmp_where, $tmp_Values) = $objDb->sfGetCatWhere($objFormParam->getValue($key));
    			if ($tmp_where != '') {
    				$where.= ' AND product_id IN (SELECT product_id FROM dtb_product_categories WHERE ' . $tmp_where . ')';
    				$arrValues = array_merge((array) $arrValues, (array) $tmp_Values);
    			}
    			break;
    			// 種別
    		case 'search_status':
    			$tmp_where = '';
    			foreach ($objFormParam->getValue($key) as $element) {
    				if ($element != '') {
    					if (SC_Utils_Ex::isBlank($tmp_where)) {
    						$tmp_where .= ' AND (status = ?';
    					} else {
    						$tmp_where .= ' OR status = ?';
    					}
    					$arrValues[] = $element;
    				}
    			}

    			if (!SC_Utils_Ex::isBlank($tmp_where)) {
    				$tmp_where .= ')';
    				$where .= " $tmp_where ";
    			}
    			break;
    			// 登録・更新日(開始)
    		case 'search_startyear':
    			$date = SC_Utils_Ex::sfGetTimestamp($objFormParam->getValue('search_startyear'),
    			$objFormParam->getValue('search_startmonth'),
    			$objFormParam->getValue('search_startday'));
    			$where.= ' AND update_date >= ?';
    			$arrValues[] = $date;
    			break;
    			// 登録・更新日(終了)
    		case 'search_endyear':
    			$date = SC_Utils_Ex::sfGetTimestamp($objFormParam->getValue('search_endyear'),
    			$objFormParam->getValue('search_endmonth'),
    			$objFormParam->getValue('search_endday'), true);
    			$where.= ' AND update_date <= ?';
    			$arrValues[] = $date;
    			break;
    			// 商品ステータス
    		case 'search_product_statuses':
    			$arrPartVal = $objFormParam->getValue($key);
    			$count = count($arrPartVal);
    			if ($count >= 1) {
    				$where .= ' '
    						. 'AND product_id IN ('
    								. '    SELECT product_id FROM dtb_product_status WHERE product_status_id IN (' . SC_Utils_Ex::repeatStrWithSeparator('?', $count) . ')'
    										. ')';
    				$arrValues = array_merge($arrValues, $arrPartVal);
    			}
    			break;


    			case 'search_allergy':
    			list($tmp_where, $tmp_Values) = $objDb->sfGetCatWhere($objFormParam->getValue($key));
    			if ($tmp_where != '') {
    				$where.= ' AND product_id IN (SELECT product_id FROM dtb_allergy WHERE ' . $tmp_where . ')';
    				$arrValues = array_merge((array) $arrValues, (array) $tmp_Values);
    			}
    			break;



    		default:
    			break;
    	}
    }



}
