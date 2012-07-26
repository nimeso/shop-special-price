<?php
class ProductSpecialPriceDecorator extends DataObjectDecorator {

	/**
	 * standard SS method
	 *
	 */
	function extraStatics(){
		return array(
			"db" => array(
				'SpecialPrice' => 'Currency',
				'Discount' => 'Int'
			)
		);
	}
	
	function Price(){
		if($this->owner->SpecialPrice > 0){ // yes use special Price
			return $this->owner->SpecialPrice;
		}
		if($this->owner->Discount > 0){ // yes use special Price
			return $this->getSalePriceUsingPercentage();
		}
		return $this->owner->Price;
	}
	
	function NormalPrice(){
		$normalPrice = new Money();
		$normalPrice->setAmount((int) $this->owner->Price);
		return $normalPrice;
	}
	
	function updateUnitPrice(&$unitprice){
		$unitprice = 2;
		/*
		if($this->owner->SpecialPrice > 0){ // yes use special Price
			$unitprice = $this->owner->SpecialPrice;
		}
		if($this->owner->Discount > 0){ // yes use special Price
			$unitprice = $this->getSalePriceUsingPercentage();
		}
		*/
	}
	
	function getSalePriceUsingPercentage(){
		$price = $this->owner->Price;
		$discountPercentage = $this->owner->Discount;
		$discount = ($discountPercentage * $price)/100;
		$specialPrice = $this->owner->Price - $discount;
		return $specialPrice;
	}
	
	function getSavingsAsPercentage(){
		if($this->owner->SpecialPrice > 0){ // yes use special Price
			$discount = $this->owner->Price - $this->owner->SpecialPrice;
			return round( ($discount / $this->owner->Price)*100 )."%";
		}
		if($this->owner->Discount > 0){ // yes use special Price
			return $this->owner->Discount."%";
		}
	}
	
	function OnSpecial(){
		if($this->owner->SpecialPrice > 0){ // yes use special Price
			return true;
		}
		if($this->owner->Discount > 0){ // yes use special Price
			return true;
		}
	}

	/**
	 * standard SS method
	 *
	 */
	function updateCMSFields(FieldSet &$fields) {
		
		// is it a product or variation
		if($this->owner instanceOf SiteTree) {
			if(!$this->owner->Variations()->exists()){
				$tabName = "Root.Content.Main";
				$fieldName = "Weight";
				$fields->addFieldToTab($tabName, new NumericField('SpecialPrice','Special Price (0.00 will be ignored)','',12),$fieldName);
				$fields->addFieldToTab($tabName, new NumericField('Discount','Percentage Discount','',2),$fieldName);
			}
		} else { // it's a variation and hopefully has fields
			$fields->insertAfter(new NumericField('SpecialPrice','Special Price (0.00 will be ignored)','',12),"Price");
			$fields->insertAfter(new NumericField('Discount','Percentage Discount','',2),"SpecialPrice");
		}

	}
	
	function getLowestPricedVariation(){
		$vars = $this->owner->Variations();
		
		if($vars->exists()){
			$varPrices;
			foreach ($vars as $var){
				if($var->Price > 0){
					$varPrices[$var->ID] = $var->Price;
				}
			}
			if(!empty($varPrices)){
				$lowestVar = DataObject::get_by_id("ProductVariation",array_search(min($varPrices), $varPrices));
				return $lowestVar;
			}
		}
	}
	
	function getHigestPricedVariation(){
		$vars = $this->owner->Variations();
		if($vars){
			$varPrices;
			foreach ($vars as $var){
				if($var->Price > 0){
					$varPrices[$var->ID] = $var->Price;
				}
			}
			$lowestVar = DataObject::get_by_id("ProductVariation",array_search(max($varPrices), $varPrices));
			return $lowestVar;
		}
	}
	
}
