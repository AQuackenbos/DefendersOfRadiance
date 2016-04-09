<?php 
/**
Script for generating multi-level items easily

COLUMNS IN FILE:
0	=>	Identifier (no item_ prefix)
1	=>	Readable Name (for comment labels)
2	=>	Levels (Max)
3	=>	Tags
4	=>	Aliases
5	=>	Quality
6	=>	Cost
7	=>	AbilitySpecial (raw inner text)
8	=>	Modifiers (raw inner text)

*************************
!! Header Row Expected !!
*************************
**/

const DS		= '/';
$_LOADFILE		= 'items.csv';
$itemPath		= 'items';
$itemPrefix		= 'item_';
$recipePrefix	= 'item_recipe_';

$blockPrefix	= "\t\t";

$file = fopen($_LOADFILE,'r');

$_ITEM_TEMPLATE 	= file_get_contents('template-item.txt');
$_RECIPE_TEMPLATE	= file_get_contents('template-recipe.txt');

$_CURRENT_ID		= 2000; //Adjust as needed

$row = 0;
while(($data = fgetcsv($file)) !== false)
{
	if($row == 0)
	{
		//Header Row
		$row++;
		continue;
	}
	
	$item		= $data[0];
	$name		= $data[1];
	$max		= $data[2];
	$tags 		= $data[3];
	$aliases 	= $data[4];
	$quality 	= $data[5];
	$cost		= $data[6];
	$ability	= str_replace("\n","\n".$blockPrefix,$data[7]);
	$modifiers	= str_replace("\n","\n".$blockPrefix,$data[8]);
	
	echo 'Writing Item: '.$name."\n";
	for($currLevel = 1; $currLevel <= $max; $currLevel++)
	{
		//Create Item
		echo "\t".'Creating Item Level '.$currLevel."\n";
		
		$identifier = $itemPrefix.$item;
		
		if($currLevel > 1)
			$identifier .= '_'.$currLevel;
		
		$id			= $_CURRENT_ID++;
		
		$itemFile	= $itemPath.DS.$identifier.'.txt';
		$itemText	= str_replace(
			array('__NAME__','__ID__','__IDENTIFIER__','__COST__','__MAXLEVEL__','__LEVEL__','__TAGS__','__ALIASES__','__QUALITY__','__ABILITY__','__MODIFIERS__'),
			array($name.' Lv '.$currLevel,$id,$identifier,$cost,$max,$currLevel,$tags,$aliases,$quality,$ability,$modifiers),
			$_ITEM_TEMPLATE);
		
		file_put_contents($itemFile,$itemText);
		
		//Create Recipe
		if($currLevel == 1)
			continue;
		
		echo "\t".'Creating Recipe Level '.$currLevel."\n";
		
		$itemIdentifier		= $identifier;
		$previousIdentifier	= str_replace($currLevel,($currLevel-1),$itemIdentifier);
		
		if($currLevel == 2)
			$previousIdentifier = str_replace('_1','',$previousIdentifier);
		
		$identifier	= $recipePrefix.$item.'_'.$currLevel;
		$id			= $_CURRENT_ID++;
		
		$recipeFile	= $itemPath.DS.$identifier.'.txt';
		$recipeText	= str_replace(
			array('__NAME__','__ID__','__IDENTIFIER__','__ITEM_IDENTIFIER__','__COST__','__TAGS__','__PREVIOUS_IDENTIFIER__'),
			array($name.' Lv '.$currLevel,$id,$identifier,$itemIdentifier,$cost,$tags,$previousIdentifier),
			$_RECIPE_TEMPLATE);
			
		file_put_contents($recipeFile,$recipeText);
	}
	
	$row++;
}