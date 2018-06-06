<?php
function wo_admin_manage_clients_page() {
	wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	?>
    <div class="wrap" id="profile-page">
        <div class="section group">
            <div class="col span_4_of_6">
				<?php $CodeTableList = new WO_Table();
				$CodeTableList->prepare_items();
				$CodeTableList->display(); ?>
            </div>
        </div>

    </div>
<?php }
