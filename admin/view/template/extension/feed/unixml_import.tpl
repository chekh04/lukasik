<?php if(isset($prices)){ //список импортов ?>
  <?php if($prices){ ?>
    <?php foreach($prices as $price){ ?>
    <tr class="price_list_<?php echo $price['setting_id']; ?>">
      <td class="text-left"><?php echo $price['name']; ?></td>
      <td class="text-left"><?php echo $price['comment']; ?></td>
      <td class="text-left" title="<?php echo $price['file']; ?>"><a href="<?php echo $price['file']; ?>" download target="_blank"><?php echo $price['file_table']; ?></a></td>
      <td class="text-left"><?php echo $price['date']; ?></td>
      <td class="text-left status_in_list">
        <?php foreach($price['status'] as $stat){ ?>
          <div><nobr><?php echo $stat; ?></nobr></div>
        <?php } ?>
      </td>
      <td class="text-right">
        <nobr>
          <span class="price_start" data-id="<?php echo $price['setting_id']; ?>" data-name="<?php echo $price['name']; ?>" data-toggle="modal" data-target="#price_start"><span data-toggle="tooltip" title="<?php echo $text_import_start; ?>" class="btn btn-success"><i class="fa fa-play"></i></span></span>
          <span class="price_pause" data-name="<?php echo $price['name']; ?>" data-toggle="modal" data-target="#price_start" style="display:none;" data-id="<?php echo $price['setting_id']; ?>"><span data-toggle="tooltip" title="<?php echo $text_import_show_process; ?>" class="btn btn-warning"><i class="fa fa-bar-chart"></i> Посмотреть &nbsp;</span></span>
          <span class="price_setting price_setting<?php echo $price['setting_id']; ?>" data-id="<?php echo $price['setting_id']; ?>" data-toggle="modal" data-target="#price_setting"><span data-toggle="tooltip" title="<?php echo $text_import_edit_price; ?>" class="btn btn-info"><i class="fa fa-pencil"></i></span></span>
          <span data-toggle="tooltip" title="<?php echo $text_import_delete_price; ?>" class="price_delete btn btn-danger" data-id="<?php echo $price['setting_id']; ?>"><i class="fa fa-trash-o"></i></span>
          <span class="price_delete_product" data-id="<?php echo $price['setting_id']; ?>" data-toggle="modal" data-target="#price_delete_product"><span data-toggle="tooltip" title="<?php echo $text_import_delete_xml_pro; ?>" class="btn btn-warning"><i class="fa fa-times"></i></span></span>
        </nobr>
      </td>
    </tr>
    <?php } ?>
  <?php }else{ ?>
    <tr>
      <td class="text-center" colspan="6"><?php echo $text_import_list_empty; ?></td>
    </tr>
  <?php } ?>
<?php } ?>

<?php if(isset($xml_example)){ //пример XML файла ?>
&lt;<span data-toggle="tooltip" data-placement="right" title="<?php echo $text_example; ?>">yml_catalog</span>&gt;
  &lt;<span data-hover=".xst_root" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_root; ?>">shop</span>&gt;
    &lt;<span data-hover=".xst_categories" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_categories; ?>">categories</span>&gt;
      &lt;<span data-hover=".xst_category" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_category; ?>">category</span> <span data-hover=".xst_category_id" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_category_id; ?>">id</span>="1"&gt;<span data-hover=".xst_category_name" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_category_name; ?>">Категория</span>&lt;/category&gt;
      &lt;category id="10" <span data-hover=".xst_category_parent_id" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_parent_id; ?>">parentId</span>="1"&gt;Подкатегория&lt;/category&gt;
      &lt;category id="2"&gt;Категория 2&lt;/category&gt;
      &lt;category id="20" parentId="2"&gt;Подкатегория категории 2&lt;/category&gt;
    &lt;/categories&gt;
    &lt;<span data-hover=".xst_products" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_offers; ?>">offers</span>&gt;
      &lt;<span data-hover=".xst_product" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_offer; ?>">offer</span> <span data-hover=".xst_product_id" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_offer_id; ?>">id</span>="1" <span data-hover=".xst_product_quantity" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_offer_available; ?>">available</span>="true"&gt;
        &lt;<span data-hover=".xst_product_price" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_price; ?>">price</span>&gt;290.28&lt;/price&gt;
        &lt;<span data-hover=".xst_product_model" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_model; ?>">vendorCode</span>&gt;BH-1229-N&lt;/vendorCode&gt;
        &lt;<span data-hover=".xst_product_category_id" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_product_category_id; ?>">categoryId</span>&gt;10&lt;/categoryId&gt;
        &lt;<span data-hover=".xst_product_image" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_image; ?>">picture</span>&gt;https://site.com/image/product_image1.jpg&lt;/picture&gt;
        &lt;<span data-hover=".xst_product_images" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_images; ?>">picture</span>&gt;https://site.com/image/product_image2.jpg&lt;/picture&gt;
        &lt;<span data-hover=".xst_product_name" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_name; ?>">name</span>&gt;&lt;![CDATA[Cковорода 20 см Carbon Metallic Line Berlinger Haus BH-1229-N]]&gt;&lt;/name&gt;
        &lt;<span data-hover=".xst_product_description" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_description; ?>">description</span>&gt;&lt;![CDATA[&lt;p&gt;Описание товара&lt;/p&gt;]]&gt;&lt;/description&gt;
        &lt;<span data-hover=".xst_product_manufacturer" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_vendor; ?>">vendor</span>&gt;Berlinger Haus&lt;/vendor&gt;
        &lt;<span data-hover=".xst_product_sku" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_sku; ?>">code</span>&gt;3323680&lt;/code&gt;
        &lt;<span data-hover=".xst_product_attributes" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_attr; ?>">param</span> name="Верхний диаметр посуды"&gt;20 (см)&lt;/param&gt;
        &lt;param <span data-hover=".xst_product_attributes" data-toggle="tooltip" data-placement="right" title="<?php echo $text_example_attr; ?>">name</span>="Материал ручек"&gt;Бакелит&lt;/param&gt;
        &lt;param name="Многослойное дно"&gt;да&lt;/param&gt;
        &lt;param name="Форма"&gt;Круглая&lt;/param&gt;
      &lt;/offer&gt;
    &lt;/offers&gt;
  &lt;/shop&gt;
&lt;/yml_catalog&gt;
<?php } ?>

<?php if(isset($import_id)){ //настройки импорта ?>
  <div class="row" style="margin-top:5px;" id="setting_price_item">
    <div class="col-sm-6">
      <div class="price_edit_block"><input type="text" class="form-control1" value="<?php echo $price_name; ?>" name="price_name" placeholder="<?php echo $text_import_name; ?>" data-toggle="tooltip" title="<?php echo $text_import_name; ?>"></div>
      <div class="price_edit_block"><input type="text" class="form-control1" value="<?php echo $price_comment; ?>" name="price_comment" placeholder="<?php echo $text_import_comment; ?>" data-toggle="tooltip" title="<?php echo $text_import_comment; ?>"></div>
    </div>
    <div class="col-sm-6" style="padding-left:0;">
      <div class="price_file_block">
        <input type="text" class="form-control1" value="<?php echo $price_file; ?>" name="price_file" placeholder="<?php echo $text_import_file; ?>">
        <button id="button-upload-popup" style="position:absolute;right:15px;top:2px;" class="btn btn-info" title="<?php echo $text_import_select_file; ?>" data-toggle="tooltip"><i class="fa fa-upload" aria-hidden="true"></i></button>
      </div>
      <div class="row">
        <div class="col-sm-3"><input type="text" name="login" id="price_login" placeholder="<?php echo $text_import_login; ?>" value="<?php echo $login; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-3"><input type="text" name="pass" id="price_pass" placeholder="<?php echo $text_import_pass; ?>" value="<?php echo $pass; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-2"><input type="text" name="import_limit" id="import_limit" data-toggle="tooltip" title="<?php echo $text_import_limit; ?>" placeholder="<?php echo $text_import_limit; ?>" value="<?php echo $import_limit; ?>" class="form-control" style="margin-top:4px;"></div>
        <div class="col-sm-4">
          <span class="import_read_xml" id="import_read_xml" data-toggle="tooltip" title="<?php echo $text_import_not_recomend; ?>"><?php echo $text_import_read_xml; ?></span>
        </div>
      </div>
    </div>
  </div>
  <hr style="margin:15px -15px;border-color:#777;">
  <style>
  .import_setting_block .form-group{margin-left:0!important;margin-right:0!important;}
  </style>
  <div class="import-item-setting">
    <div class="row">

      <div class="col-sm-6">
        <h4><strong><?php echo $text_import_setting_xml; ?></strong></h4>
        <?php echo $text_import_setting_see; ?>
        <div class="form-horizontal import_setting_block">
          <input type="hidden" value="<?php echo $import_id; ?>" name="unixml_import_id">
          <div class="form-group xst_root">
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_tag_catalog; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_root; ?>" type="text" name="unixml_import_xml_root" class="form-control text-center" placeholder="shop, catalog <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_categories" style="border-top: 1px solid #aaa;background:#fcfcfc;">
            <span class="load_item_set" data-set="nupd" data-item="categories" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_categories; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_categories; ?>" type="text" name="unixml_import_xml_categories" class="form-control text-center" placeholder="categories <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category" style="background:#fcfcfc;">
            <span class="load_item_set" data-set="status,top" data-item="category" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_category; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category; ?>" type="text" name="unixml_import_xml_category" class="form-control text-center" placeholder="category <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_id" style="background:#fcfcfc;">
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_category_id; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_id; ?>" type="text" name="unixml_import_xml_category_id" class="form-control text-center" placeholder="@id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_parent_id" style="background:#fcfcfc;">
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_parent_id; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_parent_id; ?>" type="text" name="unixml_import_xml_category_parent_id" class="form-control text-center" placeholder="@parentId">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_category_name" style="background:#fcfcfc;">
            <span class="load_item_set" data-set="url,category_replace_name" data-item="category_name" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_category_name; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_category_name; ?>" type="text" name="unixml_import_xml_category_name" class="form-control text-center">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_products" style="border-top: 1px solid #aaa;">
            <span class="load_item_set" data-set="nadd,prodis,proqua" data-item="products" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_products; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_products; ?>" type="text" name="unixml_import_xml_products" class="form-control text-center" placeholder="offers <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product">
            <span class="load_item_set" data-set="status" data-item="product" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product; ?></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product; ?>" type="text" name="unixml_import_xml_product" class="form-control text-center" placeholder="offer <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_id">
            <span class="load_item_set" data-set="url,link,to,stop" data-item="product_id" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product_id; ?><small>p.product_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_id; ?>" type="text" name="unixml_import_xml_product_id" class="form-control text-center" placeholder="@id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_name">
            <span class="load_item_set" data-set="url,nupd,link,tpl,to,replace,stop" data-item="product_name" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product_name; ?><small>pd.name</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_name; ?>" type="text" name="unixml_import_xml_product_name" class="form-control text-center" placeholder="name">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_model">
            <span class="load_item_set" data-set="url,nupd,link,calc,tpl,to,replace,stop" data-item="product_model" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product_model; ?><small>p.model</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_model; ?>" type="text" name="unixml_import_xml_product_model" class="form-control text-center" placeholder="code model <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_sku">
            <span class="load_item_set" data-set="url,nupd,link,calc,tpl,to,replace" data-item="product_sku" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product_sku; ?><small>p.sku</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_sku; ?>" type="text" name="unixml_import_xml_product_sku" class="form-control text-center" placeholder="sku vendorCode <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_manufacturer">
            <span class="load_item_set" data-set="nupd,to,replace_manufacturer" data-item="product_manufacturer" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_manufacturer; ?><small>p.manufacturer_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_manufacturer; ?>" type="text" name="unixml_import_xml_product_manufacturer" class="form-control text-center" placeholder="vendor manufacturer <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_description">
            <span class="load_item_set" data-set="nupd,tpl,to,replace" data-item="product_description" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_desc; ?><small>pd.description</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_description; ?>" type="text" name="unixml_import_xml_product_description" class="form-control text-center" placeholder="description <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_price">
            <span class="load_item_set" data-set="nupd,calc,tpl,price_filter,to,replace" data-item="product_price" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_price; ?><small>p.price</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_price; ?>" type="text" name="unixml_import_xml_product_price" class="form-control text-center" placeholder="price priceUSD <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_special">
            <span class="load_item_set" data-set="special,calc,tpl,to,replace" data-item="product_special" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><span title="<?php echo $text_import_set_xml_special_help; ?>" data-toggle="tooltip"><?php echo $text_import_set_xml_special; ?></span><small>ps.special</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo isset($unixml_import_xml_product_special)?$unixml_import_xml_product_special:''; ?>" type="text" name="unixml_import_xml_product_special" class="form-control text-center" placeholder="price если цена price_old, price_promo, special <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_quantity">
            <span class="load_item_set" data-set="nupd,calc,tpl,to,replace,sip" data-item="product_quantity" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_quantity; ?><small>p.quantity</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_quantity; ?>" type="text" name="unixml_import_xml_product_quantity" class="form-control text-center" placeholder="@available quantity <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_category_id">
            <span class="load_item_set" data-set="nupd,tpl,replace_category" data-item="product_category_id" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_product_category_id; ?><small>p2c.category_id</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_category_id; ?>" type="text" name="unixml_import_xml_product_category_id" class="form-control text-center" placeholder="categoryId category <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_image">
            <span class="load_item_set" data-set="nupd,replace,image,tpl,to" data-item="product_image" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_image; ?><small>p.image</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_image; ?>" type="text" name="unixml_import_xml_product_image" class="form-control text-center" placeholder="picture image <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_images">
            <span class="load_item_set" data-set="nupd,replace,image" data-item="product_images" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_images; ?><small>pi.images</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_images; ?>" type="text" name="unixml_import_xml_product_images" class="form-control text-center" placeholder="picture image <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_attributes">
            <span class="load_item_set" data-set="nupd,attr,replace,replace_attribute" data-item="product_attributes" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_attr; ?><small>pa.attributes</small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_attributes; ?>" type="text" name="unixml_import_xml_product_attributes" class="form-control text-center" placeholder="param @name <?php echo $text_import_etc; ?>">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>
          <div class="form-group xst_product_options">
            <span class="load_item_set" data-set="nupd,replace_option,replace_option_value" data-item="product_options" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
            <label class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_options; ?></small></label>
            <div class="col-sm-7">
              <div class="input-group w200">
                <span class="input-group-addon"><</span>
                <input value="<?php echo $unixml_import_xml_product_options; ?>" type="text" name="unixml_import_xml_product_options" class="form-control text-center" placeholder="@group_id">
                <span class="input-group-addon">></span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label style="padding-bottom:10px;" class="col-sm-5 control-label text-right"><?php echo $text_import_set_xml_url_gen; ?></label>
            <div class="col-sm-7" style="padding-left:0px;">
              <input type="checkbox" class="checkbox_exp" id="unixml_import_url_without_language" <?php if($unixml_import_url_without_language){ ?>checked="checked"<?php } ?> name="unixml_import_url_without_language" value="1">
              <label for="unixml_import_url_without_language" class="bnv green"></label>
            </div>
          </div>

          <div class="form-group xst_product_additionals">
            <div class="col-sm-12 table-responsive" style="margin-top:10px;">
              <span class="load_item_set" data-set="nupd" data-item="product_additionals" data-price="<?php echo $import_id; ?>" data-toggle="modal" data-target="#price_setting_item_set"><i class="fa fa-cog fa-lg" aria-hidden="true"></i></span>
              <b><?php echo $text_import_set_add_heading; ?></b>
              <table id="additionals_row_table" class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-left" style="width: 48%;"><?php echo $text_tag; ?></td>
                    <td class="text-left" style="width: 48%;"><?php echo $text_import_set_add_to; ?></td>
                    <td></td>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($unixml_import_xml_product_additionals as $additionals_row => $row) { ?>
                    <tr id="additionals_row<?php echo $additionals_row; ?>">
                      <td class="text-left" style="width: 48%;">
                        <div class="input-group w200">
                          <span class="input-group-addon"><</span>
                          <input type="text" name="unixml_import_xml_product_additionals[<?php echo $additionals_row; ?>][tag]" value="<?php echo $row['tag']; ?>" placeholder="<?php echo $text_tag; ?>" class="form-control text-center" />
                          <span class="input-group-addon">></span>
                        </div>
                      </td>
                      <td class="text-left" style="width: 48%;">
                        <select name="unixml_import_xml_product_additionals[<?php echo $additionals_row; ?>][to]" class="form-control">
                          <optgroup label="<?php echo $text_table; ?> product">
                            <?php foreach($fields_product as $p_field_name => $p_field_value){ ?>
                              <option value="<?php echo $p_field_value; ?>" <?php if($p_field_value == $row['to']){ ?>selected="selected"<?php } ?>>p.<?php echo $p_field_name; ?></option>
                            <?php } ?>
                          </optgroup>
                          <optgroup label="<?php echo $text_table; ?> product_description">
                            <?php foreach($fields_product_description as $pd_field_name => $pd_field_value){ ?>
                              <option value="<?php echo $pd_field_value; ?>" <?php if($pd_field_value == $row['to']){ ?>selected="selected"<?php } ?>>pd.<?php echo $pd_field_name; ?></option>
                            <?php } ?>
                          </optgroup>
                        </select>
                      </td>
                      <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr>
                    <?php $additionals_row++; ?>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="text-center"><button type="button" onclick="addAdditional();" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <input type="hidden" id="additionals_row" value="<?php echo $additionals_row; ?>" name="additionals_row">

            <script id="example_additionals_row" type="text/x-handlebars-template">
              <tr id="additionals_row777888777">
                <td class="text-left" style="width: 48%;">
                  <div class="input-group w200">
                    <span class="input-group-addon"><</span>
                    <input type="text" name="unixml_import_xml_product_additionals[777888777][tag]" value="" placeholder="<?php echo $text_tag; ?>" class="form-control text-center" />
                    <span class="input-group-addon">></span>
                  </div>
                </td>
                <td class="text-left" style="width: 48%;">
                  <select name="unixml_import_xml_product_additionals[777888777][to]" class="form-control">
                    <optgroup label="<?php echo $text_table; ?> product">
                      <?php foreach($fields_product as $p_field_name => $p_field_value){ ?>
                        <option value="<?php echo $p_field_value; ?>">p.<?php echo $p_field_name; ?></option>
                      <?php } ?>
                    </optgroup>
                    <optgroup label="<?php echo $text_table; ?> product_description">
                      <?php foreach($fields_product_description as $pd_field_name => $pd_field_value){ ?>
                        <option value="<?php echo $pd_field_value; ?>">pd.<?php echo $pd_field_name; ?></option>
                      <?php } ?>
                    </optgroup>
                  </select>
                </td>
                <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
              </tr>
            </script>
          </div>

          <div class="form-group xst_product_multilang">
            <div class="col-sm-12 table-responsive" style="margin-top:10px;">
              <b><?php echo $text_import_multilang_heading; ?></b>
              <table id="lang_row_table" class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                    <td class="text-left" style="width: 65%;"><?php echo $text_import_multilang_link; ?></td>
                    <td class="text-left" style="width: 25%;"><?php echo $text_import_multilang_lang; ?></td>
                    <td></td>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($unixml_import_xml_product_multilang as $lang_row => $row) { ?>
                    <tr id="lang_row<?php echo $lang_row; ?>">
                      <td class="text-left" style="width: 65%;">
                        <input type="text" name="unixml_import_xml_product_multilang[<?php echo $lang_row; ?>][file]" value="<?php echo $row['file']; ?>" placeholder="<?php echo $text_import_multilang_link; ?>" class="form-control" />
                        <textarea name="unixml_import_xml_product_multilang[<?php echo $lang_row; ?>][attribute]" placeholder="<?php echo $text_import_multilang_attr; ?>" class="form-control"><?php echo $row['attribute']; ?></textarea>
                      </td>
                      <td class="text-left" style="width: 25%;">
                        <select name="unixml_import_xml_product_multilang[<?php echo $lang_row; ?>][lang]" class="form-control">
                          <?php foreach($languages as $lang){ ?>
                            <option value="<?php echo $lang['language_id']; ?>" <?php if($lang['language_id'] == $row['lang']){ ?>selected="selected"<?php } ?>><?php echo $lang['name']; ?></option>
                          <?php } ?>
                        </select>
                      </td>
                      <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr>
                    <?php $lang_row++; ?>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="text-center"><button type="button" onclick="addLangRow();" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <input type="hidden" id="lang_row" value="<?php echo $lang_row; ?>" name="lang_row">

            <script id="example_lang_row" type="text/x-handlebars-template">
              <tr id="lang_row777888777">
                <td class="text-left" style="width: 65%;">
                  <input type="text" name="unixml_import_xml_product_multilang[777888777][file]" value="" placeholder="<?php echo $text_import_multilang_link; ?>" class="form-control" />
                  <textarea name="unixml_import_xml_product_multilang[777888777][attribute]" placeholder="<?php echo $text_import_multilang_attr; ?>" class="form-control"></textarea>
                </td>
                <td class="text-left" style="width: 25%;">
                  <select name="unixml_import_xml_product_multilang[777888777][lang]" class="form-control">
                    <?php foreach($languages as $lang){ ?>
                      <option value="<?php echo $lang['language_id']; ?>"><?php echo $lang['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
                <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
              </tr>
            </script>
          </div>



          <div class="form-group xst_product_custom_before">
            <label class="col-sm-12"><?php echo $text_import_custom_before; ?></label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom_before" class="form-control text-left" placeholder="<?php echo $text_import_custom_before_place; ?>"><?php echo $unixml_import_xml_product_custom_before; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

          <div class="form-group xst_product_custom">
            <label class="col-sm-12"><?php echo $text_import_custom_xml; ?></label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom" class="form-control text-left" placeholder="<?php echo $text_import_custom_xml_place; ?>"><?php echo $unixml_import_xml_product_custom; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

          <div class="form-group xst_product_custom_after">
            <label class="col-sm-12"><?php echo $text_import_custom_after; ?></label>
            <div class="col-sm-12">
              <div class="input-group w200">
                <span class="input-group-addon">&lt;?php</span>
                <textarea style="min-height:200px;" name="unixml_import_xml_product_custom_after" class="form-control text-left" placeholder="<?php echo $text_import_custom_after_place; ?>"><?php echo $unixml_import_xml_product_custom_after; ?></textarea>
                <span class="input-group-addon">?&gt;</span>
              </div>
            </div>
          </div>

        </div>

      </div>

      <div class="col-sm-6" style="padding-left:0;">
        <h4 class="h4_div h4_div_frst"><strong><?php echo $text_example_heading; ?></strong> <span>-</span></h4>
        <div>
          <div style="margin:9px 0 0px;"><?php echo $text_example_heading_sub; ?></div>
          <div class="import_price_info">
            <pre class="xmlex"><?php echo $xmlex; ?></pre>
          </div>
        </div>
        <div id="xml_res"></div>
      </div>

    </div>
  </div>
  <script>
    $('#price_settingTitle').html('<span style="font-size:20px;line-height:35px;"><?php echo $text_import_edit_title; ?> <strong>#<?php echo $import_id; ?></strong></span>');
    <?php if(isset($date_edit)){ ?>
      $('#price_lastUpdate').html('<span style="font-size:13px;position: absolute;right: 40px;top:-2px;color: #ccc;"><strong style="font-size:10px;font-weight:400;"><?php echo $text_import_last_update; ?></strong><br><?php echo $date_edit; ?></span>');
    <?php } ?>

    $('#price_import_export').html('<a class="btn btn-info export-import" href="<?php echo $import_export_setting; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_import_export_to_file_title; ?>"><i class="fa fa-upload" aria-hidden="true"></i> <?php echo $text_import_export_to_file; ?></a> <span class="btn btn-info export-import upload_file_import" data-import="<?php echo $import_id; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo $text_import_import_from_file_title; ?> <?php echo $import_id; ?>. <?php echo $text_import_import_from_file_title_alarm; ?>"><i class="fa fa-download" aria-hidden="true"></i> <?php echo $text_import_import_from_file; ?></span>');

    $('.xmlex span[data-toggle="tooltip"]').on('click', function(){
      $('.import_setting_block .form-group').removeClass('hovered');
      $('.import_setting_block .form-group input').focusout();
      $($(this).data('hover')).addClass('hovered');
      $($(this).data('hover')).find('input').focus();
      setTimeout(function(){
        $('.import_setting_block .form-group').removeClass('hovered');
      },1000);
    });

    $('#import_cron_key').text('<?php echo $cron_link; ?>');
  </script>
<?php } ?>

<?php if(isset($setting_item)){ //настройки поля ?>
  <div id="setting_item">
    <script>
      $(document).ready(function(){
        $('[data-toggle="popover"]').popover({
          placement : 'right',
          html : true
        });
        //автовысота поля ввода
        function fixTextareaSize(textarea) {
          textarea.style.height = 'auto'
          textarea.style.height = textarea.scrollHeight + 2 + "px"
        }

        $('textarea').on('input', function (e) {
          fixTextareaSize(e.target);
        });

        $('textarea').each(function(e) {
          fixTextareaSize(this);
        });
        //автовысота поля ввода
      });
    </script>
    <h3 class="text-center"><?php echo $text_import_si_set_to; ?> <b><?php echo $setting_item; ?></b></h3>
    <hr style="margin-bottom:4px;">
    <input type="hidden" value="<?php echo $id; ?>" name="id">
    <input type="hidden" value="<?php echo $item; ?>" name="item">
    <?php if($sip){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_sip; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($sip_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv2" name="sip_value" value="1">
              <label for="nv2" class="bnv green"><?php echo $text_import_si_sip_po_ttl; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_sip; ?>" data-content="<?php echo $text_import_si_sip_po_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($url){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_url_gen; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($url_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv2" name="url_value" value="1">
              <label for="nv2" class="bnv green"><?php echo $text_import_si_url_gen_on; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_url_gen_ttl; ?>" data-content="<?php echo $text_import_si_url_gen_desc; ?> <?php echo $setting_item; ?> <?php echo $text_import_si_url_gen_desc2; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($status){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_new_off; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($status_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv1" name="status_value" value="1">
              <label for="nv1" class="bnv"><?php echo $setting_item; ?> <?php echo $text_import_si_new_off_on; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_new_off_ttl; ?>" data-content="<?php echo $text_import_si_new_off_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($top){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_cat_top; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($top_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv6" name="top_value" value="1">
              <label for="nv6" class="bnv green"><?php echo $text_import_si_cat_top_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_cat_top_ttl; ?>" data-content="<?php echo $text_import_si_cat_top_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($nadd){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_no_add; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($nadd_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv" name="nadd_value" value="1">
              <label for="nv" class="bnv"><?php echo $text_import_si_no_add_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_no_add_ttl; ?>" data-content="<?php echo $text_import_si_no_add_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($prodis){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_prodis; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($prodis_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="prodis" name="prodis_value" value="1">
              <label for="prodis" class="bnv"><?php echo $text_import_si_prodis_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_prodis_ttl; ?>" data-content="<?php echo $text_import_si_prodis_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($proqua){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_proqua; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($proqua_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="proqua" name="proqua_value" value="1">
              <label for="proqua" class="bnv green"><?php echo $text_import_si_proqua_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_proqua_ttl; ?>" data-content="<?php echo $text_import_si_proqua_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($nupd){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_nupd; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($nupd_value){ ?>checked="checked"<?php } ?> class="checkbox_exp" id="nv" name="nupd_value" value="1">
              <label for="nv" class="bnv"><?php echo $text_import_si_nupd_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_nupd_ttl; ?>" data-content="<?php echo $text_import_si_nupd_desc; ?> <?php echo $text_import_etc; ?> <?php echo $text_import_si_nupd_desc2; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($special){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label pt4"><?php echo $text_import_si_special; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input type="checkbox" <?php if($special_value){ ?>checked="checked"<?php } ?> class="checkbox_exp cb_green" id="nv5" name="special_value" value="1">
              <label for="nv5" class="bnv green"><?php echo $text_import_si_special_label; ?></label>
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_special_ttl; ?>" data-content="<?php echo $text_import_si_special_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($link){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label"><?php echo $text_import_si_link; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $link_value; ?>" type="text" name="link_value" class="form-control">
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_link_ttl; ?>" data-content="<?php echo $text_import_si_link_desc; ?>">?</span>
            </div>
            <small style="position:absolute;color:#856404;font-size:10px;"><?php echo $text_import_si_link_att; ?></small>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($to){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label"><?php echo $text_import_si_to_value; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $to_value; ?>" type="text" name="to_value" class="form-control" placeholder="pd.meta_keyword, p.model, p.sku">
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_to_value_ttl; ?>" data-content="<?php echo $text_import_si_to_value_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($attr){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label text-right" style="padding-top:0px;"><?php echo $text_import_si_attr; ?></label>
          <div class="col-sm-7">
            <div class="input-group w200">
              <select name="attr_value" class="form-control" style="width:100%;">
                <?php foreach($attribute_groups as $attribute_group){ ?>
                  <option value="<?php echo $attribute_group['attribute_group_id']; ?>" <?php if($attribute_group['attribute_group_id'] == $attr_value){ ?>selected="selected"<?php } ?>><?php echo $attribute_group['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($calc){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label"><?php echo $text_import_si_calc; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $calc_value; ?>" type="text" name="calc_value" class="form-control" placeholder="+20%, -20%, +100, -200, *3, /3">
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_calc_ttl; ?>" data-content="<?php echo $text_import_si_calc_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($price_filter){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label"><?php echo $text_import_si_price_filter; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $price_filter_value; ?>" type="text" name="price_filter_value" class="form-control" placeholder="=500 или >500 или <500">
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_price_filter_ttl; ?>" data-content="<?php echo $text_import_si_price_filter_desc; ?>">?</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($tpl){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-5 control-label"><?php echo $text_import_si_tpl; ?></label>
          <div class="col-sm-7">
            <div class="input-group">
              <input value="<?php echo $tpl_value; ?>" type="text" name="tpl_value" class="form-control" placeholder="{{name}} {{model}}">
              <span class="input-group-addon" data-toggle="popover" title="<?php echo $text_import_si_tpl_ttl; ?>" data-content="<?php echo $text_import_si_tpl_desc; ?>">?</span>
            </div>
          </div>
        </div>
        <div class="helper" style="margin-bottom:15px;padding:10px;border:1px solid #eee;background:#fafafa;">
          <?php echo $text_import_si_tpl_other; ?>
        </div>
      </div>
    <?php } ?>
    <?php if($replace){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <div class="input-group">
                <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $text_import_si_replace_from_ttl; ?>"><?php echo $text_import_si_replace_from; ?></span>
                <input value="<?php echo isset($replace_value_from)?$replace_value_from:''; ?>" type="text" name="replace_value_from" class="form-control" placeholder="">
              </div>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <div class="input-group">
                <span class="input-group-addon" data-toggle="tooltip" title="<?php echo $text_import_si_replace_to_ttl; ?>"><?php echo $text_import_si_replace_to; ?></span>
                <input value="<?php echo isset($replace_value_to)?$replace_value_to:''; ?>" type="text" name="replace_value_to" class="form-control" placeholder="">
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($category_replace_name){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3><?php echo $text_import_si_replace_cat_name; ?></h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong><?php echo $text_import_si_replace_cat_name_xml; ?></strong>
              <textarea style="min-height:100px;" name="category_replace_name_value_from" class="form-control"><?php echo isset($category_replace_name_value_from)?$category_replace_name_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong><?php echo $text_import_si_replace_cat_name_to; ?></strong>
              <textarea style="min-height:100px;" name="category_replace_name_value_to" class="form-control"><?php echo isset($category_replace_name_value_to)?$category_replace_name_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <?php echo $text_import_si_replace_cat_name_help; ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_option){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3><?php echo $text_import_si_replace_option; ?></h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong><?php echo $text_import_si_replace_option_from; ?></strong>
              <textarea style="min-height:100px;" name="replace_option_value_from" class="form-control"><?php echo isset($replace_option_value_from)?$replace_option_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong><?php echo $text_import_si_replace_option_to; ?></strong>
              <textarea style="min-height:100px;" name="replace_option_value_to" class="form-control"><?php echo isset($replace_option_value_to)?$replace_option_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <?php echo $text_import_si_replace_option_help; ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_option_value){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="row">
            <div class="col-sm-12"><h3><?php echo $text_import_si_option_value; ?></h3></div>
          </div>
          <div class="row">
            <div class="col-sm-6" style="padding-right:7px;">
              <strong><?php echo $text_import_si_option_value_from; ?></strong>
              <textarea style="min-height:100px;" name="replace_option_value_value_from" class="form-control"><?php echo isset($replace_option_value_value_from)?$replace_option_value_value_from:''; ?></textarea>
            </div>
            <div class="col-sm-6" style="padding-left:7px;">
              <strong><?php echo $text_import_si_option_value_to; ?></strong>
              <textarea style="min-height:100px;" name="replace_option_value_value_to" class="form-control"><?php echo isset($replace_option_value_value_to)?$replace_option_value_value_to:''; ?></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <?php echo $text_import_si_option_value_help; ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if($replace_manufacturer){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3><?php echo $text_import_si_replace_man; ?></h3>
            <table id="replace_manufacturer" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left"><?php echo $text_import_si_replace_man_xml; ?></td>
                  <td class="text-left"><?php echo $text_import_si_replace_man_oc; ?></td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_manufacturer_list as $row_key => $row) { ?>
                  <tr id="manufacturer_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_manufacturer[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="<?php echo $text_import_si_replace_man_xml; ?>" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <select name="replace_manufacturer[<?php echo $row_key; ?>][oc]" class="form-control">
                        <option value="0" <?php if($row['oc'] == '0'){ ?>selected="selected"<?php } ?>><?php echo $text_import_si_replace_man_not; ?></option>
                        <?php foreach($manufacturers as $manufacturer){ ?>
                          <option value="<?php echo $manufacturer['manufacturer_id']; ?>" <?php if($manufacturer['manufacturer_id'] == $row['oc']){ ?>selected="selected"<?php } ?>><?php echo $manufacturer['name']; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportManufacturerMatch();" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p><?php echo $text_import_si_replace_man_help; ?></p>
          </div>

          <input type="hidden" id="manufacturer_match_row" value="<?php echo $row_key; ?>" name="manufacturer_match_row">

          <script id="example_row_manufacturer" type="text/x-handlebars-template">
            <tr id="manufacturer_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_manufacturer[777888777][xml]" value="" placeholder="<?php echo $text_import_si_replace_man_xml; ?>" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <select name="replace_manufacturer[777888777][oc]" class="form-control">
                  <option value="0"><?php echo $text_import_si_replace_man_not; ?></option>
                  <?php foreach($manufacturers as $manufacturer){ ?>
                    <option value="<?php echo $manufacturer['manufacturer_id']; ?>"><?php echo $manufacturer['name']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($replace_attribute){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3><?php echo $text_import_si_replace_att; ?></h3>
            <table id="replace_attribute" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left"><?php echo $text_import_si_replace_att_xml; ?></td>
                  <td class="text-left"><?php echo $text_import_si_replace_att_oc; ?></td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_attribute_list as $row_key => $row) { ?>
                  <tr id="attribute_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_attribute[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="<?php echo $text_import_si_replace_att_xml; ?>" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <select name="replace_attribute[<?php echo $row_key; ?>][oc]" class="form-control">
                        <option value="-1" <?php if($row['oc'] == '-1'){ ?>selected="selected"<?php } ?>><?php echo $text_import_si_replace_att_nota; ?></option>
                        <option value="0" <?php if($row['oc'] == '0'){ ?>selected="selected"<?php } ?>><?php echo $text_import_si_replace_att_not; ?></option>
                        <?php foreach($attributes as $attribute_group => $group_attributes){ ?>
                          <optgroup label="<?php echo $attribute_group; ?>">
                            <?php foreach($group_attributes as $attribute){ ?>
                              <option value="<?php echo $attribute['attribute_id']; ?>" <?php if($attribute['attribute_id'] == $row['oc']){ ?>selected="selected"<?php } ?>><?php echo $attribute['name']; ?></option>
                            <?php } ?>
                          </optgroup>
                        <?php } ?>
                      </select>
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportAttributeMatch();" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p><?php echo $text_import_si_replace_att_help; ?></p>
          </div>

          <input type="hidden" id="attribute_match_row" value="<?php echo $row_key; ?>" name="attribute_match_row">

          <script id="example_row_attribute" type="text/x-handlebars-template">
            <tr id="attribute_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_attribute[777888777][xml]" value="" placeholder="<?php echo $text_import_si_replace_att_xml; ?>" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <select name="replace_attribute[777888777][oc]" class="form-control">
                  <option value="-1"><?php echo $text_import_si_replace_att_nota; ?></option>
                  <option value="0"><?php echo $text_import_si_replace_att_not; ?></option>
                  <?php foreach($attributes as $attribute_group => $group_attributes){ ?>
                    <optgroup label="<?php echo $attribute_group; ?>">
                      <?php foreach($group_attributes as $attribute){ ?>
                        <option value="<?php echo $attribute['attribute_id']; ?>"><?php echo $attribute['name']; ?></option>
                      <?php } ?>
                    </optgroup>
                  <?php } ?>
                </select>
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($replace_category){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-121 table-responsive">
            <h3><?php echo $text_import_si_replace_category; ?></h3>
            <table id="replace_category" class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <td class="text-left"><?php echo $text_import_si_replace_category_xml; ?></td>
                  <td class="text-left"><?php echo $text_import_si_replace_category_oc; ?></td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($replace_category_list as $row_key => $row) { ?>
                  <tr id="category_match_row<?php echo $row_key; ?>">
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_category[<?php echo $row_key; ?>][xml]" value="<?php echo $row['xml']; ?>" placeholder="<?php echo $text_import_si_replace_category_xml; ?>" class="form-control" />
                    </td>
                    <td class="text-left" style="width: 45%;">
                      <input type="text" name="replace_category[<?php echo $row_key; ?>][ocname]" value="<?php echo $row['ocname']; ?>" placeholder="<?php echo $text_import_si_replace_category_oc; ?>" class="form-control" />
                      <input type="hidden" name="replace_category[<?php echo $row_key; ?>][oc]" value="<?php echo $row['oc']; ?>" />
                    </td>
                    <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                  </tr>
                  <?php $row_key++; ?>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="text-center"><button type="button" onclick="addImportCategoryMatch();" data-toggle="tooltip" title="<?php echo $text_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
              </tfoot>
            </table>
            <p><?php echo $text_import_si_replace_category_help; ?></p>
          </div>

          <script>
            loadXMLCategories();
            $('#replace_category tbody tr').each(function(index, element) {
              importcategoryautocomplete(index);
              importcategoryxmlautocomplete(index);
            });
          </script>

          <input type="hidden" id="category_match_row" value="<?php echo $row_key; ?>" name="category_match_row">

          <script id="example_row_category" type="text/x-handlebars-template">
            <tr id="category_match_row777888777">
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_category[777888777][xml]" value="" placeholder="<?php echo $text_import_si_replace_category_xml; ?>" class="form-control" />
              </td>
              <td class="text-left" style="width: 45%;">
                <input type="text" name="replace_category[777888777][ocname]" value="" placeholder="<?php echo $text_import_si_replace_category_oc; ?>" class="form-control" />
                <input type="hidden" name="replace_category[777888777][oc]" value="" />
              </td>
              <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="<?php echo $text_delete; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
            </tr>
          </script>

        </div>
      </div>
    <?php } ?>
    <?php if($stop){ ?>
      <div class="form-horizontal">
        <div class="form-group">
          <div class="col-sm-12">
            <h3><?php echo $text_import_si_stop; ?></h3>
            <textarea type="text" name="stop_value" class="form-control" style="min-height:100px;" placeholder="<?php echo $text_import_si_stop_place; ?>"><?php echo $stop_value; ?></textarea>
            <small><?php echo $text_import_si_stop_place; ?></small>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<?php } ?>

<?php if(isset($xml_orig)){ //загрузка оригинального XML из ссылки ?>
  <h4 class="h4_div"><strong><?php echo $text_import_xml_orig; ?></strong> <span>-</span></h4>
  <div>
    <div class="import_price_info">
      <pre class="xmlex"><?php echo $xml_orig; ?></pre>
    </div>
  </div>
<?php } ?>
