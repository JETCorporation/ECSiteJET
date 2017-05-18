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

require_once CLASS_REALDIR . 'pages/admin/customer/LC_Page_Admin_Customer_Edit.php';

/**
 * 会員情報修正 のページクラス(拡張).
 *
 * LC_Page_Admin_Customer_Edit をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Admin_Customer_Edit_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Admin_Customer_Edit_Ex extends LC_Page_Admin_Customer_Edit
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
    //追加

    /**
     * パラメーター情報の初期化
     *
     * @param  array $objFormParam フォームパラメータークラス
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
    	// 会員項目のパラメーター取得
    	SC_Helper_Customer_Ex::sfCustomerEntryParam($objFormParam, true);
    	// 検索結果一覧画面への戻り用パラメーター
    	$objFormParam->addParam('検索用データ', 'search_data', '', '', array(), '', false);
    	// 会員購入履歴ページング用
    	$objFormParam->addParam('', 'search_pageno', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'), '', false);

    	//追加
    	$objFormParam->addParam('', 'login_id',15, 'a', array('ALNUM_CHECK'));//aは英数字を半角に変換

    }




}
