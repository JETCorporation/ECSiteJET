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

require_once CLASS_EX_REALDIR . 'page_extends/frontparts/bloc/LC_Page_FrontParts_Bloc_Ex.php';

/**
 * Recommend のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_FrontParts_Bloc_Best5 - Copy.php -1   $
 */
class LC_Page_FrontParts_Bloc_Order_Ster extends LC_Page_FrontParts_Bloc_Ex
{
	/**
	 * Page を初期化する.
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();
        $masterData = new SC_DB_MasterData_Ex();
       $this->arrSTATUS = $masterData->getMasterData('mtb_status');


	}

	/**
	 * Page のプロセス.
	 *
	 * @return void
	 */
	public function process()
	{

		$this->action();
		$this->sendResponse();
	}

	public function action()
	{



	}


	public function lfInitFormParam(&$objFormParam, $arrPost){
		$objFormParam->addParam('商品ステータス', 'product_status', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
		$objFormParam->setParam($arrPost);
		$objFormParam->convParam();
		}
}
