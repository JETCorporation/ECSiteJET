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

require_once CLASS_REALDIR . 'pages/products/LC_Page_Products_Detail.php';

/**
 * LC_Page_Products_Detail のページクラス(拡張).
 *
 * LC_Page_Products_Detail をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Products_Detail_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Products_Detail_Ex extends LC_Page_Products_Detail
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
    	parent::init();
    	$masterData = new SC_DB_MasterData_Ex();
    	$this->arrSTATUS = $masterData->getMasterData('mtb_status');
    	$this->arrSTATUS_IMAGE = $masterData->getMasterData('mtb_status_image');
    	//追加
    	$this->arrALLE = $masterData->getMasterData('mtb_allergy');
    	$this->arrDELIVERYDATE = $masterData->getMasterData('mtb_delivery_date');
    	$this->arrRECOMMEND = $masterData->getMasterData('mtb_recommend');

    	// POST に限定する mode
    	$this->arrLimitPostMode[] = 'cart';
    	$this->arrLimitPostMode[] = 'add_favorite';
    	$this->arrLimitPostMode[] = 'add_favorite_sphone';
    	$this->arrLimitPostMode[] = 'select';
    	$this->arrLimitPostMode[] = 'select2';
    	$this->arrLimitPostMode[] = 'selectItem';
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
     * Page のAction.
     *
     * @return void
     */
    public function action()
    {
    	//決済処理中ステータスのロールバック
    	$objPurchase = new SC_Helper_Purchase_Ex();
    	$objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

    	// 会員クラス
    	$objCustomer = new SC_Customer_Ex();

    	// パラメーター管理クラス
    	$this->objFormParam = new SC_FormParam_Ex();
    	// パラメーター情報の初期化
    	$this->arrForm = $this->lfInitParam($this->objFormParam);
    	// ファイル管理クラス
    	$this->objUpFile = new SC_UploadFile_Ex(IMAGE_TEMP_REALDIR, IMAGE_SAVE_REALDIR);
    	// ファイル情報の初期化
    	$this->objUpFile = $this->lfInitFile($this->objUpFile);

    	// プロダクトIDの正当性チェック
    	$product_id = $this->lfCheckProductId($this->objFormParam->getValue('admin'), $this->objFormParam->getValue('product_id'));
    	$this->mode = $this->getMode();

    	$objProduct = new SC_Product_Ex();
    	$objProduct->setProductsClassByProductIds(array($product_id));

    	// 規格1クラス名
    	$this->tpl_class_name1 = $objProduct->className1[$product_id];

    	// 規格2クラス名
    	$this->tpl_class_name2 = $objProduct->className2[$product_id];

    	// 規格1
    	$this->arrClassCat1 = $objProduct->classCats1[$product_id];

    	// 規格1が設定されている
    	$this->tpl_classcat_find1 = $objProduct->classCat1_find[$product_id];
    	// 規格2が設定されている
    	$this->tpl_classcat_find2 = $objProduct->classCat2_find[$product_id];

    	$this->tpl_stock_find = $objProduct->stock_find[$product_id];
    	$this->tpl_product_class_id = $objProduct->classCategories[$product_id]['__unselected']['__unselected']['product_class_id'];
    	$this->tpl_product_type = $objProduct->classCategories[$product_id]['__unselected']['__unselected']['product_type'];

    	// 在庫が無い場合は、OnLoadしない。(javascriptエラー防止)
    	if ($this->tpl_stock_find) {
    		// 規格選択セレクトボックスの作成
    		$this->js_lnOnload .= $this->lfMakeSelect();
    	}

    	$this->tpl_javascript .= 'eccube.classCategories = ' . SC_Utils_Ex::jsonEncode($objProduct->classCategories[$product_id]) . ';';
    	$this->tpl_javascript .= 'function lnOnLoad()
        {' . $this->js_lnOnload . '}';
    	$this->tpl_onload .= 'lnOnLoad();';

    	// モバイル用 規格選択セレクトボックスの作成
    	if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
    		$this->lfMakeSelectMobile($this, $product_id, $this->objFormParam->getValue('classcategory_id1'));
    	}

    	// 商品IDをFORM内に保持する
    	$this->tpl_product_id = $product_id;

    	switch ($this->mode) {
    		case 'cart':
    			$this->doCart();
    			break;

    		case 'add_favorite':
    			$this->doAddFavorite($objCustomer);
    			break;

    		case 'add_favorite_sphone':
    			$this->doAddFavoriteSphone($objCustomer);
    			break;

    		case 'select':
    		case 'select2':
    		case 'selectItem':
    			/**
    			 * モバイルの数量指定・規格選択の際に、
    			 * $_SESSION['cart_referer_url'] を上書きさせないために、
    			 * 何もせずbreakする。
    			 */
    			break;

    		default:
    			$this->doDefault();
    			break;
    	}

    	// モバイル用 ポストバック処理
    	if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
    		switch ($this->mode) {
    			case 'select':
    				$this->doMobileSelect();
    				break;

    			case 'select2':
    				$this->doMobileSelect2();
    				break;

    			case 'selectItem':
    				$this->doMobileSelectItem();
    				break;

    			case 'cart':
    				$this->doMobileCart();
    				break;

    			default:
    				$this->doMobileDefault();
    				break;
    		}
    	}

    	// 商品詳細を取得
    	$this->arrProduct = $objProduct->getDetail($product_id);

    	// サブタイトルを取得
    	$this->tpl_subtitle = $this->arrProduct['name'];

    	// 関連カテゴリを取得
    	$this->arrRelativeCat = SC_Helper_DB_Ex::sfGetMultiCatTree($product_id);

    	$this->ProductAllergy =  $objProduct->getProductAllergy($proAller);



    	// 商品ステータスを取得
    	$this->productStatus = $objProduct->getProductStatus($product_id);

    	// 画像ファイル指定がない場合の置換処理
    	$this->arrProduct['main_image']= SC_Utils_Ex::sfNoImageMain($this->arrProduct['main_image']);

    	$this->subImageFlag = $this->lfSetFile($this->objUpFile, $this->arrProduct, $this->arrFile);
    	//レビュー情報の取得
    	$this->arrReview = $this->lfGetReviewData($product_id);

    	//関連商品情報表示
    	$this->arrRecommend = $this->lfPreGetRecommendProducts($product_id);

    	// ログイン判定
    	if ($objCustomer->isLoginSuccess() === true) {
    		//お気に入りボタン表示
    		$this->tpl_login = true;
    		$this->is_favorite = SC_Helper_DB_Ex::sfDataExists('dtb_customer_favorite_products', 'customer_id = ? AND product_id = ?', array($objCustomer->getValue('customer_id'), $product_id));
    	}
    }

}
