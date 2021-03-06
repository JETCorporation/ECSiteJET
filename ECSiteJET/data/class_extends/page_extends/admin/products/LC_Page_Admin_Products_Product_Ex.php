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

require_once CLASS_REALDIR . 'pages/admin/products/LC_Page_Admin_Products_Product.php';

/**
 * 商品登録 のページクラス(拡張).
 *
 * LC_Page_Admin_Products_Product をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Admin_Products_Product_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Admin_Products_Product_Ex extends LC_Page_Admin_Products_Product
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
    	parent::init();
    	$this->tpl_mainpage = 'products/product.tpl';
    	$this->tpl_mainno = 'products';
    	$this->tpl_subno = 'product';
    	$this->tpl_maintitle = '商品管理';
    	$this->tpl_subtitle = '商品登録';

    	$masterData = new SC_DB_MasterData_Ex();
    	$this->arrProductType = $masterData->getMasterData('mtb_product_type');
    	$this->arrDISP = $masterData->getMasterData('mtb_disp');
    	$this->arrSTATUS = $masterData->getMasterData('mtb_status');
    	$this->arrSTATUS_IMAGE = $masterData->getMasterData('mtb_status_image');
    	//追加
    	$this->arrALLE = $masterData->getMasterData('mtb_allergy');

    	$this->arrDELIVERYDATE = $masterData->getMasterData('mtb_delivery_date');
    	$this->arrMaker = SC_Helper_Maker_Ex::getIDValueList();
    	$this->arrAllowedTag = $masterData->getMasterData('mtb_allowed_tag');
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
    /**
     * パラメーター情報の初期化
     *
     * @param  object $objFormParam SC_FormParamインスタンス
     * @param  array  $arrPost      $_POSTデータ
     * @return void
     */
    public function lfInitFormParam(&$objFormParam, $arrPost)
    {
    	$objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品名', 'name', STEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品カテゴリ', 'category_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('公開・非公開', 'status', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('商品ステータス', 'product_status', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        //追加
    	$objFormParam->addParam('アレルギー', 'allergy', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));

    	if (!$arrPost['has_product_class']) {
    		// 新規登録, 規格なし商品の編集の場合
    		$objFormParam->addParam('商品種別', 'product_type_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('ダウンロード商品ファイル名', 'down_filename', STEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('ダウンロード商品実ファイル名', 'down_realfilename', MTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('temp_down_file', 'temp_down_file', '', '', array());
    		$objFormParam->addParam('save_down_file', 'save_down_file', '', '', array());
    		$objFormParam->addParam('商品コード', 'product_code', STEXT_LEN, 'KVna', array('EXIST_CHECK', 'SPTAB_CHECK','MAX_LENGTH_CHECK'));
    		$objFormParam->addParam(NORMAL_PRICE_TITLE, 'price01', PRICE_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam(SALE_PRICE_TITLE, 'price02', PRICE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		if (OPTION_PRODUCT_TAX_RULE) {
    			$objFormParam->addParam('消費税率', 'tax_rate', PERCENTAGE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		}
    		$objFormParam->addParam('在庫数', 'stock', AMOUNT_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('在庫無制限', 'stock_unlimited', INT_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	}
    	$objFormParam->addParam('商品送料', 'deliv_fee', PRICE_LEN, 'n', array('NUM_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('ポイント付与率', 'point_rate', PERCENTAGE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('発送日目安', 'deliv_date_id', INT_LEN, 'n', array('NUM_CHECK'));
    	$objFormParam->addParam('販売制限数', 'sale_limit', AMOUNT_LEN, 'n', array('SPTAB_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('メーカー', 'maker_id', INT_LEN, 'n', array('NUM_CHECK'));
    	$objFormParam->addParam('メーカーURL', 'comment1', URL_LEN, 'a', array('SPTAB_CHECK', 'URL_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('検索ワード', 'comment3', LLTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('備考欄(SHOP専用)', 'note', LLTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('一覧-メインコメント', 'main_list_comment', MTEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('詳細-メインコメント', 'main_comment', LLTEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('save_main_list_image', 'save_main_list_image', '', '', array());
    	$objFormParam->addParam('save_main_image', 'save_main_image', '', '', array());
    	$objFormParam->addParam('save_main_large_image', 'save_main_large_image', '', '', array());
    	$objFormParam->addParam('temp_main_list_image', 'temp_main_list_image', '', '', array());
    	$objFormParam->addParam('temp_main_image', 'temp_main_image', '', '', array());
    	$objFormParam->addParam('temp_main_large_image', 'temp_main_large_image', '', '', array());

    	for ($cnt = 1; $cnt <= PRODUCTSUB_MAX; $cnt++) {
    		$objFormParam->addParam('詳細-サブタイトル' . $cnt, 'sub_title' . $cnt, STEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('詳細-サブコメント' . $cnt, 'sub_comment' . $cnt, LLTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('save_sub_image' . $cnt, 'save_sub_image' . $cnt, '', '', array());
    		$objFormParam->addParam('save_sub_large_image' . $cnt, 'save_sub_large_image' . $cnt, '', '', array());
    		$objFormParam->addParam('temp_sub_image' . $cnt, 'temp_sub_image' . $cnt, '', '', array());
    		$objFormParam->addParam('temp_sub_large_image' . $cnt, 'temp_sub_large_image' . $cnt, '', '', array());
    	}

    	for ($cnt = 1; $cnt <= RECOMMEND_PRODUCT_MAX; $cnt++) {
    		$objFormParam->addParam('関連商品コメント' . $cnt, 'recommend_comment' . $cnt, LTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('関連商品ID' . $cnt, 'recommend_id' . $cnt, INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    		$objFormParam->addParam('recommend_delete' . $cnt, 'recommend_delete' . $cnt, '', 'n', array());
    	}

    	$objFormParam->addParam('商品ID', 'copy_product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));

    	$objFormParam->addParam('has_product_class', 'has_product_class', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
    	$objFormParam->addParam('product_class_id', 'product_class_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));

    	$objFormParam->setParam($arrPost);
    	$objFormParam->convParam();
    }
    /**
     * DBに商品データを登録する
     *
     * @param  object  $objUpFile   SC_UploadFileインスタンス
     * @param  object  $objDownFile SC_UploadFileインスタンス
     * @param  array   $arrList     フォーム入力パラメーター配列
     * @return integer 登録商品ID
     */
    public function lfRegistProduct(&$objUpFile, &$objDownFile, $arrList)
    {
    	$objQuery =& SC_Query_Ex::getSingletonInstance();
    	$objDb = new SC_Helper_DB_Ex();

    	// 配列の添字を定義
    	$checkArray = array('name', 'status',
    			'main_list_comment', 'main_comment',
    			'deliv_fee', 'comment1', 'comment2', 'comment3',
    			'comment4', 'comment5', 'comment6',
    			'sale_limit', 'deliv_date_id', 'maker_id', 'note');
    	$arrList = SC_Utils_Ex::arrayDefineIndexes($arrList, $checkArray);

    	// INSERTする値を作成する。
    	$sqlval['name'] = $arrList['name'];
    	$sqlval['status'] = $arrList['status'];
    	$sqlval['main_list_comment'] = $arrList['main_list_comment'];
    	$sqlval['main_comment'] = $arrList['main_comment'];
    	$sqlval['comment1'] = $arrList['comment1'];
    	$sqlval['comment2'] = $arrList['comment2'];
    	$sqlval['comment3'] = $arrList['comment3'];
    	$sqlval['comment4'] = $arrList['comment4'];
    	$sqlval['comment5'] = $arrList['comment5'];
    	$sqlval['comment6'] = $arrList['comment6'];
    	$sqlval['deliv_date_id'] = $arrList['deliv_date_id'];
    	$sqlval['maker_id'] = $arrList['maker_id'];
    	$sqlval['note'] = $arrList['note'];
    	$sqlval['update_date'] = 'CURRENT_TIMESTAMP';
    	$sqlval['creator_id'] = $_SESSION['member_id'];
    	$arrRet = $objUpFile->getDBFileList();
    	$sqlval = array_merge($sqlval, $arrRet);

    	for ($cnt = 1; $cnt <= PRODUCTSUB_MAX; $cnt++) {
    		$sqlval['sub_title'.$cnt] = $arrList['sub_title'.$cnt];
    		$sqlval['sub_comment'.$cnt] = $arrList['sub_comment'.$cnt];
    	}

    	$objQuery->begin();

    	// 新規登録(複製時を含む)
    	if ($arrList['product_id'] == '') {
    		$product_id = $objQuery->nextVal('dtb_products_product_id');
    		$sqlval['product_id'] = $product_id;

    		// INSERTの実行
    		$sqlval['create_date'] = 'CURRENT_TIMESTAMP';
    		$objQuery->insert('dtb_products', $sqlval);

    		$arrList['product_id'] = $product_id;

    		// カテゴリを更新
    		$objDb->updateProductCategories($arrList['category_id'], $product_id);
    		$objDb->updateProductAllergy($arrList['allergy'], $product_id);

    		// 複製商品の場合には規格も複製する
    		if ($arrList['copy_product_id'] != '' && SC_Utils_Ex::sfIsInt($arrList['copy_product_id'])) {
    			if (!$arrList['has_product_class']) {
    				//規格なしの場合、複製は価格等の入力が発生しているため、その内容で追加登録を行う
    				$this->lfCopyProductClass($arrList, $objQuery);
    			} else {
    				//規格がある場合の複製は複製元の内容で追加登録を行う
    				// dtb_products_class のカラムを取得
    				$dbFactory = SC_DB_DBFactory_Ex::getInstance();
    				$arrColList = $objQuery->listTableFields('dtb_products_class');
    				$arrColList_tmp = array_flip($arrColList);

    				// 複製しない列
    				unset($arrColList[$arrColList_tmp['product_class_id']]);     //規格ID
    				unset($arrColList[$arrColList_tmp['product_id']]);           //商品ID
    				unset($arrColList[$arrColList_tmp['create_date']]);

    				// 複製元商品の規格データ取得
    				$col = SC_Utils_Ex::sfGetCommaList($arrColList);
    				$table = 'dtb_products_class';
    				$where = 'product_id = ?';
    				$objQuery->setOrder('product_class_id');
    				$arrProductsClass = $objQuery->select($col, $table, $where, array($arrList['copy_product_id']));

    				// 規格データ登録
    				$objQuery =& SC_Query_Ex::getSingletonInstance();
    				foreach ($arrProductsClass as $arrData) {
    					$sqlval = $arrData;
    					$sqlval['product_class_id'] = $objQuery->nextVal('dtb_products_class_product_class_id');
    					$sqlval['deliv_fee'] = $arrList['deliv_fee'];
    					$sqlval['point_rate'] = $arrList['point_rate'];
    					$sqlval['sale_limit'] = $arrList['sale_limit'];
    					$sqlval['product_id'] = $product_id;
    					$sqlval['create_date'] = 'CURRENT_TIMESTAMP';
    					$sqlval['update_date'] = 'CURRENT_TIMESTAMP';
    					$objQuery->insert($table, $sqlval);
    				}
    			}
    		}
    		// 更新
    	} else {
    		$product_id = $arrList['product_id'];
    		// 削除要求のあった既存ファイルの削除
    		$arrRet = $this->lfGetProductData_FromDB($arrList['product_id']);
    		// TODO: SC_UploadFile::deleteDBFileの画像削除条件見直し要
    		$objImage = new SC_Image_Ex($objUpFile->temp_dir);
    		$arrKeyName = $objUpFile->keyname;
    		$arrSaveFile = $objUpFile->save_file;
    		$arrImageKey = array();
    		foreach ($arrKeyName as $key => $keyname) {
    			if ($arrRet[$keyname] && !$arrSaveFile[$key]) {
    				$arrImageKey[] = $keyname;
    				$has_same_image = $this->lfHasSameProductImage($arrList['product_id'], $arrImageKey, $arrRet[$keyname]);
    				if (!$has_same_image) {
    					$objImage->deleteImage($arrRet[$keyname], $objUpFile->save_dir);
    				}
    			}
    		}
    		$objDownFile->deleteDBDownFile($arrRet);
    		// UPDATEの実行
    		$where = 'product_id = ?';
    		$objQuery->update('dtb_products', $sqlval, $where, array($product_id));

    		// カテゴリを更新
    		$objDb->updateProductCategories($arrList['category_id'], $product_id);
    		$objDb->updateProductAllergy($arrList['allergy'], $product_id);
    	}

    	// 商品登録の時は規格を生成する。複製の場合は規格も複製されるのでこの処理は不要。
    	if ($arrList['copy_product_id'] == '') {
    		// 規格登録
    		if ($objDb->sfHasProductClass($product_id)) {
    			// 規格あり商品（商品規格テーブルのうち、商品登録フォームで設定するパラメーターのみ更新）
    			$this->lfUpdateProductClass($arrList);
    		} else {
    			// 規格なし商品（商品規格テーブルの更新）
    			$arrList['product_class_id'] = $this->lfInsertDummyProductClass($arrList);
    		}
    	}

    	// 商品ステータス設定
    	$objProduct = new SC_Product_Ex();
    	$objProduct->setProductStatus($product_id, $arrList['product_status']);

    	// 税情報設定
    	if (OPTION_PRODUCT_TAX_RULE && !$objDb->sfHasProductClass($product_id)) {
    		SC_Helper_TaxRule_Ex::setTaxRuleForProduct($arrList['tax_rate'], $arrList['product_id'], $arrList['product_class_id']);
    	}

    	// 関連商品登録
    	$this->lfInsertRecommendProducts($objQuery, $arrList, $product_id);

    	$objQuery->commit();

    	return $product_id;
    }

    /**
     * 規格を設定していない商品を商品規格テーブルに登録
     *
     * @param  array $arrList
     * @return void
     */
    public function lfInsertDummyProductClass($arrList)
    {
    	$objQuery =& SC_Query_Ex::getSingletonInstance();
    	$objDb = new SC_Helper_DB_Ex();

    	// 配列の添字を定義
    	$checkArray = array('product_class_id', 'product_id', 'product_code', 'stock', 'stock_unlimited', 'price01', 'price02', 'sale_limit', 'deliv_fee', 'point_rate' ,'product_type_id', 'down_filename', 'down_realfilename');
    	$sqlval = SC_Utils_Ex::sfArrayIntersectKeys($arrList, $checkArray);
    	$sqlval = SC_Utils_Ex::arrayDefineIndexes($sqlval, $checkArray);

    	$sqlval['stock_unlimited'] = $sqlval['stock_unlimited'] ? UNLIMITED_FLG_UNLIMITED : UNLIMITED_FLG_LIMITED;
    	$sqlval['creator_id'] = strlen($_SESSION['member_id']) >= 1 ? $_SESSION['member_id'] : '0';

    	if (strlen($sqlval['product_class_id']) == 0) {
    		$sqlval['product_class_id'] = $objQuery->nextVal('dtb_products_class_product_class_id');
    		$sqlval['create_date'] = 'CURRENT_TIMESTAMP';
    		$sqlval['update_date'] = 'CURRENT_TIMESTAMP';
    		// INSERTの実行
    		$objQuery->insert('dtb_products_class', $sqlval);
    	} else {
    		$sqlval['update_date'] = 'CURRENT_TIMESTAMP';
    		// UPDATEの実行
    		$objQuery->update('dtb_products_class', $sqlval, 'product_class_id = ?', array($sqlval['product_class_id']));
    	}
    	return $sqlval['product_class_id'];
    }


}

