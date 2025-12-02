<?php
class ControllerExtensionFeedUnixmlSalidzini extends Controller {
  public function index() {

    $feed = 'salidzini';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8" ?>';
      $xml .= '<root>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){

              $xml .= '<item>';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<link>' . $product['url'] .  '</link>';
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
              $xml .= '<image>' . $product['image'] .  '</image>';
              if(isset($product['category_full'])){
                $xml .= '<category_full>' . $product['category_full'] .  '</category_full>';
              }
              $xml .= '<category_link>' . $this->url->link('product/category', 'path=' . $product['category_id']) .  '</category_link>';
              $xml .= '<brand>' . $product['manufacturer'] .  '</brand>';
              foreach($product['attributes_full'] as $attribute){
                //$xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              $xml .= '</item>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      /*
      4.2
      $data['product_type'] = true;

      4.3
      $catfull = array();
      foreach(explode('_', $product['category_path']) as $cat_id){
      if($data['categories_xml'][$cat_id]){
      $catfull[] = $data['categories_xml'][$cat_id]['name'];
      }
      }
      if($catfull){
      $product['category_full'] = implode(' &gt;&gt; ', $catfull);
      }
      */

      //footerXML
      $xml .= '</root>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML

    //XML_body

  }
}
