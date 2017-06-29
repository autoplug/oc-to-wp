<?php
$wp_content = '/users/apple/desktop/wordpress/wp-content/uploads/';
require_once ("mysqli.php");

$language_id = 2;
$store_id = 0;

//Define db connection
$oc = new db("localhost","root","root","opencart");
$wp = new db("localhost","root","root","wordpress");

function htmltext($html){
	$html = htmlspecialchars_decode($html);
	$html = str_replace('&nbsp;',' ',$html);
	$result = strip_tags($html,"<a>");
	return $result;
}

function attachment($path,$destination="2017/06/"){
	if(!$path) return 0;

	global $wp;
	$wp_content = '/users/apple/desktop/wordpress/wp-content/uploads/';	

	if (!is_dir($wp_content . $destination)){
	    mkdir($wp_content . $destination, 0777, true);
	}

	$ext = substr($path,strripos($path,'.') + 1);
	$image = substr($path,strripos($path,'/') + 1);
	$image = substr($image,0,-(strlen($ext)+1));

	$attach_id = 0;
	if( file_exists($path) && copy($path,$wp_content . $destination . $image . '.' . $ext)){
		$wp->query('INSERT INTO wp_posts (post_author,post_date,post_date_gmt,post_title,post_status,comment_status,ping_status,post_name,post_modified,post_modified_gmt,guid,post_type,post_mime_type) VALUES (1,NOW(),NOW(),"'.$image.'","inherit","closed","closed","'.$image.'",NOW(),NOW(),"http://localhost:8090/wordpress/wp-content/uploads/2016/06/'.$image. '.' . $ext .'","attachment","image/'.$ext.'")');
		$attach_id = $wp->lastId();
		$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$attach_id.',"_wp_attached_file","'. $destination . $image.'.'.$ext.'")');
		list($width, $height, $type, $attr) = getimagesize($path);
		$s_image = '';
		if($width < 200){
			$fullname = $destination . $image . '.' . $ext;
			$s_image = 'a:5:{s:5:"width";i:'.$width.';s:6:"height";i:'.$height.';s:4:"file";s:13:"'.$fullname.'";s:5:"sizes";a:0:{}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"1";s:8:"keywords";a:0:{}}}';
		}else{
			$fullname = $destination . $image . '.' . $ext;
			$s_image = 'a:5:{s:5:"width";i:'.$width.';s:6:"height";i:'.$height.';s:4:"file";s:35:"'.$fullname.'";s:5:"sizes";a:7:{s:9:"thumbnail";a:4:{s:4:"file";s:35:"'.$image.'-150x150.jpg";s:5:"width";i:150;s:6:"height";i:150;s:9:"mime-type";s:10:"image/'.$ext.'";}s:6:"medium";a:4:{s:4:"file";s:35:"'.$image.'-216x300.'.$ext.'";s:5:"width";i:216;s:6:"height";i:300;s:9:"mime-type";s:10:"image/'.$ext.'";}s:12:"medium_large";a:4:{s:4:"file";s:20:"'.$image.'-768x480.'.$ext.'";s:5:"width";i:768;s:6:"height";i:480;s:9:"mime-type";s:10:"image/'.$ext.'";}s:5:"large";a:4:{s:4:"file";s:21:"'.$image.'-1024x640.jpg";s:5:"width";i:1024;s:6:"height";i:640;s:9:"mime-type";s:10:"image/'.$ext.'";}s:14:"shop_thumbnail";a:4:{s:4:"file";s:35:"'.$image.'-180x180.'.$ext.'";s:5:"width";i:180;s:6:"height";i:180;s:9:"mime-type";s:10:"image/'.$ext.'";}s:12:"shop_catalog";a:4:{s:4:"file";s:35:"'.$image.'-300x300.'.$ext.'";s:5:"width";i:300;s:6:"height";i:300;s:9:"mime-type";s:10:"image/'.$ext.'";}s:11:"shop_single";a:4:{s:4:"file";s:20:"'.$image.'-600x600.'.$ext.'";s:5:"width";i:600;s:6:"height";i:600;s:9:"mime-type";s:10:"image/'.$ext.'";}}s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}';
		}
		$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$attach_id.',"_wp_attachment_metadata","'. $wp->escape($s_image).'")');
	}
	return $attach_id;
}


function redirection($from,$to){
	global $wp;
	$wp->query("INSERT INTO wp_redirection_items (url,group_id,status,action_type,action_code,action_data,match_type) VALUES ('$from',1,'enabled','url',301,'$to','url')");						
}


//post remove
$wp->query('DELETE FROM wp_posts WHERE ID > 11');
$wp->query('DELETE FROM wp_postmeta WHERE post_id > 11');

//comments remove
$wp->query('DELETE FROM wp_comments');
$wp->query('DELETE FROM wp_commentmeta');

//category remove
$wp->query('DELETE FROM wp_terms');
$wp->query('DELETE FROM wp_termmeta');
$wp->query('DELETE FROM wp_term_taxonomy');
$wp->query('DELETE FROM wp_term_relationships');


//add header filter
$wp->query("DELETE FROM wp_woocommerce_attribute_taxonomies");
$wp->query("DELETE FROM wp_options WHERE option_name = '_transient_wc_attribute_taxonomies'");
$q = $oc->query("SELECT * FROM hyfhksipw_filter_group_description LEFT JOIN hyfhksipw_filter_group ON hyfhksipw_filter_group_description.filter_group_id = hyfhksipw_filter_group.filter_group_id WHERE hyfhksipw_filter_group_description.language_id = $language_id");
foreach ($q->rows as $row) {
	$wp->query('INSERT INTO wp_woocommerce_attribute_taxonomies (attribute_name,attribute_label,attribute_type,attribute_orderby,attribute_public) VALUES ("'. str_replace(' ', '-',trim(substr(trim($row["name"]),0,28))).'","'. trim(substr(trim($row["name"]),0,28)).'","select","menu_order",0)');
	$wp->query('INSERT INTO wp_term_taxonomy (term_id,taxonomy) VALUES ('.$wp->lastId().',"pa_'. str_replace(' ', '-', trim(substr(trim($row["name"]),0,28))) .'")');
	$oc->query("SELECT * FROM hyfhksipw_filter_description WHERE filter_group_id=" . $row['filter_group_id'] . " AND language_id=$language_id");
	foreach($oc->fetch("name") as $name){
		$slug = str_replace(' ', '-', trim(substr(trim($name),0,28)));
		$wp->query("INSERT INTO wp_terms (name,slug) VALUES ('$name', '$slug')");

		$s = str_replace(' ', '-', trim(substr(trim($row['name']),0,28)));
		$wp->query("INSERT INTO wp_term_taxonomy (term_id,taxonomy) VALUES (".$wp->lastId().",'pa_". $s ."')");
	}
}



//Add filter to wp_options
$q = $wp->query("SELECT * FROM wp_woocommerce_attribute_taxonomies");
$option = [];
foreach($q->rows as $row){
	$attribute = new stdClass();
	$attribute->attribute_id = $row['attribute_id'];
	$attribute->attribute_name = $row['attribute_name'];
	$attribute->attribute_label = $row['attribute_label'];
	$attribute->attribute_type = $row['attribute_type'];
	$attribute->attribute_orderby = $row['attribute_orderby'];
	$attribute->attribute_public = $row['attribute_public'];
	$option[] = $attribute;
}
$wp->query('INSERT INTO wp_options (option_name,option_value) VALUES ("_transient_wc_attribute_taxonomies","'. $wp->escape(serialize($option)) .'")');




//add category brand
$wp->query('INSERT INTO wp_terms (name,slug) VALUES ("Brand","brand")');
$term_id = $wp->lastId();
$wp->query('INSERT INTO wp_term_taxonomy (term_id,taxonomy) VALUES ('. $term_id .',"product_cat")');
$manufacturer = $oc->query('SELECT * FROM hyfhksipw_manufacturer');
foreach ($manufacturer->rows as $value) {
	$value['name'] = trim($value['name']);
	$wp->query('INSERT INTO wp_terms (name,slug) VALUES ("'. $value['name'] .'","'. str_replace(' ','-', trim($value['name'])) .'")');
	$last_term_id = $wp->lastId();
	$wp->query('INSERT INTO wp_term_taxonomy (term_id,taxonomy,parent) VALUES ('. $wp->lastId() .',"product_cat",'. $term_id .')');
	
	//copy image manufacture
	if($attach_id = attachment($value['image'],'2016/06/') ){
		$wp->query('INSERT INTO wp_termmeta (term_id,meta_key,meta_value) VALUES ('.$last_term_id.',"thumbnail_id",'.$attach_id.')');
		$wp->query('INSERT INTO wp_termmeta (term_id,meta_key,meta_value) VALUES ('.$last_term_id.',"display_type","")');
	}
}



//add product to wp
$query = $oc->query("SELECT * FROM hyfhksipw_product ORDER BY product_id ASC");
foreach ($query->rows as $value) {
	$product_id = $value['product_id'];

	$q = $oc->query("SELECT store_id FROM hyfhksipw_product_to_store WHERE product_id = $product_id" );
	if(isset($q->row['store_id']) && $q->row['store_id'] != $store_id)
		continue;

	//add products
	$wp->query('INSERT INTO wp_posts (post_date,post_date_gmt,post_modified,post_modified_gmt,post_type,ping_status,post_author) VALUES ("'. $value['date_added'] .'","'.$value['date_added'].'","'.$value['date_modified'].'","'. $value['date_modified'] .'","product","closed",1)');
	$post_id = $wp->lastId();
	echo "product_id : $product_id ...... post_id : $post_id <br>";


	$q = $oc->query("SELECT * FROM hyfhksipw_product_description WHERE product_id = $product_id AND language_id = $language_id" );
	$wp->query('UPDATE wp_posts SET post_title = "' .  $q->row['name'] .'" WHERE ID = '. $post_id );
	$wp->query('UPDATE wp_posts SET post_content = "' . $wp->escape( htmltext($q->row['description']) ) .'" WHERE ID = '. $post_id );
	$q = $oc->query('SELECT * FROM hyfhksipw_url_alias WHERE query="product_id='.$product_id.'" AND store_id='.$store_id);
	if($oc->count()){
		$wp->query('UPDATE wp_posts SET post_name = "' . str_replace(' ','-',trim($q->row['keyword']))  .'" WHERE ID = '. $post_id );
	}

	//postmeta for product
	$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_regular_price",'.intval($value['price']).')');
	$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_price",'.intval($value['price']).')');
	$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_sale_price","")');
	$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_manage_stock","yes")');
	$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_stock",'.$value['quantity'].')');
	
	//special price for product
	$q = $oc->query('SELECT * FROM hyfhksipw_product_special WHERE product_id=' . $product_id);
	if($oc->count()){
		$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('. $post_id .',"_sale_price",'. $q->row['price'] .')');
	}
	

	//comments
	$q = $oc->query('SELECT * FROM hyfhksipw_review WHERE product_id=' . $product_id . ' AND store_id=' . $store_id);
	if(isset($q->row['review_id'])){
		$wp->query('INSERT INTO wp_comments (comment_author,comment_content,comment_post_ID,comment_date,comment_date_gmt) VALUES ("'.$q->row['author'].'","'. $q->row['text'] .'",'.$post_id.',"'.$q->row['date_added'].'","'.$q->row['date_added'].'")');
	}

	//add category to product(manufacturer)
	$q = $oc->query('SELECT * FROM hyfhksipw_product WHERE product_id=' . $product_id);
	if($q->row['manufacturer_id']){
		$q = $oc->query('SELECT * FROM hyfhksipw_manufacturer WHERE manufacturer_id=' . $q->row['manufacturer_id']);
		$q = $wp->query('SELECT * FROM wp_terms WHERE name="' . $wp->escape($q->row['name']) . '"');
		$q = $wp->query('SELECT * FROM wp_term_taxonomy WHERE term_id=' . $q->row['term_id']);
		$wp->query('INSERT INTO wp_term_relationships (object_id,term_taxonomy_id) VALUES ('. $post_id .','. $q->row['term_taxonomy_id'] .')');
	}
	
	//Add brand to product
	$q = $wp->query('SELECT * FROM wp_term_taxonomy WHERE term_id=' . $term_id);
	$wp->query('INSERT INTO wp_term_relationships (object_id,term_taxonomy_id) VALUES ('. $post_id .','. $q->row['term_taxonomy_id'] .')');

	//Add Image to products feature image
	$q = $oc->query('SELECT * FROM hyfhksipw_product WHERE product_id=' . $product_id);
	if( $attach_id = attachment($value['image'],'2017/06/') ){
		$wp->query('UPDATE wp_posts SET post_parent=' . $post_id .' WHERE ID=' . $attach_id);
		$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_thumbnail_id",'.$attach_id.')');
	}

	//add image to gallery product
	$q = $oc->query('SELECT * FROM hyfhksipw_product_image WHERE product_id='.$product_id);
	if($oc->count()){
		foreach($q->rows as $row){
			if($attach_id = attachment($row['image'],'2017/06/')){
				$wp->query('UPDATE wp_posts SET post_parent=' . $post_id .' WHERE ID=' . $attach_id);
				$wp->query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ('.$post_id.',"_product_image_gallery",'.$attach_id.')');
			}
		}
	}

	
	//add filter header to product
	$oc->query("SELECT * FROM hyfhksipw_filter LEFT JOIN hyfhksipw_product_filter ON hyfhksipw_product_filter.filter_id = hyfhksipw_filter.filter_id LEFT JOIN hyfhksipw_filter_group_description ON hyfhksipw_filter_group_description.filter_group_id=hyfhksipw_filter.filter_group_id WHERE hyfhksipw_product_filter.product_id = $product_id GROUP BY hyfhksipw_filter.filter_group_id");
	if($oc->count()){
		$s = [];
		foreach($oc->fetch("name") as $name){
			$name = str_replace(' ', '-', trim(substr(trim($name),0,28)));
			$s['pa_' . $name ] =  array ('name' => 'pa_' . $name ,'value' => '','position' => 0,'is_visible' => 1,'is_variation' => 0,'is_taxonomy' => 1);
		}
		$wp->query("DELETE FROM wp_postmeta WHERE meta_key='_product_attributes' AND post_id=$post_id");
		$wp->query("INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES ($post_id,'_product_attributes','" . $wp->escape( serialize($s) ) . "')" );
		
		//Add filter sub to product
		$oc->query("SELECT hyfhksipw_filter_description.name AS name FROM hyfhksipw_product_filter LEFT JOIN hyfhksipw_filter_description ON hyfhksipw_product_filter.filter_id = hyfhksipw_filter_description.filter_id  WHERE hyfhksipw_product_filter.product_id=$product_id AND hyfhksipw_filter_description.language_id=$language_id");
		foreach ($oc->fetch("name") as $name) {
			$wp->query("SELECT wp_term_taxonomy.term_taxonomy_id AS term_taxonomy_id FROM wp_terms LEFT JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id WHERE wp_terms.name='$name'");
			$wp->query("INSERT INTO wp_term_relationships (object_id,term_taxonomy_id) VALUES ($post_id,". $wp->fetch('term_taxonomy_id')[0] .")");	
		}
	}





	//add tag to product
	$q = $oc->query('SELECT * FROM hyfhksipw_product_description WHERE product_id=' . $product_id . ' AND language_id=' . $language_id);
	if($q->row['tag']){
		$a = explode(",", $q->row['tag'] );
		foreach ($a as $value) {
			$q = $wp->query('SELECT * FROM wp_terms WHERE name="' . $value . '"');
			if(!$wp->count()){
				$wp->query('INSERT INTO wp_terms (name,slug) VALUES ("'.$value.'","'. str_replace(' ', '-', $value) .'")');
				$wp->query('INSERT INTO wp_term_taxonomy (term_id,taxonomy) VALUES ('.$wp->lastId().',"product_tag")');
				$wp->query('INSERT INTO wp_term_relationships (object_id,term_taxonomy_id) VALUES ('.$post_id.','.$wp->lastId().')');
			}else{

				//need works
				$q = $wp->query('SELECT * FROM wp_term_taxonomy WHERE term_id=' . $q->row['term_id']);
				// $wp->query('INSERT INTO wp_term_relationships (object_id,term_taxonomy_id) VALUES ('.$post_id.','. $q->row['term_taxonomy_id'] .')');

			}
		}
	}



	// break;

}//end of product import



redirection("/wordpress/sssskk","/wordpress/sdsdsdsd");









