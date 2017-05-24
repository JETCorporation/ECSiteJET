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

require_once CLASS_REALDIR . 'SC_Product.php';

class SC_Product_Ex extends SC_Product
    {
 public function getProductAllergy($proAller)
   {
       if (empty($proAller)) {
          return array();
      }
     $objQuery =& SC_Query_Ex::getSingletonInstance();
       $cols = 'product_id,allergy_id';
      $from = 'dtb_allergy';
      $where = 'del_flg=0 AND product_id IN  ('  . SC_Utils_Ex::repeatStrWithSeparator('?', count($proAller)). ')';
      $objQuery->setOrder("allergy_id");
       $productAllergy = $objQuery->select($cols, $from, $where, array($proAller));
       $results = array();

     foreach ($productAllergy as $Allergy) {
          $results[$Allergy['product_id']][] = $Allergy['allergy_id'];
      }


      return $results;


   }
   }
