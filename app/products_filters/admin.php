<?php

d()->post('/admin/change_products_property', function() {
	if (iam()) {
		if (d()->Products__field->find_by('field_name', $_POST['field_name'])->type === 'strings_list') {
			d()->db->exec('update `products` set `' . et($_POST['field_name']) . '`=binary trim("\\n" from replace(concat("\\n", replace(`' . et($_POST['field_name']) . '`, "\\r", ""), "\\n"), concat("\\n", ' . d()->db->quote($_POST['old_value']) . ', "\\n"), concat("\\n", ' . d()->db->quote($_POST['new_value']) . ', "\\n"))) where find_in_set(' . d()->db->quote($_POST['old_value']) . ', replace(replace(binary `' . et($_POST['field_name']) . '`, "\\n", ","), "\\r", ""))');
		} else {
			d()->db->exec($query = 'update `products` set `' . et($_POST['field_name']) . '`=binary ' . d()->db->quote($_POST['new_value']) . ' where `' . et($_POST['field_name']) . '`=binary ' . d()->db->quote($_POST['old_value']) . ' and `id` in (' . $_POST['ids'] . ')');
		}
	}
	exit;
});
