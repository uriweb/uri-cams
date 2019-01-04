<?php	

	$classes = 'uri-cams';
	$classes .= ( ! empty( $class ) ) ? ' ' . $class : '';
	
	$alt .= ' (retrieved ' . $timestamp . ')';
	
?>

<figure class="<?php echo $classes; ?>">
	
	<?php if ( false !== $link ): ?>
		<a href="<?php echo $path . '?t=' . $time; ?>">
	<?php endif; ?>

	<img src="<?php echo $path . '?t=' . $time; ?>" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>" />

	<?php if ( false !== $link ): ?>
		</a>
	<?php endif; ?>

</figure>

