<tr class="resume-table-row <?php echo $this->is_tabbed() ? "" : "resume-fields-collection" ?>">
	<td></td>
	<td>
		<div class="container-holder resume-term-container <?php echo !empty($_GET['tag_ID']) ? 'edit-term-container' : 'add-term-container'; ?> container-<?php echo $this->id; ?>"></div>
		<?php echo $this->get_nonce_field(); ?>
	</td>
</tr>