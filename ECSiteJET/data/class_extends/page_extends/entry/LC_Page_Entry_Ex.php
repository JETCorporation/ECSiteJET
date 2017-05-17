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

require_once CLASS_REALDIR . 'pages/entry/LC_Page_Entry.php';

/**
 * 会員登録(入力ページ) のページクラス(拡張).
 *
 * LC_Page_Entry をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Entry_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Entry_Ex extends LC_Page_Entry
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

    public function lfSendMail($uniqid, $arrForm,$login_id)
    {


    	$CONF           = SC_Helper_DB_Ex::sfGetBasisData();

    	$objMailText    = new SC_SiteView_Ex();
    	$objMailText->setPage($this);
    	$objMailText->assign('CONF', $CONF);
    	$objMailText->assign('name01', $arrForm['name01']);
    	$objMailText->assign('name02', $arrForm['name02']);

    	//追加
    	$objMailText->assign( 'Log_id',$login_id);

    	$objMailText->assign('uniqid', $uniqid);

        $objMailText->assignobj($this);











    	$objHelperMail  = new SC_Helper_Mail_Ex();
    	$objHelperMail->setPage($this);

    	// 仮会員が有効の場合
    	if (CUSTOMER_CONFIRM_MAIL == true) {
    		$subject        = $objHelperMail->sfMakeSubject('会員登録のご確認');
    		$toCustomerMail = $objMailText->fetch('mail_templates/customer_mail.tpl');
    	} else {
    		$subject        = $objHelperMail->sfMakeSubject('会員登録のご完了');
    		$toCustomerMail = $objMailText->fetch('mail_templates/customer_regist_mail.tpl');



    	}

    	$objMail = new SC_SendMail_Ex();
    	$objMail->setItem(
    			''                    // 宛先
    			, $subject              // サブジェクト
    			, $toCustomerMail       // 本文
    			, $CONF['email03']      // 配送元アドレス
    			, $CONF['shop_name']    // 配送元 名前
    			, $CONF['email03']      // reply_to
    			, $CONF['email04']      // return_path
    			, $CONF['email04']      // Errors_to
    			, $CONF['email01']      // Bcc
    	);
    	// 宛先の設定
    	$objMail->setTo($arrForm['email'],
    			$arrForm['name01'] . $arrForm['name02'] .' 様');

    	$objMail->sendMail();
    }

    public function action()
    {
    	//決済処理中ステータスのロールバック
    	$objPurchase = new SC_Helper_Purchase_Ex();
    	$objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

    	$objFormParam = new SC_FormParam_Ex();

    	// PC時は規約ページからの遷移でなければエラー画面へ遷移する
    	if ($this->lfCheckReferer() === false) {
    		SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, '', true);
    	}

    	SC_Helper_Customer_Ex::sfCustomerEntryParam($objFormParam);
    	$objFormParam->setParam($_POST);

    	// mobile用（戻るボタンでの遷移かどうかを判定）
    	if (!empty($_POST['return'])) {
    		$_REQUEST['mode'] = 'return';
    	}

    	switch ($this->getMode()) {
    		case 'confirm':
    			if (isset($_POST['submit_address'])) {
    				// 入力エラーチェック
    				$this->arrErr = $this->lfCheckError($_POST);
    				// 入力エラーの場合は終了
    				if (count($this->arrErr) == 0) {
    					// 郵便番号検索文作成
    					$zipcode = $_POST['zip01'] . $_POST['zip02'];

    					// 郵便番号検索
    					$arrAdsList = SC_Utils_Ex::sfGetAddress($zipcode);

    					// 郵便番号が発見された場合
    					if (!empty($arrAdsList)) {
    						$data['pref'] = $arrAdsList[0]['state'];
    						$data['addr01'] = $arrAdsList[0]['city']. $arrAdsList[0]['town'];
    						$objFormParam->setParam($data);

    						// 該当無し
    					} else {
    						$this->arrErr['zip01'] = '※該当する住所が見つかりませんでした。<br>';
    					}
    				}
    				break;
    			}

    			//-- 確認
    			$this->arrErr = SC_Helper_Customer_Ex::sfCustomerEntryErrorCheck($objFormParam);
    			// 入力エラーなし
    			if (empty($this->arrErr)) {
    				//パスワード表示
    				$this->passlen      = SC_Utils_Ex::sfPassLen(strlen($objFormParam->getValue('password')));

    				$this->tpl_mainpage = 'entry/confirm.tpl';
    				$this->tpl_title    = '会員登録(確認ページ)';
    			}
    			break;
    		case 'complete':
    			//-- 会員登録と完了画面
    			$this->arrErr = SC_Helper_Customer_Ex::sfCustomerEntryErrorCheck($objFormParam);
    			if (empty($this->arrErr)) {
    				list($uniqid,$login_id)             = $this->lfRegistCustomerData($this->lfMakeSqlVal($objFormParam));

    				//追加
    				$this->lfSendMail($uniqid, $objFormParam->getHashArray(),$login_id);

    				// 仮会員が無効の場合
    				if (CUSTOMER_CONFIRM_MAIL == false) {
    					// ログイン状態にする
    					$objCustomer = new SC_Customer_Ex();
    					$objCustomer->setLogin($objFormParam->getValue('email'));
    				}

    				// 完了ページに移動させる。
    				SC_Response_Ex::sendRedirect('complete.php', array('ci' => SC_Helper_Customer_Ex::sfGetCustomerId($uniqid)));
    			}
    			break;
    		case 'return':
    			// quiet.
    			break;
    		default:
    			break;
    	}
    	$this->arrForm = $objFormParam->getFormParamList();
    }

    /**
     * 会員情報の登録
     *
     * @access private
     * @return uniqid
     */
    public function lfRegistCustomerData($sqlval)
    {
    	//追加
  	 list($customer_id,$login_id) =  SC_Helper_Customer_Ex::sfEditCustomerData($sqlval);

   return array($sqlval['secret_key'], $login_id);



    }


}
