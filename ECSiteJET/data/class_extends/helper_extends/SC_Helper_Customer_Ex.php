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

require_once CLASS_REALDIR . 'helper/SC_Helper_Customer.php';

/**
 * CSV関連のヘルパークラス(拡張).
 *
 * LC_Helper_Customer をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Helper
 * @author LOCKON CO.,LTD.
 * @version $Id:SC_Helper_DB_Ex.php 15532 2007-08-31 14:39:46Z nanasess $
 */
class SC_Helper_Customer_Ex extends SC_Helper_Customer{
	/**
	 * 会員情報の登録・編集処理を行う.
	 *
	 * @param array $arrData     登録するデータの配列（SC_FormParamのgetDbArrayの戻り値）
	 * @param array $customer_id nullの場合はinsert, 存在する場合はupdate
	 * @access public
	 * @return integer 登録編集したユーザーのcustomer_id
	 */
	public function sfEditCustomerData($arrData, $customer_id = null)
	{
		$objQuery =& SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();

		$old_version_flag = false;

		$arrData['update_date'] = 'CURRENT_TIMESTAMP';    // 更新日

		// salt値の生成(insert時)または取得(update時)。
		if (is_numeric($customer_id)) {
			$salt = $objQuery->get('salt', 'dtb_customer', 'customer_id = ? ', array($customer_id));

			// 旧バージョン(2.11未満)からの移行を考慮
			if (strlen($salt) === 0) {
				$old_version_flag = true;
			}
		} else {
			$salt = SC_Utils_Ex::sfGetRandomString(10);
			$arrData['salt'] = $salt;
		}
		//-- パスワードの更新がある場合は暗号化
		if ($arrData['password'] == DEFAULT_PASSWORD or $arrData['password'] == '') {
			//更新しない
			unset($arrData['password']);
		} else {
			// 旧バージョン(2.11未満)からの移行を考慮
			if ($old_version_flag) {
				$is_password_updated = true;
				$salt = SC_Utils_Ex::sfGetRandomString(10);
				$arrData['salt'] = $salt;
			}

			$arrData['password'] = SC_Utils_Ex::sfGetHashString($arrData['password'], $salt);
		}
		//-- 秘密の質問の更新がある場合は暗号化
		if ($arrData['reminder_answer'] == DEFAULT_PASSWORD or $arrData['reminder_answer'] == '') {
			//更新しない
			unset($arrData['reminder_answer']);

			// 旧バージョン(2.11未満)からの移行を考慮
			if ($old_version_flag && $is_password_updated) {
				// パスワードが更新される場合は、平文になっている秘密の質問を暗号化する
				$reminder_answer = $objQuery->get('reminder_answer', 'dtb_customer', 'customer_id = ? ', array($customer_id));
				$arrData['reminder_answer'] = SC_Utils_Ex::sfGetHashString($reminder_answer, $salt);
			}
		} else {
			// 旧バージョン(2.11未満)からの移行を考慮
			if ($old_version_flag && !$is_password_updated) {
				// パスワードが更新されない場合は、平文のままにする
				unset($arrData['salt']);
			} else {
				$arrData['reminder_answer'] = SC_Utils_Ex::sfGetHashString($arrData['reminder_answer'], $salt);
			}
		}

		//デフォルト国IDを追加
		if (FORM_COUNTRY_ENABLE == false) {
			$arrData['country_id'] = DEFAULT_COUNTRY_ID;
		}

		//-- 編集登録実行
		if (is_numeric($customer_id)) {
			// 編集
			$objQuery->update('dtb_customer', $arrData, 'customer_id = ? ', array($customer_id));
		} else {
			// 新規登録

			// 会員ID
			$customer_id = $objQuery->nextVal('dtb_customer_customer_id');
			$arrData['customer_id'] = $customer_id;

			do{
				$a = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10);


				if($a == true){
					$b = $objQuery->get("login_id","dtb_customer","login_id=?",array($a));

				}
			}while(TRUE ==$b);

			$arrData['login_id'] = $a;


			// 作成日
			if (is_null($arrData['create_date'])) {
				$arrData['create_date'] = 'CURRENT_TIMESTAMP';
			}
			$objQuery->insert('dtb_customer', $arrData);
		}

		$objQuery->commit();

		return array($customer_id, $arrData['login_id']);
	}




}
