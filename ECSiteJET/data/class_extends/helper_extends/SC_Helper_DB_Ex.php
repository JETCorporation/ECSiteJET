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

require_once CLASS_REALDIR . 'helper/SC_Helper_DB.php';

/**
 * DB関連のヘルパークラス(拡張).
 *
 * LC_Helper_DB をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Helper
 * @author LOCKON CO.,LTD.
 * @version $Id: SC_Helper_DB_Ex.php 22856 2013-06-08 07:35:27Z Seasoft $
 */
class SC_Helper_DB_Ex extends SC_Helper_DB
{
	/**
	 * 商品をカテゴリの先頭に追加する.
	 *
	 * @param  integer $category_id カテゴリID
	 * @param  integer $product_id  プロダクトID
	 * @return void
	 */
	public function addProductBeforAllergy($allergy_id, $product_id)
	{
		$objQuery =& SC_Query_Ex::getSingletonInstance();

		$sqlval = array('allergy_id' => $allergy_id,
				'product_id' => $product_id);

		$arrSql = array();
		$arrSql['rank'] = '(SELECT COALESCE(MAX(rank), 0) FROM dtb_allergy sub WHERE allergy_id = ?) + 1';

		$from_and_where = $objQuery->dbFactory->getDummyFromClauseSql();
		$from_and_where .= ' WHERE NOT EXISTS(SELECT * FROM dtb_allergy WHERE allergy_id = ? AND product_id = ?)';
		$objQuery->insert('dtb_allergy', $sqlval, $arrSql, array($allergy_id), $from_and_where, array($allergy_id, $product_id));
	}
	/**
	 * 商品カテゴリを更新する.
	 *
	 * @param  array   $arrCategory_id 登録するカテゴリIDの配列
	 * @param  integer $product_id     プロダクトID
	 * @return void
	 */
	public function updateProductAllergy($arrAllergy_id, $product_id)
	{
		$objQuery =& SC_Query_Ex::getSingletonInstance();

		// 現在のカテゴリ情報を取得
		$arrAllergy = $objQuery->getCol('allergy_id',
				'dtb_allergy',
				'product_id = ?',
				array($product_id));

		// 登録するカテゴリ情報と比較
		foreach ($arrAllergy as $allergy_id) {
			// 登録しないカテゴリを削除
			if (!in_array($allergy_id, $arrAllergy_id)) {
				$this->removeProductByAllergy($allergy_id, $product_id);
			}
		}

		// カテゴリを登録
		foreach ($arrAllergy_id as $allergy_id) {
			$this->addProductBeforAllergy($allergy_id, $product_id);
			SC_Utils_Ex::extendTimeOut();
		}
	}

}
