<?php
class ControllerExtensionFeedUnixmlHalyk extends Controller {
  public function index() {

    $feed = 'halyk';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
    //headerXML
    $xml .= '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<merchant_offers date="' . date('Y-m-d H:i', time()) . '" xmlns="halyk_market" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    $xml .= '<company>' . $this->config->get('config_name') . '</company>';
    $xml .= '<merchantid>' . $startup['name'] . '</merchantid>';
    $xml .= '<brand></brand>';
    $xml .= '<offers>';

    $xml = $this->unixml->exportToXml($startup, $xml, "start");
    //headerXML

    //generateXML
      for($startup['iteration'] = 0; 1; $startup['iteration']++){

        $controller_data = $this->load->controller($controller, $startup);
        $startup['stat'] = $controller_data['data']['stat'];

        if($controller_data['products']){

          foreach($controller_data['products'] as $product_id => $product){
            $xml .= '<offer sku="' . trim($product['model']) . '" stockLevel="' . $product['quantity'] .  '">';
            $xml .= '<model>' . $product['name'] .  '</model>';
            $xml .= '<brand>' . $product['manufacturer'] .  '</brand>';

            $xml .= '<barcodes><barcode>' . $product['ean'] .  '</barcode></barcodes>';
            if(isset($product['availabilities'])){
              $xml .= '<stocks>';
                foreach($product['availabilities'] as $availability){
                  if($availability['preorder']){
                    $xml .= '<stock available="yes" storeId="' . $availability['store_id'] . '" isPP="yes" preOrder="' . $availability['preorder'] . '"/>';
                  }else{
                    $xml .= '<stock available="yes" storeId="' . $availability['store_id'] . '" isPP="yes"/>';
                  }
                }
              $xml .= '</stocks>';
            }
            $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
            foreach($product['attributes_full'] as $attribute){
              $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
            }
            $xml .= '</offer>';
          }
        } else {
          break;
        }

        $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
      }
    //generateXML

    //footerXML
    $xml .= '</offers>';
    $xml .= '</merchant_offers>';

    $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
    //footerXML
    //XML_body

  }
}
