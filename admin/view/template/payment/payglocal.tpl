<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

  <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>

  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" size="50" name="payglocal_total" value="<?php echo $payglocal_total; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_title; ?></td>
            <td><input type="text" size="50" name="payglocal_title" value="<?php echo $payglocal_title; ?>"/></td>
          </tr>
          <tr>
            <td><?php echo $entry_test; ?></td>
            <td>
              <?php if ($payglocal_test) { ?>
                <input type="radio" name="payglocal_test" value="1" checked="checked" /><?php echo $text_yes; ?>
                <input type="radio" name="payglocal_test" value="0" /><?php echo $text_no; ?>
              <?php } else { ?>
                <input type="radio" name="payglocal_test" value="1" /><?php echo $text_yes; ?>
                <input type="radio" name="payglocal_test" value="0" checked="checked" /><?php echo $text_no; ?>
              <?php } ?>
            </td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_sandbox_merchant_id; ?></td>
            <td><input type="text" size="50" name="payglocal_sandbox_merchant_id" value="<?php echo $payglocal_sandbox_merchant_id; ?>"/></td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_sandbox_public_kid; ?></td>
            <td><input type="password" size="50" name="payglocal_sandbox_public_kid" value="<?php echo $payglocal_sandbox_public_kid; ?>"/></td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_sandbox_private_kid; ?></td>
            <td><input type="password" size="50" name="payglocal_sandbox_private_kid" value="<?php echo $payglocal_sandbox_private_kid; ?>"/></td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_sandbox_public_pem; ?></td>
            <td>
              <input type="text" name="payglocal_sandbox_public_pem" value="<?php echo $payglocal_sandbox_public_pem; ?>" />
              <a id="payglocal_sandbox_public_pem" class="button"><?php echo $button_upload; ?> <br/> 
            </td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_sandbox_private_pem; ?></td>
            <td>
              <input type="text" name="payglocal_sandbox_private_pem" value="<?php echo $payglocal_sandbox_private_pem; ?>" />
              <a id="payglocal_sandbox_private_pem" class="button"><?php echo $button_upload; ?> <br/> 
            </td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_gateway_url; ?></td>
            <td><input type="text" size="50" name="payglocal_sandbox_gateway_url" value="<?php echo $payglocal_sandbox_gateway_url; ?>"/></td>
          </tr>
          <tr class="sandbox">
            <td><?php echo $entry_refund_url; ?></td>
            <td><input type="text" size="50" name="payglocal_sandbox_refund_url" value="<?php echo $payglocal_sandbox_refund_url; ?>"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_live_merchant_id; ?></td>
            <td><input type="text" size="50" name="payglocal_live_merchant_id" value="<?php echo $payglocal_live_merchant_id; ?>" accept=".pem"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_live_public_kid; ?></td>
            <td><input type="text" size="50" name="payglocal_live_public_kid" value="<?php echo $payglocal_live_public_kid; ?>"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_live_private_kid; ?></td>
            <td><input type="text" size="50" name="payglocal_live_private_kid" value="<?php echo $payglocal_live_private_kid; ?>"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_live_public_pem; ?></td>
            <td><input type="file" name="payglocal_live_public_pem" value="<?php echo $payglocal_live_public_pem; ?>"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_live_private_pem; ?></td>
            <td><input type="file" name="payglocal_live_private_pem" value="<?php echo $payglocal_live_private_pem; ?>"/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_gateway_url; ?></td>
            <td><input type="text" size="50" name="payglocal_live_gateway_url" value="<?php echo $payglocal_live_gateway_url; ?>" <?php if(!empty($payglocal_live_gateway_url)){?> readonly="readonly" <?php }?>/></td>
          </tr>
          <tr class="live">
            <td><?php echo $entry_refund_url; ?></td>
            <td><input type="text" size="50" name="payglocal_live_refund_url" value="<?php echo $payglocal_live_refund_url; ?>" <?php if(!empty($payglocal_live_refund_url)){?> readonly="readonly" <?php }?>/></td>
          </tr>
          <tr>
            <td><?php echo $entry_refund_required; ?></td>
            <td>
              <?php if ($payglocal_refund) { ?>
                <input type="radio" name="payglocal_refund" value="1" checked="checked" /><?php echo $text_yes; ?>
                <input type="radio" name="payglocal_refund" value="0" /><?php echo $text_no; ?>
              <?php } else { ?>
                <input type="radio" name="payglocal_refund" value="1" /><?php echo $text_yes; ?>
                <input type="radio" name="payglocal_refund" value="0" checked="checked" /><?php echo $text_no; ?>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td>
              <select name="payglocal_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                  <?php if ($order_status['order_status_id'] == $payglocal_order_status_id) { ?>
                    <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td>
              <select name="payglocal_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $payglocal_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td>
              <select name="payglocal_status">
                <?php if ($payglocal_status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" size="50" name="payglocal_sort_order" value="<?php echo $payglocal_sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
$('input[name="payglocal_test"]').on('click', function () {
  var payment_mode = $('input[name="payglocal_test"]:checked').val();
  payglocal_test(payment_mode);
});

$(document).ready(function () {
  var payment_mode = $('input[name="payglocal_test"]:checked').val();
  payglocal_test(payment_mode);
});

function payglocal_test(payment_mode){
  if(payment_mode == 1){
    $(".live").hide();
    $(".sandbox").show();
  }else{
    $(".sandbox").hide();
    $(".live").show();
  }
}
//--></script>

<script type="text/javascript" src="view/javascript/jquery/ajaxupload.js"></script> 

<script type="text/javascript"><!--
new AjaxUpload('#payglocal_sandbox_public_pem', {
	action: 'index.php?route=payment/payglocal/upload&token=<?php echo $token; ?>&name=payglocal_sandbox_public_pem',
	name: 'payglocal_sandbox_public_pem',
	autoSubmit: true,
	responseType: 'json',
	onSubmit: function(file, extension) {
		$('#payglocal_sandbox_public_pem').after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
		$('#payglocal_sandbox_public_pem').attr('disabled', true);
	},
	onComplete: function(file, json) {
		$('#payglocal_sandbox_public_pem').attr('disabled', false);
		if (json['success']) {
			alert(json['success']);
			$('input[name=\'payglocal_sandbox_public_pem\']').attr('value', json['filename']);
		}
		if (json['error']) {
			alert(json['error']);
		}
		$('.loading').remove();	
	}
});
//--></script> 

<script type="text/javascript"><!--
new AjaxUpload('#payglocal_sandbox_private_pem', {
	action: 'index.php?route=payment/payglocal/upload&token=<?php echo $token; ?>&name=payglocal_sandbox_private_pem',
	name: 'payglocal_sandbox_private_pem',
	autoSubmit: true,
	responseType: 'json',
	onSubmit: function(file, extension) {
		$('#payglocal_sandbox_private_pem').after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
		$('#payglocal_sandbox_private_pem').attr('disabled', true);
	},
	onComplete: function(file, json) {
		$('#payglocal_sandbox_private_pem').attr('disabled', false);
    $('.loading').remove();
		if (json['success']) {
			alert(json['success']);
			$('input[name=\'payglocal_sandbox_private_pem\']').attr('value', json['filename']);
		}
		if (json['error']) {
			alert(json['error']);
		}
		$('.loading').remove();	
	}
});
//--></script> 
<style>
input[readonly] {
  background-color: #e9e9e9;
  border-color: #adadad;
  color: #303030;
  opacity: .5;
  cursor: not-allowed;
}
</style>
<?php echo $footer; ?>