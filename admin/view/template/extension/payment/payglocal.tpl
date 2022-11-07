<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-payglocal" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
          <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>

      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payglocal" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_total" value="<?php echo $payglocal_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_title; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_title" value="<?php echo $payglocal_title; ?>" placeholder="<?php echo $entry_title; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-test"><?php echo $entry_test; ?></label>
            <div class="col-sm-10">
              <select name="payglocal_test" id="input-status" class="form-control">
                <?php if ($payglocal_test) { ?>
                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                <option value="0"><?php echo $text_no; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes; ?></option>
                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sandbox_merchant_id; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_sandbox_merchant_id" value="<?php echo $payglocal_sandbox_merchant_id; ?>" placeholder="<?php echo $entry_sandbox_merchant_id; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sandbox_public_kid; ?></label>
            <div class="col-sm-10">
              <input type="password" name="payglocal_sandbox_public_kid" value="<?php echo $payglocal_sandbox_public_kid; ?>" placeholder="<?php echo $entry_sandbox_public_kid; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sandbox_private_kid; ?></label>
            <div class="col-sm-10">
              <input type="password" name="payglocal_sandbox_private_kid" value="<?php echo $payglocal_sandbox_private_kid; ?>" placeholder="<?php echo $entry_sandbox_private_kid; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sandbox_public_pem; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="payglocal_sandbox_public_pem" value="<?php echo $payglocal_sandbox_public_pem; ?>" placeholder="<?php echo $entry_sandbox_public_pem; ?>"  class="form-control" />
                <span class="input-group-btn">
                  <button type="button" id="payglocal_sandbox_public_pem" data-loading-text="Loading..." class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
                </span>
              </div>
            </div>
          </div> 
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_sandbox_private_pem; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" name="payglocal_sandbox_private_pem" value="<?php echo $payglocal_sandbox_private_pem; ?>" placeholder="<?php echo $entry_sandbox_private_pem; ?>"  class="form-control" />
                <span class="input-group-btn">
                  <button type="button" id="payglocal_sandbox_private_pem" data-loading-text="Loading..." class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
                </span>
              </div>
            </div>
          </div>    
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_gateway_url; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_sandbox_gateway_url" value="<?php echo $payglocal_sandbox_gateway_url; ?>" placeholder="<?php echo $entry_gateway_url; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group sandbox">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_refund_url; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_sandbox_refund_url" value="<?php echo $payglocal_sandbox_refund_url; ?>" placeholder="<?php echo $entry_refund_url; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_live_merchant_id; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_merchant_id" value="<?php echo $payglocal_live_merchant_id; ?>" placeholder="<?php echo $entry_live_merchant_id; ?>"  class="form-control" accept=".pem"/>
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_live_public_kid; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_public_kid" value="<?php echo $payglocal_live_public_kid; ?>" placeholder="<?php echo $entry_live_public_kid; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_live_private_kid; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_private_kid" value="<?php echo $payglocal_live_private_kid; ?>" placeholder="<?php echo $entry_live_private_kid; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_live_public_pem; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_public_pem" value="<?php echo $payglocal_live_public_pem; ?>" placeholder="<?php echo $entry_live_public_pem; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_live_private_pem; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_private_pem" value="<?php echo $payglocal_live_private_pem; ?>" placeholder="<?php echo $entry_live_private_pem; ?>"  class="form-control" />
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_gateway_url; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_gateway_url" value="<?php echo $entry_gateway_url; ?>" placeholder="<?php echo $payglocal_live_gateway_url; ?>"  class="form-control" <?php if(!empty($payglocal_live_gateway_url)){?> readonly="readonly" <?php }?>/>
            </div>
          </div>
          <div class="form-group live">
            <label class="col-sm-2 control-label" for="input-total"><?php echo $entry_refund_url; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_live_refund_url" value="<?php echo $entry_refund_url; ?>" placeholder="<?php echo $payglocal_live_refund_url; ?>"  class="form-control" <?php if(!empty($payglocal_live_refund_url)){?> readonly="readonly" <?php }?>/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-test"><?php echo $entry_refund_required; ?></label>
            <div class="col-sm-10">
              <select name="payglocal_refund" id="input-status" class="form-control">
                <?php if ($payglocal_refund) { ?>
                <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                <option value="0"><?php echo $text_no; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes; ?></option>
                <option value="0" selected="selected"><?php echo $text_no; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-test"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
            <select name="payglocal_order_status_id" id="input-order-status" class="form-control">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $payglocal_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
            <div class="col-sm-10">
              <select name="payglocal_geo_zone_id" id="input-geo-zone" class="form-control">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $payglocal_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="payglocal_status" id="input-status" class="form-control">
                <?php if ($payglocal_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="payglocal_sort_order" value="<?php echo $payglocal_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>   
        </form>
    </div>
  </div>
</div>
<script type="text/javascript">
<!--

$('[name="payglocal_test"]').on('change', function() {
  payglocal_test(this.value);
});

$(document).ready(function() {
  var payment_mode = $('[name="payglocal_test"] option:selected').val();
  payglocal_test(payment_mode);
});

function payglocal_test(payment_mode) {
  if (payment_mode == 1) {
    $(".live").hide();
    $(".sandbox").show();
  } else {
    $(".sandbox").hide();
    $(".live").show();
  }
}
//
-->
</script>

<script type="text/javascript" src="view/javascript/jquery/ajaxupload.js"></script>

<script type="text/javascript">
<!--
new AjaxUpload('#payglocal_sandbox_public_pem', {
    action: 'index.php?route=extension/payment/payglocal/upload&token=<?php echo $token; ?>&name=payglocal_sandbox_public_pem',
    name: 'payglocal_sandbox_public_pem',
    autoSubmit: true,
    responseType: 'json',
    onSubmit: function(file, extension) {
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
//
-->
</script>

<script type="text/javascript">
<!--
new AjaxUpload('#payglocal_sandbox_private_pem', {
    action: 'index.php?route=extension/payment/payglocal/upload&token=<?php echo $token; ?>&name=payglocal_sandbox_private_pem',
    name: 'payglocal_sandbox_private_pem',
    autoSubmit: true,
    responseType: 'json',
    onSubmit: function(file, extension) {
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
//
-->
</script>
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