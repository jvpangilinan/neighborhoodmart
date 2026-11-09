<?php
// ACTION: add (id=0) or update a generic catalog item.
$v=[post('name'),post('packaging'),post('category'),post('section'),max(1,(int)post('pack_size')),max(1,(int)post('load_units')),max(0,(int)post('max')),isset($_POST['active'])||!$id?1:0,max(0,(float)post('retail'))];
if($v[0]==='') go('Item name is required.','?p=admin_catalog');
if($id) ex("UPDATE items SET name=?,packaging=?,category=?,section=?,pack_size=?,load_units=?,max_per_member=?,active=?,retail_price=? WHERE id=?",'ssssiiiidi',...[...$v,$id]);
else $id=ex("INSERT INTO items(name,packaging,category,section,pack_size,load_units,max_per_member,active,retail_price) VALUES(?,?,?,?,?,?,?,?,?)",'ssssiiiid',...$v);
if((int)post('sugg')) ex("UPDATE item_suggestions SET status='added' WHERE id=?",'i',(int)post('sugg'));
audit("saved item $id"); go('Item saved.','?p=admin_catalog');
