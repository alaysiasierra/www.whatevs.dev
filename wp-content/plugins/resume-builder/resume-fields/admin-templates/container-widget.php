<div class="resume-container">
	<?php if ($this->has_fields()): ?>
		<div class="container-holder resume-grid container-<?php echo $this->id; ?>" data-json="<?php echo urlencode( json_encode($this->to_json(false)) ); ?>"></div>
	<?php else:
		_e('No options are available for this widget.', 'rbf'); ?>
	<?php endif; ?>
</div>
