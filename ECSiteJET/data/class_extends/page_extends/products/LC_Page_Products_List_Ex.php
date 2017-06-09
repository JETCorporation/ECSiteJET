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

require_once CLASS_REALDIR . 'pages/products/LC_Page_Products_List.php';

/**
 * LC_Page_Products_List のページクラス(拡張).
 *
 * LC_Page_Products_List をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Products_List_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Products_List_Ex extends LC_Page_Products_List
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();
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




    	//決済処理中ステータスのロールバック
    	$objPurchase = new SC_Helper_Purchase_Ex();
    	$objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

    	$objProduct = new SC_Product_Ex();
    	// パラメーター管理クラス
    	$objFormParam = new SC_FormParam_Ex();

    	// パラメーター情報の初期化
    	$this->lfInitParam($objFormParam);

    	// 値の設定
    	$objFormParam->setParam($_REQUEST);

    	// 入力値の変換
    	$objFormParam->convParam();

    	// 値の取得
    	$this->arrForm = $objFormParam->getHashArray();

    	//modeの取得
    	$this->mode = $this->getMode();

    	$arrST = $this->lfGetStatusId(intval($this->arrForm['status_id']));


    	//表示条件の取得
    	$this->arrSearchData = array(
    			'status_id'     => $arrST['id'],
    			'category_id'   => $this->lfGetCategoryId(intval($this->arrForm['category_id'])),
    			'maker_id'      => intval($this->arrForm['maker_id']),
    			'name'          => $this->arrForm['name']
    	);
    	$this->orderby = $this->arrForm['orderby'];


    	//ページング設定
    	$this->tpl_pageno   = $this->arrForm['pageno'];
    	$this->disp_number  = $this->lfGetDisplayNum($this->arrForm['disp_number']);

    	// 画面に表示するサブタイトルの設定
    	$this->tpl_subtitle = $this->lfGetPageTitle($this->mode, $this->arrSearchData['category_id'],$arrST['name']);



    	// 画面に表示する検索条件を設定
    	$this->arrSearch    = $this->lfGetSearchConditionDisp($this->arrSearchData);

    	// 商品一覧データの取得
    	$arrSearchCondition = $this->lfGetSearchCondition($this->arrSearchData);
    	$this->tpl_linemax  = $this->lfGetProductAllNum($arrSearchCondition);
    	$urlParam           = "category_id={$this->arrSearchData['category_id']}&pageno=#page#";
    	// モバイルの場合に検索条件をURLの引数に追加
    	if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_MOBILE) {
    		$searchNameUrl = urlencode(mb_convert_encoding($this->arrSearchData['name'], 'SJIS-win', 'UTF-8'));
    		$urlParam .= "&mode={$this->mode}&name={$searchNameUrl}&orderby={$this->orderby}";
    	}
    	$this->objNavi      = new SC_PageNavi_Ex($this->tpl_pageno, $this->tpl_linemax, $this->disp_number, 'eccube.movePage', NAVI_PMAX, $urlParam, SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE);
    	$this->arrProducts  = $this->lfGetProductsList($arrSearchCondition, $this->disp_number, $this->objNavi->start_row, $objProduct);

    	switch ($this->getMode()) {
    		case 'json':
    			$this->doJson($objProduct);
    			break;

    		default:
    			$this->doDefault($objProduct, $objFormParam);
    			break;
    	}

    	$this->tpl_rnd = SC_Utils_Ex::sfGetRandomString(3);
    }


    /**
     * ページタイトルの設定
     *
     * @return str
     */
    public function lfGetPageTitle($mode, $category_id = 0,$stname = null)
    {

    	if(!empty($stname)){
    		return $stname;
    	}

    	if ($mode == 'search') {
    		return '検索結果';
    	} elseif ($category_id == 0) {
    		return '全商品';



        } else {
    		$objCategory = new SC_Helper_Category_Ex();
    		$arrCat = $objCategory->get($category_id);

    		return $arrCat['category_name'];
    	}
    }




    public function lfInitParam(&$objFormParam)
    {
    	// 抽出条件
    	// XXX カートインしていない場合、チェックしていない
    	$objFormParam->addParam('カテゴリID', 'category_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('メーカーID', 'maker_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品ステータス', 'status_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品名', 'name', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('表示順序', 'orderby', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('ページ番号', 'pageno', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('表示件数', 'disp_number', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	// カートイン
    	$objFormParam->addParam('規格1', 'classcategory_id1', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('規格2', 'classcategory_id2', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('数量', 'quantity', INT_LEN, 'n', array('EXIST_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品規格ID', 'product_class_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    }





    public function lfGetStatusId($Status_id)
    {


    	// 指定なしの場合、0 を返す
    	if (empty($Status_id)) return 0;


    	$objQuery =& SC_Query_Ex::getSingletonInstance();
      	$sql = "SELECT * FROM mtb_status WHERE id = ? ";

    	$arrStatus_id = $objQuery->getAll($sql, array($Status_id));

       if (empty($arrStatus_id)) {
    		SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, 'この商品ステータスIDは存在しません。');;

    	}

    	return $arrStatus_id[0];
    }





    public function lfGetSearchCondition($arrSearchData)
    {
    	$searchCondition = array(
    			'where'             => '',
    			'arrval'            => array(),
    			'where_category'    => '',
    			'arrvalCategory'    => array()
    	);



    	// カテゴリからのWHERE文字列取得
    	if ($arrSearchData['category_id'] != 0) {
    		list($searchCondition['where_category'], $searchCondition['arrvalCategory']) = SC_Helper_DB_Ex::sfGetCatWhere($arrSearchData['category_id']);
    	}
    	// ▼対象商品IDの抽出
    	// 商品検索条件の作成（未削除、表示）
    	$searchCondition['where'] = SC_Product_Ex::getProductDispConditions('alldtl');

    	if (strlen($searchCondition['where_category']) >= 1) {
    		$searchCondition['where'] .= ' AND EXISTS (SELECT * FROM dtb_product_categories WHERE ' . $searchCondition['where_category'] . ' AND product_id = alldtl.product_id)';
    		$searchCondition['arrval'] = array_merge($searchCondition['arrval'], $searchCondition['arrvalCategory']);
    	}
    //追加

    	if ($arrSearchData['status_id']) {
    		$searchCondition['where'] .= 'AND product_id IN (SELECT product_id FROM dtb_product_status WHERE product_status_id = ? AND del_flg = 0)';
    		$searchCondition['arrval'][] = $arrSearchData['status_id'];
    	}
    	// 商品名をwhere文に
    	$name = $arrSearchData['name'];
    	$name = str_replace(',', '', $name);
    	// 全角スペースを半角スペースに変換
    	$name = str_replace('　', ' ', $name);
    	// スペースでキーワードを分割
    	$names = preg_split('/ +/', $name);
    	// 分割したキーワードを一つずつwhere文に追加
    	foreach ($names as $val) {
    		if (strlen($val) > 0) {
    			$searchCondition['where']    .= ' AND ( alldtl.name ILIKE ? OR alldtl.comment3 ILIKE ?) ';
    			$searchCondition['arrval'][]  = "%$val%";
    			$searchCondition['arrval'][]  = "%$val%";
    		}
    	}

    	// メーカーらのWHERE文字列取得
    	if ($arrSearchData['maker_id']) {
    		$searchCondition['where']   .= ' AND alldtl.maker_id = ? ';
    		$searchCondition['arrval'][] = $arrSearchData['maker_id'];
    	}

    	// 在庫無し商品の非表示
    	if (NOSTOCK_HIDDEN) {
    		$searchCondition['where'] .= ' AND EXISTS(SELECT * FROM dtb_products_class WHERE product_id = alldtl.product_id AND del_flg = 0 AND (stock >= 1 OR stock_unlimited = 1))';
    	}

    	// XXX 一時期内容が異なっていたことがあるので別要素にも格納している。
    	$searchCondition['where_for_count'] = $searchCondition['where'];

    	return $searchCondition;
    }








    public function lfGetSearchConditionDisp($arrSearchData)
    {
    	$objQuery   =& SC_Query_Ex::getSingletonInstance();
    	$arrSearch  = array('category' => '指定なし', 'status' => '指定なし','maker' => '指定なし', 'name' => '指定なし');

    	// カテゴリ検索条件
    	if ($arrSearchData['category_id'] > 0) {
    		$arrSearch['category']  = $objQuery->get('category_name', 'dtb_category', 'category_id = ?', array($arrSearchData['category_id']));
    	}


    	// カテゴリ検索条件
    	if ($arrSearchData['status_id'] > 0) {
    		$arrSearch['status']  = $objQuery->get('name', 'mtb_status', 'id = ?', array($arrSearchData['status_id']));
    	}


    	// メーカー検索条件
    	if (strlen($arrSearchData['maker_id']) > 0) {
    		$objMaker = new SC_Helper_Maker_Ex();
    		$maker = $objMaker->getMaker($arrSearchData['maker_id']);
    		$arrSearch['maker']     = $maker['name'];
    	}

    	// 商品名検索条件
    	if (strlen($arrSearchData['name']) > 0) {
    		$arrSearch['name']      = $arrSearchData['name'];
    	}

    	return $arrSearch;
    }

}
