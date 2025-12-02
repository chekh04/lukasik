<?php if(isset($export_system)){ //окно системных настроек в списке экспортов ?>

  <div class="row top-row">
    <div class="col-sm-5">
      <h3 style="line-height:35px;"><?php echo $text_export_system; ?> <strong><?php echo $export_system; ?></strong>
        <span class="goto_feed_setting btn btn-link" data-feed="<?php echo $export_system; ?>"><?php echo $text_export_system_goto_set; ?></span>
      </h3>
    </div>
    <div class="col-sm-7 text-right">
      <span class="btn btn-success" id="export_system_message" style="display:none;"><?php echo $text_export_saved; ?></span>
      <span class="close1 btn btn-default" data-dismiss="modal" aria-label="Close" id="export_system_close" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_modal_import_close; ?>"><i class="fa fa-times" aria-hidden="true"></i> <?php echo $text_modal_import_item_close; ?></span>
      <span class="btn btn-success" id="save_export_system_item"><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php echo $text_export_save_file_set; ?></span>
    </div>
  </div>

  <ul class="nav nav-tabs nav-export-system" style="margin-top:15px;">
    <li class="active"><a href="#tab-system-xml" data-toggle="tab"><i class="fa fa-file-code-o" aria-hidden="true"></i> <?php echo $text_export_xml_str; ?></a></li>
    <li><a href="#tab-system-del" data-toggle="tab"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $text_export_delete_feed; ?></a></li>
  </ul>

  <div class="tab-content tabs-data" style="margin-top:-2px;">

    <!--tab-system-xml-->
    <div class="tab-pane active" id="tab-system-xml">
      <small style="position:absolute;margin-top:-20px;"><?php echo $text_export_file; ?>: <?php echo $server_path; ?><b><?php echo $file_path; ?></b> (<?php echo $text_export_syntax_php; ?>)</small>
      <textarea id="export_system_file" data-action="<?php echo $action; ?>" <?php if($file_data_error){ ?>readonly="readonly"<?php } ?> style="overflow:scroll;width:100%;min-height:500px;border:1px solid #aaa;outline:none!important;"><?php echo $file_data; ?></textarea>
    </div>
    <!--/tab-system-xml-->

    <!--tab-system-del-->
    <div class="tab-pane" id="tab-system-del">
      <div style="font-weight:bold;font-size:24px;margin-bottom:15px;"><?php echo $text_export_realy_delete; ?></div>
      <div class="alert alert-info"><?php echo $text_export_feed_delete_alert; ?> <?php echo $export_system; ?>.php <?php echo $text_export_feed_delete_alert2; ?></div>
      <span class="btn btn-warning" data-feed="<?php echo $export_system; ?>" id="delete_feed"><?php echo $text_export_delete_feed; ?></span>
      <span class="btn btn-success" onclick="$('#export_system_close').click();"><?php echo $text_export_no_close_win; ?></span>
    </div>
    <!--/tab-system-del-->

  </div>

<?php } ?>
<?php if(isset($exports)){ //список экспортов ?>
  <?php foreach($exports as $export){ ?>
    <tr id="feed_list_<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>">
      <td class="text-center list_sort"><?php echo $export['export_num']; ?></td>
      <td class="text-left"><?php echo $export['name']; ?></td>
      <td class="text-center"><span class="export_list_status status<?php echo $export['status']; ?>" title="<?php if($export['status']){ ?><?php echo $text_export_on; ?><?php }else{ ?><?php echo $text_export_off; ?><?php } ?>"></td>
      <td class="text-left">
        <div><?php echo $text_export_gen_link1; ?> <a href="<?php echo $export['link_direct']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_direct']; ?></a>
        <div><?php echo $text_export_gen_link2; ?> <a href="<?php echo $export['link_cron']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_cron']; ?></a>
        <div><?php echo $text_export_gen_link3; ?> <a href="<?php echo $export['link_file']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_file']; ?></a>
      </td>
      <td class="text-right">
        <nobr>
          <span class="export_setting export_setting<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_setting"><span data-toggle="tooltip" title="<?php echo $text_export_list1; ?>" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span class="export_system export_system<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_system"><span data-toggle="tooltip" title="<?php echo $text_export_list2; ?>" class="btn btn-default"><i class="fa fa-code" aria-hidden="true"></i></span></span>
          <span class="export_trash export_trash<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>"><span data-toggle="tooltip" title="<?php echo $text_export_list3; ?>" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></span>
        </nobr>
      </td>
    </tr>
  <?php } ?>
<?php } ?>
<?php if(isset($trash)){ //список в корзине ?>
  <?php if($trash){ ?>
    <?php foreach($trash as $export){ ?>
    <tr id="feed_list_<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>">
      <td class="text-center list_sort"><?php echo $export['export_num']; ?></td>
      <td class="text-left"><?php echo $export['name']; ?></td>
      <td class="text-center"><span class="export_list_status status<?php echo $export['status']; ?>" title="<?php if($export['status']){ ?>Включена<?php }else{ ?>Выключена<?php } ?>"></td>
      <td class="text-left">
        <div><?php echo $text_export_gen_link1; ?> <a href="<?php echo $export['link_direct']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_direct']; ?></a>
        <div><?php echo $text_export_gen_link2; ?> <a href="<?php echo $export['link_cron']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_cron']; ?></a>
        <div><?php echo $text_export_gen_link3; ?> <a href="<?php echo $export['link_file']; ?>" target="_blank" title="<?php echo $text_export_open_new_win; ?>" data-toggle="tooltip"><?php echo $export['link_file']; ?></a>
      </td>
      <td class="text-right">
        <nobr>
          <span class="export_setting export_setting<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_setting"><span data-toggle="tooltip" title="<?php echo $text_export_list1; ?>" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span class="export_system export_system<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>" data-toggle="modal" data-target="#export_system"><span data-toggle="tooltip" title="<?php echo $text_export_list2; ?>" class="btn btn-default"><i class="fa fa-code" aria-hidden="true"></i></span></span>
          <span class="export_to_list export_trash<?php echo $export['feed']; ?>" data-feed="<?php echo $export['feed']; ?>"><span data-toggle="tooltip" title="<?php echo $text_export_trash_list3; ?>" class="btn btn-success"><i class="fa fa-repeat" aria-hidden="true"></i></span></span>
        </nobr>
      </td>
    </tr>
    <?php } ?>
  <?php }else{ ?>
    <tr>
      <td class="text-center" colspan="5"><?php echo $text_export_none; ?></td>
    </tr>
  <?php } ?>
<?php } ?>
<?php if(isset($feed)){ //окно настройки экспорта ?>

  <div class="exp-imp">
    <div class="row top-row">
      <div class="col-sm-5">
        <h3 style="line-height:35px;"><?php echo $text_export_modal_heading; ?> <strong><?php echo $feed; ?></strong></h3>
        <span class="btn btn-default export_full_btn" onclick="$('#export_setting .modal-dialog').toggleClass('export_full');$(this).toggleClass('export_full');"><i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i><br><?php echo $text_export_big; ?></span>
        <span class="btn btn-default export_full_btn2" style="display:none;" onclick="$('.export_full_btn').click();"><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i><br><?php echo $text_export_small; ?></span>
      </div>
      <div class="col-sm-7 text-right">
        <span class="btn btn-success" id="save_export_message" style="display:none;"><?php echo $text_export_saved; ?></span>
        <span class="close1 btn btn-default" data-dismiss="modal" aria-label="Close" id="export_setting_close" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_modal_import_close; ?>"><i class="fa fa-times" aria-hidden="true"></i> <?php echo $text_modal_import_item_close; ?></span>
        <a class="btn btn-info export-import" href="<?php echo $export_setting; ?>&feed=<?php echo $feed; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_export_export_setting; ?> <?php echo $feed; ?>. <?php echo $text_export_export_setting_alert; ?>"><i class="fa fa-upload" aria-hidden="true"></i> <?php echo $text_import_export_to_file; ?></a>
        <span class="btn btn-info export-import upload_file" data-feed="<?php echo $feed; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_import_import_from_file_title; ?> <?php echo $feed; ?>. <?php echo $text_import_import_from_file_title_alarm; ?>"><i class="fa fa-download" aria-hidden="true"></i> <?php echo $text_import_import_from_file; ?></span>
        <span class="btn btn-success" id="save_export_item"><i class="fa fa-floppy-o" aria-hidden="true"></i> <?php echo $text_modal_import_save; ?></span>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-10">

      <h3 class="setting_item_top">1. <?php echo $text_export_1; ?></h3>

      <form style="overflow-y:scroll;" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-unixml-export" class="form-horizontal">
        <input type="hidden" name="feed" value="<?php echo $feed; ?>">

          <!--export-block-1-->
          <div id="export-block-1" class="export-block-item">

            <h3>1. <?php echo $text_export_1; ?></h3>

            <!--1.1-->
              <div class="form-group" id="export-block-1-1">
                <span class="field_counter">1.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-1" target="_blank">
                    <?php echo $text_export_1_1; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_1_help; ?> <?php echo $feed; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input style="display:none;" type="checkbox" <?php if($unixml_status){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="unixml_<?php echo $feed; ?>_status" name="unixml_<?php echo $feed; ?>_status" value="1">
                  <label for="unixml_<?php echo $feed; ?>_status"></label>
                </div>
              </div>
            <!--/1.1-->

            <!--1.2-->
              <div class="form-group" id="export-block-1-2">
                <span class="field_counter">1.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-2" target="_blank">
                    <?php echo $text_export_1_2; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_2_help; ?> <?php echo $feed; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_name" type="text" name="unixml_<?php echo $feed; ?>_name" value="<?php echo $unixml_name; ?>" class="form-control">
                </div>
              </div>
            <!--/1.2-->

            <!--1.3-->
              <div class="form-group" id="export-block-1-3">
                <span class="field_counter">1.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-3" target="_blank">
                    <?php echo $text_export_1_3; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_3_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_language" id="unixml_<?php echo $feed; ?>_language" class="form-control">
                    <?php foreach($languages as $language){ ?>
                      <option value="<?php echo $language['language_id']; ?>" <?php if($language['language_id'] == $unixml_language){ ?>selected="selected"<?php } ?>><?php echo $language['name']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/1.3-->

            <!--1.4-->
              <div class="form-group" id="export-block-1-4">
                <span class="field_counter">1.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-4" target="_blank">
                    <?php echo $text_export_1_4; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_4_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_currency" id="unixml_<?php echo $feed; ?>_currency" class="form-control">
                    <?php foreach($currencies as $currency){ ?>
                      <option value="<?php echo $currency['currency_id']; ?>" <?php if($currency['currency_id'] == $unixml_currency){ ?>selected="selected"<?php } ?>><?php echo $currency['title']; ?> (<?php echo $currency['code']; ?>) - <?php echo $text_export_1_4_curs; ?> <?php echo $currency['value']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/1.4-->

            <!--1.5-->
              <div class="form-group" id="export-block-1-5">
                <span class="field_counter">1.5</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-5" target="_blank">
                    <?php echo $text_export_1_5; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_5_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_cost" placeholder="<?php echo $text_export_1_5_help; ?>" type="text" name="unixml_<?php echo $feed; ?>_delivery_cost" value="<?php echo $unixml_delivery_cost; ?>" class="form-control">
                </div>
              </div>
            <!--/1.5-->

            <!--1.6-->
              <div class="form-group" id="export-block-1-6">
                <span class="field_counter">1.6</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-6" target="_blank">
                    <?php echo $text_export_1_6; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_6_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_time" placeholder="<?php echo $text_export_1_6_help; ?>" type="text" name="unixml_<?php echo $feed; ?>_delivery_time" value="<?php echo $unixml_delivery_time; ?>" class="form-control">
                </div>
              </div>
            <!--/1.6-->

            <!--1.7-->
              <div class="form-group" id="export-block-1-7">
                <span class="field_counter">1.7</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/1-7" target="_blank">
                    <?php echo $text_export_1_7; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_1_7_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_delivery_jump" placeholder="<?php echo $text_export_1_7_help; ?>" type="text" name="unixml_<?php echo $feed; ?>_delivery_jump" value="<?php echo $unixml_delivery_jump; ?>" class="form-control">
                </div>
              </div>
            <!--/1.7-->

          </div>
          <!--/export-block-1-->

          <!--export-block-2-->
          <div id="export-block-2" class="export-block-item">
            <h3>2. <?php echo $text_export_2; ?></h3>

            <!--2.1-->
              <div class="form-group" id="export-block-2-1">
                <span class="field_counter">2.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-1" target="_blank">
                    <?php echo $text_export_2_1; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_1_help; ?>
                    </div>
                  </a>
                  <select name="unixml_<?php echo $feed; ?>_products_mode" class="form-control" style="margin-left:-15px;width:calc(100% + 15px);font-weight:400;margin-top:10px;">
                    <option value="" <?php if(!$unixml_products_mode){ ?>selected="selected"<?php } ?>><?php echo $text_export_2_1_mode1; ?></option>
                    <option value="1" <?php if($unixml_products_mode == 1){ ?>selected="selected"<?php } ?>><?php echo $text_export_2_1_mode2; ?></option>
                    <option value="2" <?php if($unixml_products_mode == 2){ ?>selected="selected"<?php } ?>><?php echo $text_export_2_1_mode3; ?></option>
                  </select>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <input type="text" name="unixml_<?php echo $feed; ?>_product" value="" placeholder="<?php echo $text_export_2_1_place; ?>" id="input-products" class="form-control" />
                    <div class="input-group-btn">
                      <span class="btn btn-danger" onclick="$('#input-products').val('');$('#export-block-2-1 .dropdown-menu').hide();">
                        <i class="fa fa-times-circle-o" aria-hidden="true"></i>
                      </span>
                    </div>
                  </div>
                  <div id="unixml_products" class="well well-sm" style="max-height: 300px; min-height: 35px; overflow: auto;">
                    <?php if($unixml_products){ ?>
                      <?php foreach($unixml_products as $product){ ?>
                        <div id="unixml_<?php echo $feed; ?>_products<?php echo $product['product_id']; ?>">
                          <i class="fa fa-minus-circle"></i>
                          <?php echo $product['name']; ?>
                          <a target="_blank" href="<?php echo $product['edit']; ?>" title="<?php echo $text_export_2_1_list1; ?>" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                          <a target="_blank" href="<?php echo $product['view']; ?>" title="<?php echo $text_export_2_1_list2; ?>" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>
                          <input type="hidden" name="unixml_<?php echo $feed; ?>_products[]" value="<?php echo $product['product_id']; ?>" />
                        </div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </div>
              </div>
            <!--/2.1-->

            <!--2.2-->
              <div class="form-group" id="export-block-2-2">
                <span class="field_counter">2.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-2" target="_blank">
                    <?php echo $text_export_2_2; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_2_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div id="unixml_categories" class="scrollbox" style="max-height:800px;border:1px solid #ccc;overflow:auto;">
                    <?php foreach($categories as $category) { ?>
                      <div data-id="<?php echo $category['category_id']; ?>">
                          <input id="category-<?php echo $category['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $category['category_id']; ?>" <?php if (in_array($category['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                          <label for="category-<?php echo $category['category_id']; ?>"></label>
                          <span class="category_item_name_span"><?php echo $category['name']; ?> <?php if ($category['child']) { ?><span>+</span><?php } ?></span>

                          <?php if ($category['child']) { ?>
                            <div class="category_child_block child_<?php echo $category['category_id']; ?>">
                              <?php foreach($category['child'] as $child) { ?>
                                <div class="category_child_item">
                                  <input id="category-<?php echo $child['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child['category_id']; ?>" <?php if (in_array($child['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                  <label for="category-<?php echo $child['category_id']; ?>"></label>
                                  <span class="category_item_name_span"><?php echo $child['name']; ?> <?php if ($child['child']) { ?><span>+</span><?php } ?></span>

                                  <?php if ($child['child']) { ?>
                                    <div class="category_child_block">
                                      <?php foreach($child['child'] as $child2) { ?>
                                        <div class="category_child_item">
                                          <input id="category-<?php echo $child2['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child2['category_id']; ?>" <?php if (in_array($child2['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                          <label for="category-<?php echo $child2['category_id']; ?>"></label>
                                          <span class="category_item_name_span"><?php echo $child2['name']; ?> <?php if ($child2['child']) { ?><span>+</span><?php } ?></span>

                                          <?php if ($child2['child']) { ?>
                                            <div class="category_child_block">
                                              <?php foreach($child2['child'] as $child3) { ?>
                                                <div class="category_child_item">
                                                  <input id="category-<?php echo $child3['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child3['category_id']; ?>" <?php if (in_array($child3['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                  <label for="category-<?php echo $child3['category_id']; ?>"></label>
                                                  <span class="category_item_name_span"><?php echo $child3['name']; ?> <?php if ($child3['child']) { ?><span>+</span><?php } ?></span>

                                                  <?php if ($child3['child']) { ?>
                                                    <div class="category_child_block">
                                                      <?php foreach($child3['child'] as $child4) { ?>
                                                        <div class="category_child_item">
                                                          <input id="category-<?php echo $child4['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child4['category_id']; ?>" <?php if (in_array($child4['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                          <label for="category-<?php echo $child4['category_id']; ?>"></label>
                                                          <span class="category_item_name_span"><?php echo $child4['name']; ?> <?php if ($child4['child']) { ?><span>+</span><?php } ?></span>

                                                          <?php if ($child4['child']) { ?>
                                                            <div class="category_child_block">
                                                              <?php foreach($child4['child'] as $child5) { ?>
                                                                <div class="category_child_item">
                                                                  <input id="category-<?php echo $child5['category_id']; ?>" class="checkbox_exp_mini" type="checkbox" name="unixml_<?php echo $feed; ?>_categories[]" value="<?php echo $child5['category_id']; ?>" <?php if (in_array($child5['category_id'], $unixml_categories)) { ?>checked="checked"<?php } ?>>
                                                                  <label for="category-<?php echo $child5['category_id']; ?>"></label>
                                                                  <span class="category_item_name_span"><?php echo $child5['name']; ?> <?php if ($child5['child']) { ?><span>+</span><?php } ?></span>
                                                                </div>
                                                              <?php } ?>
                                                              <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                                                            </div>
                                                          <?php } ?>

                                                        </div>
                                                      <?php } ?>
                                                      <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                                                    </div>
                                                  <?php } ?>

                                                </div>
                                              <?php } ?>
                                              <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                                            </div>
                                          <?php } ?>

                                        </div>
                                      <?php } ?>
                                      <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                                    </div>
                                  <?php } ?>

                                </div>
                              <?php } ?>
                              <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                            </div>
                          <?php } ?>

                      </div>
                    <?php } ?>
                  </div>
                  <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                </div>
              </div>
            <!--/2.2-->

            <!--2.3-->
              <div class="form-group" id="export-block-2-3">
                <span class="field_counter">2.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-3" target="_blank">
                    <?php echo $text_export_2_3; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_3_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div id="unixml_<?php echo $feed; ?>_brands" class="scrollbox" style="max-height:400px;border:1px solid #ccc;overflow:auto;">
                    <?php foreach($manufacturers as $manufacturer){ ?>
                      <div>
                        <input class="checkbox_exp_mini" type="checkbox" id="manufacturer-<?php echo $manufacturer['manufacturer_id']; ?>" name="unixml_<?php echo $feed; ?>_manufacturers[]" value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if (in_array($manufacturer['manufacturer_id'], $unixml_manufacturers)) { ?>checked="checked"<?php } ?> />
                        <label for="manufacturer-<?php echo $manufacturer['manufacturer_id']; ?>">&nbsp;<?php echo $manufacturer['name']; ?></label>
                      </div>
                     <?php } ?>
                  </div>
                  <a class="select_all"><?php echo $text_select_all; ?></a> / <a class="unselect_all"><?php echo $text_unselect_all; ?></a>
                </div>
              </div>
            <!--/2.3-->

            <!--2.4-->
              <div class="form-group" id="export-block-2-4">
                <span class="field_counter">2.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-4" target="_blank">
                    <?php echo $text_export_2_4; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_4_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_andor" name="unixml_<?php echo $feed; ?>_andor" class="form-control">
                    <?php if($unixml_andor){ ?>
                      <option value="0"><?php echo $text_export_2_4_1; ?></option>
                      <option value="1" selected="selected"><?php echo $text_export_2_4_2; ?></option>
                     <?php }else{ ?>
                      <option value="0" selected="selected"><?php echo $text_export_2_4_1; ?></option>
                      <option value="1"><?php echo $text_export_2_4_2; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.4-->

            <!--2.5-->
              <div class="form-group" id="export-block-2-5">
                <span class="field_counter">2.5</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/2-5" target="_blank">
                    <?php echo $text_export_2_5; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_5_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <?php if($seopro){ ?>
                    <select id="unixml_<?php echo $feed; ?>_seopro" name="unixml_<?php echo $feed; ?>_seopro" class="form-control">
                      <?php if($unixml_seopro){ ?>
                        <option value="1" selected="selected"><?php echo $text_export_2_5_1; ?></option>
                        <option value="0"><?php echo $text_export_2_5_2; ?></option>
                       <?php }else{ ?>
                        <option value="1"><?php echo $text_export_2_5_1; ?></option>
                        <option value="0" selected="selected"><?php echo $text_export_2_5_2; ?></option>
                      <?php } ?>
                    </select>
                  <?php }else{ ?>
                    <?php echo $text_export_2_5_none; ?>
                  <?php } ?>
                </div>
              </div>
            <!--/2.5-->

            <!--2.6-->
              <div class="form-group" id="export-block-2-6">
                <span class="field_counter">2.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    <?php echo $text_export_2_6; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_6_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_quantity" name="unixml_<?php echo $feed; ?>_quantity" class="form-control">
                    <?php if($unixml_quantity){ ?>
                      <option value="1" selected="selected"><?php echo $text_export_2_6_1; ?></option>
                      <option value="0"><?php echo $text_export_2_6_2; ?></option>
                     <?php }else{ ?>
                      <option value="1"><?php echo $text_export_2_6_1; ?></option>
                      <option value="0" selected="selected"><?php echo $text_export_2_6_2; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="form-group" id="hideblock_quantity" <?php if(!$unixml_quantity){ ?>style="display:none;"<?php } ?>>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    <?php echo $text_export_2_6_stat; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_6_stat_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select name="unixml_<?php echo $feed; ?>_stock" id="unixml_<?php echo $feed; ?>_stock" class="form-control">
                    <?php foreach($stock_statuses as $stock_status){ ?>
                      <?php if($stock_status['stock_status_id'] == $unixml_stock){ ?>
                        <option value="<?php echo $stock_status['stock_status_id']; ?>" selected="selected"><?php echo $stock_status['name']; ?></option>
                       <?php }else{ ?>
                        <option value="<?php echo $stock_status['stock_status_id']; ?>"><?php echo $stock_status['name']; ?></option>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.6-->

            <!--2.7-->
              <div class="form-group" id="export-block-2-7">
                <span class="field_counter">2.7</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/2-6" target="_blank">
                    <?php echo $text_export_2_7; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_2_7_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_image" name="unixml_<?php echo $feed; ?>_image" class="form-control">
                    <?php if($unixml_image){ ?>
                      <option value="0"><?php echo $text_export_2_7_1; ?></option>
                      <option value="1" selected="selected"><?php echo $text_export_2_7_2; ?></option>
                     <?php }else{ ?>
                      <option value="0" selected="selected"><?php echo $text_export_2_7_1; ?></option>
                      <option value="1"><?php echo $text_export_2_7_2; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/2.7-->

          </div>
          <!--/export-block-2-->

          <!--export-block-3-->
          <div id="export-block-3" class="export-block-item">
            <h3>3. <?php echo $text_export_3; ?></h3>

            <!--3.1-->
              <div class="form-group" id="export-block-3-1">
                <span class="field_counter">3.1</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-1" target="_blank">
                    <?php echo $text_export_3_1; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_1_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_markup" placeholder="<?php echo $text_export_3_1_place; ?>" type="text" name="unixml_<?php echo $feed; ?>_markup" value="<?php echo $unixml_markup; ?>" class="form-control">
                </div>
              </div>
            <!--/3.1-->

            <!--3.2-->
              <div class="form-group" id="export-block-3-2">
                <span class="field_counter">3.2</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-2" target="_blank">
                     <?php echo $text_export_3_2; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_2_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <?php if($unixml_options){ //если есть опции в магазине ?>

                    <select id="unixml_<?php echo $feed; ?>_option_multiplier_status" name="unixml_<?php echo $feed; ?>_option_multiplier_status" class="form-control">
                      <?php if($unixml_option_multiplier_status){ ?>
                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                        <option value="0"><?php echo $text_disabled; ?></option>
                       <?php }else{ ?>
                        <option value="1"><?php echo $text_enabled; ?></option>
                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>

                    <div class="hideoption<?php echo $feed; ?>" <?php if(!$unixml_option_multiplier_status){ ?>style="display:none;"<?php } ?>>

                      <div class="option-block-list">

                        <?php if ($unixml_option_multiplier) { ?>
                          <?php foreach ($unixml_option_multiplier as $option_multiplier_key => $block) { ?>

                            <div class="option-block-item option-block-<?php echo $option_multiplier_key; ?>">
                              <div class="row mtb-10">
                                <div class="col-sm-12">
                                  <input type="text" placeholder="<?php echo $text_export_3_2_add; ?>" class="form-control get-select-option" data-option-block="<?php echo $option_multiplier_key; ?>">

                                  <div class="row">
                                    <div class="col-sm-9">
                                      <div class="scrollbox option_select_scroll">
                                        <?php foreach ($block as $option) { ?>

                                          <div class="option-list-item option-list-item-<?php echo $option['option_id']; ?>">
                                            <div class="option-list-item-name">id <?php echo $option['option_id']; ?>: <b><?php echo $option['name']; ?></b> <small>(<?php echo $text_export_in; ?> <?php echo $option['products']; ?> <?php echo $text_export_in_pro; ?>)</small></div>
                                            <input type="hidden" value="<?php echo $option['option_id']; ?>" name="unixml_<?php echo $feed; ?>_option_multiplier[<?php echo $option_multiplier_key; ?>][]">
                                            <div class="option-list-item-values"><?php echo $text_export_3_2_values; ?> <?php echo $option['values']; ?></div>
                                            <span class="delete-option-item"><i class="fa fa-times" aria-hidden="true"></i></span>
                                          </div>

                                         <?php } ?>
                                         <div class="option-list-placeholder" data-option-block="<?php echo $option_multiplier_key; ?>" style="display:none;"><?php echo $text_export_3_2_add_text; ?> <i class="fa fa-level-up" aria-hidden="true"></i></div>
                                      </div>
                                    </div>
                                    <div class="col-sm-3 mt10">
                                      <div><b><?php echo $text_export_3_2_optionset; ?> <?php echo $option_multiplier_key; ?></b></div>
                                      <span class="delete-option-block" data-option-block="<?php echo $option_multiplier_key; ?>"><?php echo $text_export_3_2_optionset_delete; ?></span>
                                      <div style="margin-top:20px;"><?php echo $text_export_3_2_option_in_feed; ?><br><b>[[optionset<?php echo $option_multiplier_key; ?>]]</b></div>
                                    </div>
                                  </div>

                                </div>
                              </div>
                            </div>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <span class="btn btn-info add-option-block"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $text_export_3_2_add_a_set_of_options; ?></span>
                    </div>
                  <?php }else{ ?>
                    <?php echo $text_export_3_2_none_options; ?>
                  <?php } ?>
                </div><!--/col-sm-10-->
              </div><!--/#export-block-3-2-->
            <!--/3.2-->

            <!--3.3-->
              <div class="form-group" id="export-block-3-3">
                <span class="field_counter">3.3</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-3" target="_blank">
                     <?php echo $text_export_3_3; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_3_help; ?>
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <textarea style="min-height:65px;" id="unixml_<?php echo $feed; ?>_genname" name="unixml_<?php echo $feed; ?>_genname" class="form-control"><?php echo $unixml_genname; ?></textarea>
                </div>
              </div>
            <!--/3.3-->

            <!--3.4-->
            <div class="form-group" id="export-block-3-4">
              <span class="field_counter">3.4</span>
              <label class="col-sm-2 control-label pt0">
                <a href="https://unixml.pro/set/export/3-4" target="_blank">
                  <?php echo $text_export_3_4; ?>
                  <div class="help">
                    <?php echo $text_export_help_title; ?>
                    <?php echo $text_export_3_4_help; ?>
                  </div>
                </a>
                <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong"><?php echo $text_export_db_fields; ?></button>
              </label>
              <div class="col-sm-10">
                <textarea style="min-height:65px;" id="unixml_<?php echo $feed; ?>_gendesc" name="unixml_<?php echo $feed; ?>_gendesc" class="form-control"><?php echo $unixml_gendesc; ?></textarea>
              </div>
            </div>
            <!--/3.4-->

            <!--3.5-->
              <div class="form-group" id="export-block-3-5">
                <span class="field_counter">3.5</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-5" target="_blank">
                    <?php echo $text_export_3_5; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_5_help; ?>
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_gendesc_mode" name="unixml_<?php echo $feed; ?>_gendesc_mode" class="form-control">
                      <option value="" <?php if(!$unixml_gendesc_mode){ ?>selected="selected"<?php } ?>><?php echo $text_export_3_5_1; ?></option>
                      <option value="1" <?php if($unixml_gendesc_mode){ ?>selected="selected"<?php } ?>><?php echo $text_export_3_5_2; ?></option>
                  </select>
                </div>
              </div>
            <!--/3.5-->

            <!--3.6-->
              <div class="form-group" id="export-block-3-6">
                <span class="field_counter">3.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-6" target="_blank">
                     <?php echo $text_export_3_6; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_6_help; ?>
                    </div>
                  </a>
                  <button type="button" class="mt10 btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmLong"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_clear_desc" name="unixml_<?php echo $feed; ?>_clear_desc" class="form-control">
                      <option value="" <?php if(!$unixml_clear_desc){ ?>selected="selected"<?php } ?>><?php echo $text_export_3_6_1; ?></option>
                      <option value="2" <?php if($unixml_clear_desc == 2){ ?>selected="selected"<?php } ?>><?php echo $text_export_3_6_2; ?></option>
                      <option value="1" <?php if($unixml_clear_desc == 1){ ?>selected="selected"<?php } ?>><?php echo $text_export_3_6_3; ?></option>
                  </select>
                </div>
              </div>
            <!--/3.6-->

            <!--3.7-->
              <div class="form-group" id="export-block-3-7">
                <span class="field_counter">3.7</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-7" target="_blank">
                    <?php echo $text_export_3_7; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_7_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow: visible;">
                  <table id="unixml_<?php echo $feed; ?>_category_match" class="table table-striped table-bordered table-hover">
                    <?php echo $text_export_3_7_table_header; ?>
                    <tbody>
                      <?php foreach ($unixml_category_match as $xml_name) { ?>
                        <tr id="category_match_row<?php echo $category_match_row; ?>">
                          <td class="text-left" style="width: 22%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][category_name]" value="<?php echo $xml_name['category_name']; ?>" placeholder="<?php echo $text_export_3_7_cat_name; ?>" class="form-control" />
                            <input type="hidden" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][category_id]" value="<?php echo $xml_name['category_id']; ?>" />
                          </td>
                          <td class="text-left" style="width: 22%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][xml_name]" value="<?php echo $xml_name['xml_name']; ?>" placeholder="<?php echo $text_export_3_7_xml_name; ?>" class="form-control" />
                          </td>
                          <td class="text-left" style="width: 13%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][markup]" value="<?php echo $xml_name['markup']; ?>" placeholder="<?php echo $text_export_3_7_markup; ?>" class="form-control" />
                          </td>
                          <td class="text-left" style="width: 41%;">
                            <textarea name="unixml_<?php echo $feed; ?>_category_match[<?php echo $category_match_row; ?>][custom]" placeholder="<?php echo $text_export_3_7_tag; ?>" class="form-control" ><?php echo $xml_name['custom']; ?></textarea>
                          </td>
                          <td class="text-center"><button type="button" onclick="$('#category_match_row<?php echo $category_match_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $category_match_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="4"></td>
                        <td class="text-center"><button type="button" onclick="addCategoryMatch('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.7-->

            <!--3.8-->
              <div class="form-group" id="export-block-3-8">
                <span class="field_counter">3.8</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/3-8" target="_blank">
                     <?php echo $text_export_3_8; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_8_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_attribute_status" name="unixml_<?php echo $feed; ?>_attribute_status" class="form-control">
                    <?php if($unixml_attribute_status){ ?>
                      <option value="0"><?php echo $text_export_3_8_1; ?></option>
                      <option value="1" selected="selected"><?php echo $text_export_3_8_2; ?></option>
                     <?php }else{ ?>
                      <option value="0" selected="selected"><?php echo $text_export_3_8_1; ?></option>
                      <option value="1"><?php echo $text_export_3_8_2; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div id="hideattr" class="form-group" <?php if($unixml_attribute_status){ ?>style="display:none;"<?php } ?>>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-8" target="_blank">
                     <?php echo $text_export_3_8_replace; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_8_replace_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow: visible;">
                  <table id="unixml_attributes" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $text_export_3_8_replace_attr_oc; ?></td>
                        <td class="text-left"><?php echo $text_export_3_8_replace_attr_xml; ?></td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_attributes as $xml_attribute) { ?>
                        <tr id="attribute-row<?php echo $attribute_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][attribute_name]" value="<?php echo $xml_attribute['attribute_name']; ?>" placeholder="<?php echo $text_export_3_8_replace_attr_oc; ?>" class="form-control" />
                            <input type="hidden" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][attribute_id]" value="<?php echo $xml_attribute['attribute_id']; ?>" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_attributes[<?php echo $attribute_row; ?>][xml_name]" value="<?php echo $xml_attribute['xml_name']; ?>" placeholder="<?php echo $text_export_3_8_replace_attr_xml; ?>" class="form-control" />
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $attribute_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2"></td>
                        <td class="text-center"><button type="button" onclick="addAttribute('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.8-->

            <!--3.9-->
              <div class="form-group" id="export-block-3-9">
                <span class="field_counter">3.9</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-9" target="_blank">
                     <?php echo $text_export_3_9; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_9_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive" style="overflow-y:scroll;max-height:730px;">
                  <table id="unixml_product_markup_table" class="table table-striped2 table-bordered table-hover2">
                    <?php echo $text_export_3_9_markup_table_header; ?>
                    <tbody>
                      <?php foreach ($unixml_product_markup as $markup_item) { ?>
                        <tr data-row="<?php echo $product_markup_row; ?>" id="product_markup_row<?php echo $product_markup_row; ?>">
                          <td class="text-left va-top" style="width: 15%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][name]" value="<?php echo $markup_item['name']; ?>" placeholder="<?php echo $text_name; ?>" autocomplete="off" class="form-control" />
                            <button class="btn btn-info importMarkup" data-feed="<?php echo $feed; ?>" data-row="<?php echo $product_markup_row; ?>" title="<?php echo $text_export_3_9_pro_import; ?>" data-toggle="tooltip"><i class="fa fa-upload" aria-hidden="true"></i> <?php echo $text_export_3_9_pro_import2; ?></button>
                          </td>
                          <td class="text-left" style="width: 67%;">
                            <input type="text" value="" placeholder="<?php echo $text_export_3_9_pro_entry_name; ?>" id="input-markup-products<?php echo $product_markup_row; ?>" class="form-control" />
                            <div id="unixml_<?php echo $feed; ?>_markup_products<?php echo $product_markup_row; ?>" class="well well-sm" style="height: 250px; overflow: auto;">
                              <?php foreach($markup_item['products'] as $product){ ?>
                                <div id="unixml_<?php echo $feed; ?>_markup_products<?php echo $product_markup_row; ?>-<?php echo $product['product_id']; ?>"><i class="fa fa-minus-circle"></i>
                                  <?php echo $product['name']; ?>
                                  <a target="_blank" href="<?php echo $product['edit']; ?>" title="<?php echo $text_export_2_1_list1; ?>" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                  <a target="_blank" href="<?php echo $product['view']; ?>" title="<?php echo $text_export_2_1_list2; ?>" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                  <input type="hidden" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][products][]" value="<?php echo $product['product_id']; ?>" />
                                </div>
                              <?php } ?>
                            </div>
                          </td>
                          <td class="text-left va-top" style="width: 13%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_product_markup[<?php echo $product_markup_row; ?>][markup]" value="<?php echo $markup_item['markup']; ?>" placeholder="<?php echo $text_export_3_9_pro_markup; ?>" class="form-control" />
                          </td>
                          <td class="text-center va-top"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $product_markup_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="3"><?php echo $text_export_3_9_table_help; ?></td>
                        <td class="text-center"><button type="button" onclick="addProductMarkup('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.9-->

            <!--3.10-->
              <div class="form-group" id="export-block-3-10">
                <span class="field_counter">3.10</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-10" target="_blank">
                    <?php echo $text_export_3_10; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_10_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive">
                  <table id="unixml_<?php echo $feed; ?>_replace_names" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $text_export_3_10_from; ?></td>
                        <td class="text-left"><?php echo $text_export_3_10_to; ?></td>
                        <td class="text-left"><?php echo $text_export_3_10_where; ?></td>
                        <td class="text-center"></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_replace_name as $xml_replace_item) { ?>
                        <tr id="replace_name-row<?php echo $replace_name_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][name_from]" value="<?php echo $xml_replace_item['name_from']; ?>" placeholder="<?php echo $text_export_3_10_from; ?>" class="form-control" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][name_to]" value="<?php echo $xml_replace_item['name_to']; ?>" placeholder="<?php echo $text_export_3_10_to; ?>" class="form-control" />
                          </td>
                          <td class="text-left">
                           <div class="replace-where-div">
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="1" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-1" <?php if(in_array(1, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-1">&nbsp;<?php echo $text_export_3_10_name; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="2" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-2" <?php if(in_array(2, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-2">&nbsp;<?php echo $text_export_3_10_model; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="3" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-3" <?php if(in_array(3, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-3">&nbsp;<?php echo $text_export_3_10_man; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="4" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-4" <?php if(in_array(4, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-4">&nbsp;<?php echo $text_export_3_10_desc; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="5" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-5" <?php if(in_array(5, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-5">&nbsp;<?php echo $text_export_3_10_url; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="6" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-6" <?php if(in_array(6, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-6">&nbsp;<?php echo $text_export_3_10_image; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="7" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-7" <?php if(in_array(7, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-7">&nbsp;<?php echo $text_export_3_10_attr_name; ?><label></div>
                            <div><input type="checkbox" name="unixml_<?php echo $feed; ?>_replace_name[<?php echo $replace_name_row; ?>][replace_where][]" value="8" class="checkbox_exp_mini" id="rr-<?php echo $replace_name_row; ?>-8" <?php if(in_array(8, $xml_replace_item['replace_where'])){ ?>checked="checked"<?php } ?>>
                            <label for="rr-<?php echo $replace_name_row; ?>-8">&nbsp;<?php echo $text_export_3_10_attr_val; ?><label></div>
                           </div>
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $replace_name_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="3"></td>
                        <td class="text-center"><button type="button" onclick="addReplaceRow('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>

                </div>
              </div>
            <!--/3.10-->

            <!--3.11-->
              <div class="form-group" id="export-block-3-11">
                <span class="field_counter">3.11</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-11" target="_blank">
                    <?php echo $text_export_3_11; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_11_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <select id="unixml_<?php echo $feed; ?>_images" name="unixml_<?php echo $feed; ?>_images" class="form-control">
                    <?php if($unixml_images){ ?>
                      <option value="1" selected="selected"><?php echo $text_export_3_11_1; ?></option>
                      <option value="0"><?php echo $text_export_3_11_2; ?></option>
                     <?php }else{ ?>
                      <option value="1"><?php echo $text_export_3_11_1; ?></option>
                      <option value="0" selected="selected"><?php echo $text_export_3_11_2; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            <!--/3.11-->

            <!--3.12-->
              <div class="form-group" id="export-block-3-12">
                <span class="field_counter">3.12</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-12" target="_blank">
                    <?php echo $text_export_3_12; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_12_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10 table-responsive">
                  <table id="unixml_additional_params" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <td class="text-left"><?php echo $text_export_3_12_name; ?></td>
                        <td class="text-left"><?php echo $text_export_3_12_text; ?></td>
                        <td></td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unixml_additional_params as $xml_attribute) { ?>
                        <tr id="param-row<?php echo $param_row; ?>">
                          <td class="text-left" style="width: 40%;">
                            <input type="text" name="unixml_<?php echo $feed; ?>_additional_params[<?php echo $param_row; ?>][param_name]" value="<?php echo $xml_attribute['param_name']; ?>" placeholder="<?php echo $text_export_3_12_name; ?>" class="form-control" />
                          </td>
                          <td class="text-left">
                            <input type="text" name="unixml_<?php echo $feed; ?>_additional_params[<?php echo $param_row; ?>][param_text]" value="<?php echo $xml_attribute['param_text']; ?>" placeholder="<?php echo $text_export_3_12_text; ?>" class="form-control" />
                          </td>
                          <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                        </tr>
                        <?php $param_row++; ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2">
                          <?php echo $text_export_3_12_table_help; ?>
                        </td>
                        <td class="text-center"><button type="button" onclick="addParam('<?php echo $feed; ?>');" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            <!--/3.12-->

            <!--3.13-->
              <div class="form-group" id="export-block-3-13">
                <span class="field_counter">3.13</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/3-13" target="_blank">
                    <?php echo $text_export_3_13; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_3_13_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_utm" placeholder="?utm_source=<?php echo $feed; ?>&utm_medium=cpc&utm_campaign=utm_metki" type="text" name="unixml_<?php echo $feed; ?>_utm" value="<?php echo $unixml_utm; ?>" class="form-control">
                </div>
              </div>
            <!--/3.13-->

          </div>
          <!--/export-block-3-->

          <!--export-block-4-->
          <div id="export-block-4" class="export-block-item">
            <h3>4. <?php echo $text_export_4; ?></h3>

            <!--4.1-->
              <div class="form-group" id="export-block-4-1">
                <span class="field_counter">4.1</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-1" target="_blank">
                    <?php echo $text_export_4_1; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_4_1_help; ?>
                    </div>
                  </a>
                </label>

                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1"><?php echo $text_export_4_1_addon1; ?></span>
                    <input type="text" id="unixml_<?php echo $feed; ?>_custom_sql" value="<?php echo $unixml_custom_sql; ?>" placeholder="<?php echo $text_export_4_1_place; ?>" name="unixml_<?php echo $feed; ?>_custom_sql" class="form-control">
                    <span class="input-group-addon" id="input-_custom_xml2"><?php echo $text_export_4_1_addon2; ?></span>
                  </div>
                </div>
              </div>
            <!--/4.1-->

            <!--4.2-->
              <div class="form-group" id="export-block-4-2">
                <span class="field_counter">4.2</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-2" target="_blank">
                    <?php echo $text_export_4_2; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_4_2_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml_after_sql">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml_after_sql" placeholder="<?php echo $text_export_4_2_textarea; ?>" type="text" name="unixml_<?php echo $feed; ?>_custom_xml_after_sql" class="form-control"><?php echo $unixml_custom_xml_after_sql; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml_after_sql">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.2-->

            <!--4.3-->
              <div class="form-group" id="export-block-4-3">
                <span class="field_counter">4.3</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-3" target="_blank">
                    <?php echo $text_export_4_3; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_4_3_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml" placeholder="<?php echo $text_export_4_3_place; ?>" type="text" name="unixml_<?php echo $feed; ?>_custom_xml" class="form-control"><?php echo $unixml_custom_xml; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml2">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.3-->

            <!--4.4-->
              <div class="form-group" id="export-block-4-4">
                <span class="field_counter">4.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/4-4" target="_blank">
                    <?php echo $text_export_4_4; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_4_4_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="input-_custom_xml1">&lt;?php</span>
                    <textarea style="min-height:100px;" id="unixml_<?php echo $feed; ?>_custom_xml_final" placeholder="<?php echo $text_export_4_4_place; ?>" type="text" name="unixml_<?php echo $feed; ?>_custom_xml_final" class="form-control"><?php echo $unixml_custom_xml_final; ?></textarea>
                    <span class="input-group-addon" id="input-_custom_xml2">?&gt;</span>
                  </div>
                </div>
              </div>
            <!--/4.4-->

          </div>
          <!--/export-block-4-->

          <!--export-block-5-->
          <div id="export-block-5" class="export-block-item">
            <h3>5. <?php echo $text_export_5; ?></h3>

            <!--5.1-->
              <div class="form-group" id="export-block-5-1">
                <span class="field_counter">5.1</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-1" target="_blank">
                    <?php echo $text_export_5_1; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_1_help; ?>
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_fields" type="text" name="unixml_<?php echo $feed; ?>_fields" value="<?php echo $unixml_fields; ?>" class="form-control">
                </div>
              </div>
            <!--/5.1-->

            <!--5.2-->
              <div class="form-group" id="export-block-5-2">
                <span class="field_counter">5.2</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-2" target="_blank">
                    <?php echo $text_export_5_2; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_2_help; ?>
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_field_id" type="text" name="unixml_<?php echo $feed; ?>_field_id" value="<?php echo $unixml_field_id; ?>" class="form-control" placeholder="p.product_id">
                </div>
              </div>
            <!--/5.2-->

            <!--5.3-->
              <div class="form-group" id="export-block-5-3">
                <span class="field_counter">5.3</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-3" target="_blank">
                    <?php echo $text_export_5_3; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_3_help; ?>
                    </div>
                  </a>
                  <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#uxmShort"><?php echo $text_export_db_fields; ?></button>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_field_price" type="text" name="unixml_<?php echo $feed; ?>_field_price" value="<?php echo $unixml_field_price; ?>" class="form-control" placeholder="p.price">
                </div>
              </div>
            <!--/5.3-->

            <!--5.4-->
              <div class="form-group" id="export-block-5-4">
                <span class="field_counter">5.4</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/5-4" target="_blank">
                    <?php echo $text_export_5_4; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_4_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_<?php echo $feed; ?>_step" type="text" name="unixml_<?php echo $feed; ?>_step" value="<?php echo $unixml_step; ?>" class="form-control">
                </div>
              </div>
            <!--/5.4-->

            <!--5.5-->
              <div class="form-group" id="export-block-5-5">
                <span class="field_counter">5.5</span>
                <label class="col-sm-2 control-label">
                  <a href="https://unixml.pro/set/export/5-5" target="_blank">
                    <?php echo $text_export_5_5; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_5_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="unixml_<?php echo $feed; ?>_log"><?php echo HTTPS_CATALOG; ?>system/storage/logs/</span>
                    <input id="unixml_<?php echo $feed; ?>_log" placeholder="<?php echo $text_export_5_5_place; ?>" type="text" name="unixml_<?php echo $feed; ?>_log" value="<?php echo $unixml_log; ?>" class="form-control">
                  </div>
                </div>
              </div>
            <!--/5.5-->

            <!--5.6-->
              <div class="form-group" id="export-block-5-6">
                <span class="field_counter">5.6</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-6" target="_blank">
                    <?php echo $text_export_5_6; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_6_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <input id="unixml_secret" type="text" name="unixml_<?php echo $feed; ?>_secret" value="<?php echo $unixml_secret; ?>" class="form-control">
                  <small><?php echo $text_export_5_6_small; ?></small>
                </div>
              </div>
            <!--/5.6-->

            <!--5.7-->
              <div class="form-group" id="export-block-5-7">
                <span class="field_counter">5.7</span>
                <label class="col-sm-2 control-label pt0">
                  <a href="https://unixml.pro/set/export/5-7" target="_blank">
                    <?php echo $text_export_5_7; ?>
                    <div class="help">
                      <?php echo $text_export_help_title; ?>
                      <?php echo $text_export_5_7_help; ?>
                    </div>
                  </a>
                </label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <span class="input-group-addon" id="unixml_<?php echo $feed; ?>_xml_name"><?php echo HTTPS_CATALOG; ?>price/</span>
                    <input id="unixml_xml_name" data-feed="<?php echo $feed; ?>" type="text" name="unixml_<?php echo $feed; ?>_xml_name" value="<?php echo $unixml_xml_name; ?>" class="form-control">
                    <span class="input-group-addon">.xml</span>
                  </div>
                </div>
              </div>
            <!--/5.7-->

          </div>
          <!--/export-block-5-->

          <!--export-block-6-->
          <div id="export-block-6" class="export-block-item">
            <h3>6. <?php echo $text_export_6; ?></h3>

            <div class="form-group" id="export-block-6-1">
              <span class="field_counter">6.1</span>

              <div id="direct-link" style="display:none;"><?php echo $data_feed; ?><span><?php if($unixml_secret){ ?>&key=<?php echo $unixml_secret; ?><?php } ?></span></div>

              <!--6.1-->
                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                      <?php echo $text_export_6_1; ?>
                      <div class="help">
                        <?php echo $text_export_help_title; ?>
                        <?php echo $text_export_6_1_help; ?>
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-direct-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy"><?php echo $text_copy; ?> <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" target="_blank" title="<?php echo $text_export_6_1_1_title; ?>" data-toggle="tooltip"><?php echo $text_goto; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                    <small><?php echo $text_export_6_1_1_small; ?></small>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                      <?php echo $text_export_6_1_file; ?>
                      <div class="help">
                        <?php echo $text_export_help_title; ?>
                        <?php echo $text_export_6_1_file_help; ?>
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-cron-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy"><?php echo $text_copy; ?> <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" target="_blank" title="<?php echo $text_export_6_1_1_title; ?>" data-toggle="tooltip"><?php echo $text_goto; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                    <small><?php echo $text_export_6_1_2_small; ?></small>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label pt0">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                      <?php echo $text_export_6_1_cron_title; ?>
                      <div class="help">
                        <?php echo $text_export_help_title; ?>
                        <?php echo $text_export_6_1_cron_title_help; ?>
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-cron-command">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon tocopy"><?php echo $text_copy; ?> <i class="fa fa-copy" aria-hidden="true"></i></span>
                    </div>
                    <?php echo $text_export_6_1_cron_title_help2; ?>
                  </div>
                </div>

                <div class="link-block row">
                  <label class="col-sm-2 control-label">
                    <a href="https://unixml.pro/set/export/6-1" target="_blank">
                      <?php echo $text_export_6_1_xml_file; ?>
                      <div class="help">
                        <?php echo $text_export_help_title; ?>
                        <?php echo $text_export_6_1_xml_file_help; ?>
                      </div>
                    </a>
                  </label>
                  <div class="col-sm-10" id="row-file-link">
                    <div class="input-group">
                      <input placeholder="" type="text" readonly class="form-control">
                      <span class="input-group-addon fileinfo"><?php echo $text_loading; ?></span>
                      <a href="" class="input-group-addon filelink" title="Скачать файл" download data-toggle="tooltip"><i class="fa fa-download" aria-hidden="true"></i></a>
                      <span class="input-group-addon tocopy"><?php echo $text_copy; ?> <i class="fa fa-copy" aria-hidden="true"></i></span>
                      <span class="input-group-addon">
                        <a href="" class="filelink_goto" target="_blank" title="<?php echo $text_export_6_1_1_title; ?>" data-toggle="tooltip"><?php echo $text_goto; ?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                      </span>
                    </div>
                  </div>
                </div>
              <!--/6.1-->

            </div>

            <div class="form-group" id="export-block-6-2">
              <span class="field_counter">6.2</span>
              <label class="col-sm-2 control-label" for="<?php echo $data_feed; ?>"><?php echo $text_export_6_2; ?></label>
              <div class="col-sm-10 control-label" style="text-align:left;">
                <?php echo str_replace('{{feed}}', $feed, $text_export_6_2_desc); ?>
              </div>
            </div>

          </div>
          <!--/export-block-6-->

      </form>

      <div id="get-option-html">
        <div class="option-block-item option-block-777" data-option-block="777">
          <div class="row mtb-10">
            <div class="col-sm-12">
              <input type="text" placeholder="<?php echo $text_export_3_2_add; ?>" class="form-control get-select-option" data-option-block="777">

              <div class="row">
                <div class="col-sm-9">
                  <div class="scrollbox option_select_scroll"><div class="option-list-placeholder"  data-option-block="777"><?php echo $text_export_3_2_add_text; ?> <i class="fa fa-level-up" aria-hidden="true"></i></div></div>
                </div>
                <div class="col-sm-3 mt10">
                  <div><b><?php echo $text_export_3_2_optionset; ?> 777</b></div>
                  <span class="delete-option-block" data-option-block="777"><?php echo $text_export_3_2_optionset_delete; ?></span>
                  <div style="margin-top:20px;"><?php echo $text_export_3_2_option_in_feed; ?><br><b>[[optionset777]]</b></div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-sm-2" style="padding:0;z-index:2;">

      <div class="export-navigation-fast">
        <input type="text" placeholder="<?php echo $text_export_fast_nav; ?>" autofocus class="form-control">
        <ul class="export-navigation-list">
          <?php foreach($export_fields as $export_block){ ?>
          <li data-search="<?php echo $export_block['block_search']; ?>" data-id="export-block-<?php echo $export_block['block_id']; ?>">
            <span><?php echo $export_block['block_name']; ?></span>
            <ul>
              <?php foreach($export_block['block_fields'] as $block_field){ ?>
                <li data-search="<?php echo $block_field['field_search']; ?>" data-id="export-block-<?php echo $export_block['block_id']; ?>-<?php echo $block_field['field_id']; ?>">
                  <span><?php echo $export_block['block_id']; ?>.<?php echo $block_field['field_id']; ?> <?php echo $block_field['field_name']; ?></span>
                </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>
        </ul>
      </div>

      <ul class="export-navigation">
        <li class="export-block-1 active">1. <?php echo $text_export_1; ?></li>
        <li class="export-block-2">2. <?php echo $text_export_2; ?></li>
        <li class="export-block-3">3. <?php echo $text_export_3; ?></li>
        <li class="export-block-4">4. <?php echo $text_export_4; ?></li>
        <li class="export-block-5">5. <?php echo $text_export_5; ?></li>
        <li class="export-block-6">6. <?php echo $text_export_6; ?></li>
      </ul>
      <span class="load_system_setting_popup btn btn-default" data-feed="<?php echo $feed; ?>" title="<?php echo $text_export_list2; ?>" data-toggle="tooltip" data-placement="left"><i class="fa fa-code" aria-hidden="true"></i> <?php echo $text_export_xml_str; ?></span>
      <?php echo str_replace('{{feed}}', $feed, $text_export_what_link); ?>

    </div>
  </div>
  <span class="to-top"><?php echo $text_to_top; ?></span>

  <!--scripts after load popup-->
    <script>
      option_multiplier_key = '<?php echo $option_multiplier_key; ?>';
      category_match_row = '<?php echo $category_match_row; ?>';
      attribute_row = '<?php echo $attribute_row; ?>';
      product_markup_row = '<?php echo $product_markup_row; ?>';
      replace_name_row = '<?php echo $replace_name_row; ?>';
      param_row = <?php echo $param_row; ?>;
      $('<?php echo $minus; ?>').hide();
      setTimeout(function(){
        $('<?php echo $plus; ?>').show();
      },50);
      loaded_export_setting('<?php echo $feed; ?>');
    </script>
  <!--/scripts after load popup-->

<?php } ?>
