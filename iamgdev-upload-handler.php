<?php
/**
 * Functions upload handler WordPress
 * 
 * iamgdev_upload_handler( 'name', 'value' );
 *
 * @package iamgdev
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



function iamgdev_upload_handler( $field_name = '', $field_value = '', $title_upload = '', $arr_upload = array() ) {
	ob_start();

	if ( empty( $field_name ) ) :
		return false;
	endif;

	if ( ! did_action( 'wp_enqueue_media' ) ) :
		wp_enqueue_media();
	endif;

	if ( empty( $title_upload ) ) :
		$title_upload = __('Upload file', 'iamgdev');
	endif;

	$attachment_field = array();

	if( !empty( $field_value ) ) :
		if( strpos( $field_value, '{' ) !== false ) :
			$field_value = str_replace( '{', ' ', $field_value );
			$field_value = str_replace( '}', ',', $field_value );
		endif;
	
		if( strpos( $field_value, ',' ) !== false ) :
			$field_value	= str_replace( " , ", ",", $field_value );
			$field_value	= str_replace( " ,", ",", $field_value );
			$field_value	= str_replace( ", ", ",", $field_value );
			$field_value	= explode( ",", $field_value );
		endif;

		$attachment_field = get_post( $field_value[0] );
	endif;

	$attachment_caption	= basename( $attachment_field->guid ) ?: $attachment_field->post_title;
	$attachment_type	= $attachment_field->post_mime_type ?? '';
	$attachment_link	= "";

	if( strpos( $attachment_type, 'image' ) !== false ) :
		$attachment_link = wp_get_attachment_image_url( $attachment_field->ID, 'medium' ) ?? '';
	endif;
	
	$upload_multiple = $arr_upload['multiple'] ?? false;

	wp_enqueue_style( 'koran-style-upload-handler', '/admin/assets/css/koran-upload-handler.css', array(), '1.0.0' );

	$id_handler = "iamgdev-upload-handler-" . rand( 10, PHP_INT_MAX );

	?>

	<div id="<?php echo esc_attr( $id_handler ); ?>" class="iamgdev-upload-handler">
		<img src="<?php echo esc_attr( $attachment_link ); ?>" class="iamgdev-upload-preview" alt="" />
		<input type="hidden" class="iamgdev-upload-input" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" />
		<span class="iamgdev-upload-caption"><?php echo $attachment_caption; ?></span>
		<span class="iamgdev-upload-type"><?php echo $attachment_type; ?></span>
		<span class="iamgdev-upload-remove">&times;</span>
	</div>

	<script id="<?php echo esc_attr( $id_handler ); ?>-script">
		document.addEventListener('DOMContentLoaded', function () {

			var iamgdev_upload = document.getElementById("<?php echo $id_handler; ?>");

				iamgdev_upload.addEventListener('click', function (e) {
				e.preventDefault();
				var $this = this;
				var upload_input	= $this.querySelector(".iamgdev-upload-input");
				var upload_preview	= $this.querySelector(".iamgdev-upload-preview");
				var upload_type		= $this.querySelector(".iamgdev-upload-type");
				var upload_caption	= $this.querySelector(".iamgdev-upload-caption");

				if( e.target.classList.contains("iamgdev-upload-remove") ) {
					upload_input.value = "";
					upload_preview.setAttribute("src", "");
					upload_type.textContent = "";
					upload_caption.textContent = "<?php echo $title_upload; ?>";
				} else {
					var iamgdev_uploader = wp.media({
						title: '<?php echo $arr_upload['title'] ?? $title_upload; ?>',
						button: {
							text: "<?php echo $arr_upload['button']['text'] ?? __( 'Select file', 'iamgdev'); ?>",
						},
						multiple: <?php echo ($upload_multiple === false ? 'false' : 'true'); ?>,
					}).on('select', function () {
						var attachments = iamgdev_uploader.state().get('selection').toJSON();

						for(var i = 0; i < attachments.length; i++) {
							var attachment = attachments[i];
							upload_input.value = upload_input.value + "{" + attachment.id + "}";

							if( i === 0 ) {
								upload_caption.textContent = attachment.filename;
								upload_type.textContent = attachment.type;
								upload_preview.setAttribute("src", attachment.url);
							} else {
								upload_caption.textContent = upload_caption.textContent + ', ' + attachment.filename;
							}
						}

						$this.scrollIntoView();
					}).on('close', function() {
						$this.scrollIntoView();
					}).open();
				}
			});
		});
	</script>

	<?php
	return ob_get_clean();
}