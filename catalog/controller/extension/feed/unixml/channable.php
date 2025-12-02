<?php
class ControllerExtensionFeedUnixmlChannable extends Controller {
  public function index() {

    $feed = 'channable';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml  = '<?xml version="1.0" encoding="utf-8" ?>';
      $xml .= '<items>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){
            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<item>';
              $xml .= '<unique_id>' . $product_id . '</unique_id>';
              $xml .= '<title>' . $product['name'] . '</title>';
              if($product['special']){
                $xml .= '<price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</price>';
                $xml .= '<sale_price>' . $product['special'] . ' ' . $startup['currency_xml'] . '</sale_price>';
                $xml .= '<sale_price_effective_date>' . date("Y-m-d", strtotime("yesterday")) . '/' . date("Y-m-d", strtotime("tomorrow")) . '</sale_price_effective_date>';
              }else{
                $xml .= '<price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</price>';
              }
              $xml .= '<stock>' . $product['quantity'] . '</stock>';
              $xml .= '<availability>' . ($product['quantity'] ? 'in stock' : 'out of stock') . '</availability>';
              if(isset($startup['categories_xml'][$product['category_id']]['market_id']) && $startup['categories_xml'][$product['category_id']]['market_id']){
                $xml .= '<google_product_category>' . $startup['categories_xml'][$product['category_id']]['market_id'] . '</google_product_category>';
              }
              $xml .= '<condition>new</condition>';
              $xml .= '<image_link>' . $product['image'] .  '</image_link>';
              if($product['images']){
                $xml .= '<additional_image_link>' . implode(', ', $product['images']) .  '</additional_image_link>';
              }
              $xml .= '<link>' . $product['url'] .  '</link>';
              $xml .= '<brand>' . $product['manufacturer'] . '</brand>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';

              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . str_replace('g:', '', $attribute['name']) . '>' . str_replace('>', '/', $attribute['text']) .  '</' . str_replace('g:', '', $attribute['end']) . '>';
              }
              $xml .= '</item>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</items>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
